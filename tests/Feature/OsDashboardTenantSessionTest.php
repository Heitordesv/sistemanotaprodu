<?php

namespace Tests\Feature;

use App\Http\Controllers\API\GraficoController;
use App\Http\Controllers\API\ProdutoController;
use Illuminate\Http\Request;
use Tests\TestCase;

class OsDashboardTenantSessionTest extends TestCase
{
    public function test_busca_de_produtos_da_os_usa_rota_web_da_sessao(): void
    {
        foreach ([
            '/ordemServico/produtos/pesquisa',
            '/ordemServico/produtos/find/10',
            '/ordemServico/produtos/findByBarcode?barcode=123',
        ] as $uri) {
            $route = app('router')->getRoutes()->match(Request::create($uri, 'GET'));

            $this->assertStringContainsString(ProdutoController::class, $route->getActionName());
            $this->assertContains('web', $route->gatherMiddleware());
            $this->assertContains('verificaEmpresa', $route->gatherMiddleware());
        }
    }

    public function test_dashboard_usa_rotas_web_da_empresa_da_sessao(): void
    {
        foreach ([
            '/graficos/dados/getDataCards',
            '/graficos/dados/vendasAnual',
            '/graficos/dados/curvaABC',
            '/graficos/dados/contasReceber',
            '/graficos/dados/contasPagar',
            '/graficos/dados/contasPagarCategorias',
            '/graficos/dados/fluxoAnual',
            '/graficos/dados/produtos',
        ] as $uri) {
            $route = app('router')->getRoutes()->match(Request::create($uri, 'GET'));

            $this->assertStringContainsString(GraficoController::class, $route->getActionName());
            $this->assertContains('web', $route->gatherMiddleware());
            $this->assertContains('verificaEmpresa', $route->gatherMiddleware());
        }
    }

    public function test_consultas_compartilhadas_de_produtos_usam_empresa_da_sessao(): void
    {
        foreach ([
            '/produtos/consulta/pesquisa',
            '/produtos/consulta/find/10',
            '/produtos/consulta/findByBarcode?barcode=123',
            '/produtos/consulta/findByBarcodeReference?barcode=123',
            '/produtos/consulta/findProdRemessa?produto_id=10',
        ] as $uri) {
            $route = app('router')->getRoutes()->match(Request::create($uri, 'GET'));

            $this->assertStringContainsString(ProdutoController::class, $route->getActionName());
            $this->assertContains('web', $route->gatherMiddleware());
            $this->assertContains('verificaEmpresa', $route->gatherMiddleware());
        }
    }

    public function test_javascript_nao_depende_mais_das_apis_com_hash(): void
    {
        $ordem = (string) file_get_contents(resource_path('views/ordem_servico/ordem_completa.blade.php'));
        $ordemJs = (string) file_get_contents(public_path('js/ordem_servico.js'));
        $grafico = (string) file_get_contents(public_path('js/grafico.js'));
        $scripts = (string) file_get_contents(resource_path('views/default/components/scripts.blade.php'));

        $this->assertStringContainsString("route('ordemServico.produtos.pesquisa')", $ordem);
        $this->assertStringNotContainsString('api/produtos/', $ordemJs);
        $this->assertStringNotContainsString("ajaxGet('api/graficos/", $grafico);
        $this->assertStringContainsString("ajaxGet('graficos/dados/", $grafico);
        $this->assertStringContainsString('window.pdvProdutoEndpoints = window.produtoWebEndpoints', $scripts);

        foreach (['vendas.js', 'compra.js', 'pre_venda.js', 'pedidos.js', 'nfeRemessa.js'] as $arquivo) {
            $javascript = (string) file_get_contents(public_path('js/' . $arquivo));
            $this->assertStringNotContainsString('api/produtos/find', $javascript);
        }
    }
}
