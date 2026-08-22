<?php

namespace App\Providers;

use App\Models\ConfigNota;
use App\Models\Usuario;
use App\Models\VideoAjuda;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            \App\Services\SistemaAiFinancialContextService::class,
            \App\Services\SistemaAiFinancialSpecialistContextService::class
        );
    }

    public function boot()
    {
        Paginator::useBootstrap();

        view()->composer('*', function ($view) {
            /*
             * O composer global é chamado para cada Blade/include renderizado.
             * Mantemos a compatibilidade do projeto, mas os dados globais são
             * carregados apenas UMA vez por request e reutilizados nas demais views.
             */
            static $shared = null;
            static $skip = false;

            if ($skip) {
                return;
            }

            if ($shared === null) {
                $userId = get_id_user();
                if (!$userId) {
                    $skip = true;
                    return;
                }

                $user = Usuario::with('theme')->find($userId);
                if (!$user) {
                    $skip = true;
                    return;
                }

                if (!ConfigNota::where('empresa_id', $user->empresa_id)->exists()) {
                    if (!session()->has('error')) {
                        session()->flash('error', 'Configuração não encontrada para esta empresa.');
                    }
                    $skip = true;
                    return;
                }

                $theme = $user->theme;
                $colorDefault = $this->themeColor($theme?->cabecalho);

                $shared = [
                    'casasDecimais' => 2,
                    'user' => $user,
                    'ultimoAcesso' => $user->ultimoAcesso(),
                    'colorDefault' => $colorDefault,
                    'theme' => $theme,
                    'rotaAtiva' => $this->rotaAtiva(),
                    'video_url' => $this->getVideoUrl(),
                    'audio' => $user->aviso_sonoro,
                ];
            }

            foreach ($shared as $key => $value) {
                $view->with($key, $value);
            }
        });
    }

    private function themeColor(?string $cabecalho): string
    {
        return [
            'headercolor1' => '#0727D7',
            'headercolor2' => '#23282C',
            'headercolor3' => '#E10A1F',
            'headercolor4' => '#157D4C',
            'headercolor5' => '#673AB7',
            'headercolor6' => '#795548',
            'headercolor7' => '#D3094E',
            'headercolor8' => '#FF9800',
        ][$cabecalho] ?? '';
    }

    private function getVideoUrl(): string
    {
        $url = url()->full();
        if (!$url) {
            return '';
        }

        try {
            return (string) (VideoAjuda::where('url_sistema', $url)->value('url_video') ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function rotaAtiva(): string
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return '';
        }

        $parts = explode('/', $_SERVER['REQUEST_URI']);
        $uri = $parts[1] ?? '';

        $grupos = [
            'SUPER' => [
                'empresas', 'planos', 'ibpt', 'contrato', 'financeiro', 'cidades', 'representantes',
                'online', 'etiquetas', 'relatorioSuper', 'ticketsSuper', 'cidadeDelivery',
                'categoriaMasterDelivery', 'produtosDestaque', 'planosPendentes', 'pesquisa', 'alertas',
                'errosLog', 'config', 'appUpdate'
            ],
            'Cadastros' => [
                'categorias', 'produtos', 'clientes', 'fornecedores', 'transportadoras', 'categoria-servico', 'servicos',
                'categoriasConta', 'veiculos', 'usuarios', 'marcas', 'contaBancaria', 'acessores', 'gruposCliente',
                'listaDePrecos', 'formasPagamento'
            ],
            'Entradas' => ['compraFiscal', 'compraManual', 'compras', 'cotacao', 'dfe', 'devolucao'],
            'Gestão Pessoal' => ['funcionarios', 'eventosFuncionario', 'funcionarioEventos', 'apuracaoMensal'],
            'Estoque' => ['estoque', 'inventario', 'transferencia'],
            'Financeiro' => ['conta-pagar', 'conta-receber', 'fluxoCaixa', 'graficos'],
            'Configurações' => [
                'configNF', 'escritorio', 'naturezas', 'tributos', 'enviarXml', 'tickets', 'configEmail', 'filial'
            ],
            'Pedidos' => ['pedidos', 'deliveryComplemento', 'telasPedido', 'controleCozinha', 'mesas'],
            'Vendas' => [
                'caixa', 'vendas', 'frenteCaixa', 'orcamentoVenda', 'ordemServico', 'vendasEmCredito',
                'agendamentos', 'trocas', 'nfse', 'nferemessa'
            ],
            'CTe' => ['cte', 'categoriaDespesa'],
            'CTe Os' => ['cteos'],
            'MDFe' => ['mdfe'],
            'Eventos' => ['eventos'],
            'Locação' => ['locacao'],
            'Relatórios' => ['relatorios', 'dre'],
            'Ecommerce' => [
                'categoriaEcommerce', 'produtoEcommerce', 'configEcommerce', 'carrosselEcommerce',
                'pedidosEcommerce', 'autorPost', 'categoriaPosts', 'postBlog', 'contatoEcommerce',
                'clienteEcommerce', 'informativoEcommerce', 'cuponsEcommerce'
            ],
            'Nuvem Shop' => ['nuvemshop', 'nuvemshop-pedidos', 'nuvemshop-produtos', 'nuvemshop-clientes'],
            'iFood' => ['ifood'],
            'Delivery' => [
                'deliveryCategoria', 'configDelivery', 'deliveryProduto', 'deliveryComplemento',
                'funcionamentoDelivery', 'push', 'tamanhosPizza', 'clientesDelivery', 'categoriaDeLoja',
                'pedidosDelivery', 'bairrosDeliveryLoja', 'codigoDesconto', 'carrosselDelivery',
                'motoboys', 'pedidosMesa', 'mesas'
            ],
        ];

        foreach ($grupos as $grupo => $rotas) {
            if (in_array($uri, $rotas, true)) {
                return $grupo;
            }
        }

        return '';
    }
}