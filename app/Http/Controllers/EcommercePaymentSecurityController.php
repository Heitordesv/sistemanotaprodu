<?php

namespace App\Http\Controllers;

use App\Helpers\PedidoEcommerceHelper;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\ClienteEcommerce;
use App\Models\ConfigEcommerce;
use App\Models\PedidoEcommerce;
use App\Models\PostBlogEcommerce;
use App\Services\EcommerceMercadoPagoService;
use App\Services\EcommerceStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EcommercePaymentSecurityController extends Controller
{
    public function __construct(
        private EcommerceMercadoPagoService $mercadoPago,
        private EcommerceStockService $stockService
    ) {}

    private function configLoja(string $link): ConfigEcommerce
    {
        return ConfigEcommerce::where('link', strtolower($link))->firstOrFail();
    }

    private function clienteDaSessao(ConfigEcommerce $config): ClienteEcommerce
    {
        $sessao = session('user_ecommerce');
        abort_if(!$sessao, 403, 'Faça login para continuar.');
        abort_if((int) ($sessao['empresa_id'] ?? 0) !== (int) $config->empresa_id, 403, 'Sessão inválida para esta loja.');

        return ClienteEcommerce::where('id', $sessao['cliente_id'])
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 1)
            ->firstOrFail();
    }

    private function pedidoDaSessao(ConfigEcommerce $config): PedidoEcommerce
    {
        $cliente = $this->clienteDaSessao($config);
        $pedido = (new PedidoEcommerceHelper())->getCarrinho($config->empresa_id);
        abort_if(!$pedido, 404, 'Carrinho não encontrado.');
        abort_if((int) $pedido->cliente_id !== (int) $cliente->id, 403, 'Pedido inválido para o cliente logado.');
        abort_if((int) $pedido->empresa_id !== (int) $config->empresa_id, 403, 'Pedido inválido para esta loja.');
        return $pedido;
    }

    private function pedidoDoCliente(ConfigEcommerce $config, int $pedidoId): PedidoEcommerce
    {
        $cliente = $this->clienteDaSessao($config);
        return PedidoEcommerce::where('id', $pedidoId)
            ->where('empresa_id', $config->empresa_id)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();
    }

    public function pix(Request $request, string $link)
    {
        $config = $this->configLoja($link);
        $pedido = $this->pedidoDaSessao($config);
        try {
            $this->stockService->reservarPedido($pedido);
            $payment = $this->mercadoPago->criarPix($pedido, $config);
            $pedido->refresh();
            if (in_array(strtolower((string) ($payment['status'] ?? '')), ['rejected', 'cancelled'], true)) {
                $this->stockService->liberarPedido($pedido);
                $pedido->status = 0;
                $pedido->save();
                return back()->with('mensagem_erro', 'O Mercado Pago não conseguiu gerar esta cobrança PIX. Tente novamente.');
            }
            return redirect()->route('ecommerce.secure.pix', ['link' => $config->link, 'pedidoId' => $pedido->id]);
        } catch (\Throwable $e) {
            $this->stockService->liberarPedido($pedido);
            report($e);
            return back()->with('mensagem_erro', $e->getMessage());
        }
    }

    public function boleto(Request $request, string $link)
    {
        $config = $this->configLoja($link);
        $pedido = $this->pedidoDaSessao($config);
        try {
            $this->stockService->reservarPedido($pedido);
            $payment = $this->mercadoPago->criarBoleto($pedido, $config);
            $pedido->refresh();
            if (in_array(strtolower((string) ($payment['status'] ?? '')), ['rejected', 'cancelled'], true)) {
                $this->stockService->liberarPedido($pedido);
                $pedido->status = 0;
                $pedido->save();
                return back()->with('mensagem_erro', 'O boleto não foi gerado pelo Mercado Pago. Tente novamente.');
            }
            return redirect()->route('ecommerce.secure.finalizado', ['link' => $config->link, 'hash' => $pedido->hash]);
        } catch (\Throwable $e) {
            $this->stockService->liberarPedido($pedido);
            report($e);
            return back()->with('mensagem_erro', $e->getMessage());
        }
    }

    public function cartao(Request $request, string $link)
    {
        $config = $this->configLoja($link);
        $pedido = $this->pedidoDaSessao($config);
        $dados = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'payment_method_id' => ['required', 'string', 'max:50'],
            'issuer_id' => ['nullable'],
            'installments' => ['required', 'integer', 'min:1', 'max:24'],
            'payer.email' => ['nullable', 'email', 'max:150'],
            'payer.identification.type' => ['nullable', 'string', 'max:10'],
            'payer.identification.number' => ['nullable', 'string', 'max:30'],
        ]);
        try {
            $this->stockService->reservarPedido($pedido);
            $payment = $this->mercadoPago->criarCartao($pedido, $config, $dados);
            $pedido->refresh();
            $status = strtolower((string) ($payment['status'] ?? $pedido->status_pagamento));
            if (in_array($status, ['rejected', 'cancelled'], true)) {
                $this->stockService->liberarPedido($pedido);
                $pedido->status = 0;
                $pedido->save();
                return response()->json(['ok' => false, 'status' => $status, 'message' => 'Pagamento não aprovado. Verifique os dados do cartão ou tente outra forma de pagamento.'], 422);
            }
            return response()->json(['ok' => true, 'status' => $status, 'redirect' => route('ecommerce.secure.finalizado', ['link' => $config->link, 'hash' => $pedido->hash])]);
        } catch (\Throwable $e) {
            $this->stockService->liberarPedido($pedido);
            report($e);
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function showPix(string $link, int $pedidoId)
    {
        $config = $this->configLoja($link);
        $pedido = $this->pedidoDoCliente($config, $pedidoId);
        abort_unless(strcasecmp((string) $pedido->forma_pagamento, 'Pix') === 0, 404);
        return view($config->tema_ecommerce . '/pix', ['pedido_pix' => $pedido, 'pedido' => $pedido, 'cliente' => $pedido->cliente, 'empresa' => $config, 'default' => $this->dadosDefault($config, $pedido), 'title' => 'Pagamento PIX - Pedido #' . $pedido->id, 'link' => $config->link, 'rota' => '/loja/' . strtolower($config->link)]);
    }

    public function status(string $link, int $pedidoId)
    {
        $config = $this->configLoja($link);
        $pedido = $this->pedidoDoCliente($config, $pedidoId);
        try {
            $payment = $this->mercadoPago->consultarPagamento($pedido, $config);
            $status = strtolower((string) ($payment['status'] ?? $pedido->status_pagamento));
            if (in_array($status, ['rejected', 'cancelled'], true)) $this->stockService->liberarPedido($pedido);
            return response()->json(['status' => $status, 'status_detail' => $payment['status_detail'] ?? $pedido->status_detalhe]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['status' => $pedido->status_pagamento ?: 'pending'], 200);
        }
    }

    public function finalizado(string $link, string $hash)
    {
        $config = $this->configLoja($link);
        $cliente = $this->clienteDaSessao($config);
        $pedido = PedidoEcommerce::where('hash', $hash)->where('empresa_id', $config->empresa_id)->where('cliente_id', $cliente->id)->firstOrFail();
        return view($config->tema_ecommerce . '/finalizado', ['pedido' => $pedido, 'default' => $this->dadosDefault($config, $pedido), 'rota' => '/loja/' . strtolower($config->link), 'title' => 'Pedido #' . $pedido->id]);
    }

    public function webhook(Request $request, int $configId)
    {
        $config = ConfigEcommerce::findOrFail($configId);
        $dataId = (string) ($request->input('data.id') ?? $request->input('id') ?? '');
        if (!$this->mercadoPago->validarAssinaturaWebhook($config, $request->header('x-signature'), $request->header('x-request-id'), $dataId)) {
            Log::warning('Webhook Mercado Pago ecommerce com assinatura inválida.', ['config_id' => $config->id, 'request_id' => $request->header('x-request-id')]);
            return response()->json(['ok' => false], 401);
        }
        if ($dataId === '') return response()->json(['ok' => true]);
        try {
            $pedido = $this->mercadoPago->sincronizarPorWebhook($config, $dataId);
            if ($pedido && in_array(strtolower((string) $pedido->status_pagamento), ['rejected', 'cancelled'], true)) $this->stockService->liberarPedido($pedido);
        } catch (\Throwable $e) {
            Log::error('Falha ao sincronizar webhook Mercado Pago ecommerce.', ['config_id' => $config->id, 'payment_id' => $dataId, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false], 500);
        }
        return response()->json(['ok' => true]);
    }

    private function dadosDefault(ConfigEcommerce $config, PedidoEcommerce $pedido): array
    {
        return ['config' => $config, 'template' => $config->tema_ecommerce, 'categorias' => CategoriaProdutoEcommerce::where('empresa_id', $config->empresa_id)->get(), 'curtidas' => (new PedidoEcommerceHelper())->getProdutosCurtidos($config->empresa_id), 'active' => '', 'carrinho' => $pedido, 'postBlogExists' => PostBlogEcommerce::where('empresa_id', $config->empresa_id)->exists(), 'rota' => '/loja/' . strtolower($config->link)];
    }
}