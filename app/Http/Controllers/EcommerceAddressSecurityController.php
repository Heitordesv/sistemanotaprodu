<?php

namespace App\Http\Controllers;

use App\Helpers\PedidoEcommerceHelper;
use App\Models\ClienteEcommerce;
use App\Models\ConfigEcommerce;
use App\Models\PedidoEcommerce;
use App\Services\EcommerceCheckoutService;
use Illuminate\Http\Request;

class EcommerceAddressSecurityController extends Controller
{
    public function __construct(private EcommerceCheckoutService $checkoutService)
    {
    }

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

    private function carrinhoDaSessao(ConfigEcommerce $config, ClienteEcommerce $cliente): PedidoEcommerce
    {
        $pedido = (new PedidoEcommerceHelper())->getCarrinho($config->empresa_id);
        abort_if(!$pedido, 404, 'Carrinho não encontrado.');
        abort_if((int) $pedido->empresa_id !== (int) $config->empresa_id, 403, 'Pedido inválido para esta loja.');
        abort_if((int) $pedido->cliente_id !== (int) $cliente->id, 403, 'Pedido inválido para o cliente logado.');

        return $pedido;
    }

    public function endereco(Request $request, string $link)
    {
        $config = $this->configLoja($link);
        $cliente = $this->clienteDaSessao($config);
        $pedido = $this->carrinhoDaSessao($config, $cliente);

        $tipoFrete = strtolower(trim((string) ($request->query('tipo_frete') ?: $request->query('tp_frete') ?: $pedido->tipo_frete ?: '')));
        if (!in_array($tipoFrete, ['pac', 'sedex', 'gratis', 'retirada'], true)) {
            $tipoFrete = '';
        }

        $enderecos = $cliente->enderecos;
        $total = (float) $pedido->somaItens();

        foreach ($enderecos as $endereco) {
            $opcoes = $this->checkoutService->opcoesFrete($pedido, $config, (string) $endereco->cep);
            $endereco->preco_sedex = $opcoes['preco_sedex'] ?? '0,00';
            $endereco->prazo_sedex = $opcoes['prazo_sedex'] ?? '0';
            $endereco->preco = $opcoes['preco'] ?? '0,00';
            $endereco->prazo = $opcoes['prazo'] ?? '0';
            $endereco->frete_gratis = (int) ($opcoes['frete_gratis'] ?? 0);
            $endereco->habilitar_retirada = (int) ($opcoes['habilitar_retirada'] ?? 0);
        }

        $default = [
            'config' => $config,
            'template' => $config->tema_ecommerce,
            'categorias' => \App\Models\CategoriaProdutoEcommerce::where('empresa_id', $config->empresa_id)->get(),
            'curtidas' => (new PedidoEcommerceHelper())->getProdutosCurtidos($config->empresa_id),
            'active' => '',
            'carrinho' => $pedido,
            'postBlogExists' => \App\Models\PostBlogEcommerce::where('empresa_id', $config->empresa_id)->exists(),
            'rota' => '/loja/' . strtolower($config->link),
        ];

        return view($config->tema_ecommerce . '/selecionar_endereco', [
            'default' => $default,
            'carrinho' => $pedido,
            'enderecos' => $enderecos,
            'cliente' => $cliente,
            'total' => $total,
            'contato' => true,
            'tipoFrete' => $tipoFrete,
            'rota' => $default['rota'],
            'title' => 'Selecionar endereço e entrega',
        ]);
    }

    public function pagamentoGet(string $link)
    {
        $config = $this->configLoja($link);
        $this->clienteDaSessao($config);

        return redirect('/loja/' . strtolower($config->link) . '/endereco')
            ->with('flash_erro', 'Escolha o endereço e a forma de entrega para continuar ao pagamento.');
    }
}