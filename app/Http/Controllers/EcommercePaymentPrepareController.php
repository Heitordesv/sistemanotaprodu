<?php

namespace App\Http\Controllers;

use App\Helpers\PedidoEcommerceHelper;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\ClienteEcommerce;
use App\Models\ConfigEcommerce;
use App\Models\EnderecoEcommerce;
use App\Models\PedidoEcommerce;
use App\Models\PostBlogEcommerce;
use App\Services\EcommerceCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EcommercePaymentPrepareController extends Controller
{
    public function __construct(private EcommerceCheckoutService $checkoutService)
    {
    }

    public function pagamento(Request $request, string $link)
    {
        [$config, $cliente, $pedido] = $this->contexto($link);

        $data = $request->validate([
            'endereco_id' => ['nullable', 'integer'],
            'endereco' => ['nullable'],
            'tipo' => ['required', Rule::in(['pac', 'sedex', 'gratis', 'retirada'])],
            'cupom_desconto' => ['nullable', 'string', 'max:50'],
        ]);

        $enderecoId = (int) ($data['endereco_id'] ?? 0);
        if ($enderecoId <= 0 && !empty($data['endereco'])) {
            $decoded = is_string($data['endereco']) ? json_decode($data['endereco'], true) : $data['endereco'];
            $enderecoId = (int) ($decoded['id'] ?? 0);
        }

        $endereco = EnderecoEcommerce::where('id', $enderecoId)
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        try {
            return $this->montarTelaPagamento(
                $config,
                $cliente,
                $pedido,
                $endereco,
                (string) $data['tipo'],
                $data['cupom_desconto'] ?? null
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect('/loja/' . strtolower($config->link) . '/endereco?tipo_frete=' . urlencode((string) $data['tipo']))
                ->withInput()
                ->with('flash_erro', $e->getMessage());
        }
    }

    public function mostrar(string $link)
    {
        [$config, $cliente, $pedido] = $this->contexto($link);

        $tipoFrete = strtolower(trim((string) $pedido->tipo_frete));
        if (!in_array($tipoFrete, ['pac', 'sedex', 'gratis', 'retirada'], true) || !$pedido->endereco_id) {
            return redirect('/loja/' . strtolower($config->link) . '/endereco')
                ->with('flash_erro', 'Selecione o endereço e a forma de entrega antes de continuar.');
        }

        $endereco = EnderecoEcommerce::where('id', $pedido->endereco_id)
            ->where('cliente_id', $cliente->id)
            ->first();

        if (!$endereco) {
            return redirect('/loja/' . strtolower($config->link) . '/endereco')
                ->with('flash_erro', 'O endereço selecionado não está mais disponível. Escolha outro endereço.');
        }

        try {
            return $this->montarTelaPagamento(
                $config,
                $cliente,
                $pedido,
                $endereco,
                $tipoFrete,
                $pedido->cupom_desconto ?: null
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect('/loja/' . strtolower($config->link) . '/endereco?tipo_frete=' . urlencode($tipoFrete))
                ->with('flash_erro', $e->getMessage());
        }
    }

    private function contexto(string $link): array
    {
        $config = ConfigEcommerce::where('link', strtolower($link))->firstOrFail();
        $sessao = session('user_ecommerce');

        abort_if(!$sessao, 403, 'Faça login para continuar.');
        abort_if((int) ($sessao['empresa_id'] ?? 0) !== (int) $config->empresa_id, 403, 'Sessão inválida para esta loja.');

        $cliente = ClienteEcommerce::where('id', $sessao['cliente_id'])
            ->where('empresa_id', $config->empresa_id)
            ->where('status', 1)
            ->firstOrFail();

        $pedido = (new PedidoEcommerceHelper())->getCarrinho($config->empresa_id);
        abort_if(!$pedido, 404, 'Carrinho não encontrado.');
        abort_if((int) $pedido->empresa_id !== (int) $config->empresa_id, 403, 'Pedido inválido para esta loja.');
        abort_if((int) $pedido->cliente_id !== (int) $cliente->id, 403, 'Pedido inválido para o cliente logado.');

        return [$config, $cliente, $pedido];
    }

    private function montarTelaPagamento(
        ConfigEcommerce $config,
        ClienteEcommerce $cliente,
        PedidoEcommerce $pedido,
        EnderecoEcommerce $endereco,
        string $tipoFrete,
        ?string $cupom = null
    ) {
        $resumo = $this->checkoutService->resumo(
            $pedido,
            $config,
            $endereco,
            $tipoFrete,
            $cupom
        );

        $pedido = $this->checkoutService->salvarResumo($pedido, $endereco, $resumo);
        $totais = $this->checkoutService->totaisPorFormaPagamento($resumo['total'], $config);

        $formasConfiguradas = json_decode((string) $config->formas_pagamento, true);
        $formas = array_values(array_intersect(
            is_array($formasConfiguradas) ? $formasConfiguradas : [],
            ['pix', 'cartao', 'boleto']
        ));

        if (empty($formas)) {
            throw new \RuntimeException('Nenhuma forma de pagamento está habilitada nesta loja.');
        }

        $default = [
            'config' => $config,
            'template' => $config->tema_ecommerce,
            'categorias' => CategoriaProdutoEcommerce::where('empresa_id', $config->empresa_id)->get(),
            'curtidas' => (new PedidoEcommerceHelper())->getProdutosCurtidos($config->empresa_id),
            'active' => '',
            'carrinho' => $pedido,
            'postBlogExists' => PostBlogEcommerce::where('empresa_id', $config->empresa_id)->exists(),
            'rota' => '/loja/' . strtolower($config->link),
        ];

        return view($config->tema_ecommerce . '/pay', [
            'default' => $default,
            'carrinho' => $pedido,
            'cliente' => $cliente,
            'descricao' => 'Pedido Ecommerce #' . $pedido->id,
            'total' => $resumo['total'],
            'formas_pagamento' => $formas,
            'forma_inicial' => $formas[0],
            'totais' => $totais,
            'cart' => true,
            'rota' => $default['rota'],
            'title' => 'Pagamento do Pedido',
        ]);
    }
}