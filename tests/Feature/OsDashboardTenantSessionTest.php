<?php

namespace Tests\Feature;

use App\Http\Controllers\API\GraficoController;
use App\Http\Controllers\API\ProdutoController;
use App\Http\Middleware\ResolveFiscalWebTenantContext;
use App\Services\FiscalTenantGuardService;
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
        $this->assertStringNotContainsString('api/produtos/pesquisa', (string) file_get_contents(public_path('js/main.js')));

        foreach (['vendas.js', 'compra.js', 'pre_venda.js', 'pedidos.js', 'nfeRemessa.js'] as $arquivo) {
            $javascript = (string) file_get_contents(public_path('js/' . $arquivo));
            $this->assertStringNotContainsString('api/produtos/find', $javascript);
        }
    }

    public function test_telas_compartilhadas_invalidam_cache_do_seletor_de_produtos(): void
    {
        $scripts = (string) file_get_contents(
            resource_path('views/default/components/scripts.blade.php')
        );
        $preVenda = (string) file_get_contents(
            resource_path('views/pre_venda/index.blade.php')
        );
        $pdv = (string) file_get_contents(
            resource_path('views/frontBox/index.blade.php')
        );

        foreach ([$scripts, $preVenda, $pdv] as $view) {
            $this->assertStringContainsString('js/main.js', $view);
            $this->assertStringContainsString('filemtime(', $view);
        }
    }

    public function test_busca_web_retorna_payload_enxuto_e_tolera_utf8_legado(): void
    {
        $controller = (string) file_get_contents(
            app_path('Http/Controllers/API/ProdutoController.php')
        );

        $this->assertStringContainsString('produtoParaPesquisaWeb', $controller);
        $this->assertStringContainsString('JSON_INVALID_UTF8_SUBSTITUTE', $controller);
        $this->assertStringContainsString("'estoqueAtual' =>", $controller);
    }

    public function test_busca_e_dashboard_substituem_empresa_do_navegador_pela_sessao(): void
    {
        foreach ([
            ProdutoController::class . '@pesquisaWeb',
            GraficoController::class . '@getDataCards',
        ] as $action) {
            $guard = new OsDashboardRecordingTenantGuard();
            $request = Request::create('/consulta', 'GET', ['empresa_id' => 999]);
            $request->setRouteResolver(fn () => new OsDashboardTenantTestRoute($action));

            (new ResolveFiscalWebTenantContext($guard))->handle(
                $request,
                fn ($resolvedRequest) => response()->json(['ok' => true])
            );

            $this->assertSame(22, (int) $request->empresa_id);
            $this->assertSame([['web_tenant', 22]], $guard->calls);
        }
    }

    public function test_front_nao_envia_empresa_como_identidade_do_dashboard(): void
    {
        $grafico = (string) file_get_contents(public_path('js/grafico.js'));
        $this->assertStringNotContainsString("empresa_id: $('#empresa_id').val()", $grafico);
        $this->assertStringContainsString('ProdutoController@pesquisaWeb', (string) file_get_contents(base_path('routes/web.php')));
    }

    public function test_busca_mantem_produtos_legados_sem_local_definido(): void
    {
        $controller = new ProdutoController();
        $method = new \ReflectionMethod($controller, 'produtoDisponivelNoLocal');

        $this->assertTrue($method->invoke($controller, null, -1));
        $this->assertTrue($method->invoke($controller, [], 8));
        $this->assertTrue($method->invoke($controller, [-1], -1));
        $this->assertTrue($method->invoke($controller, [8], 8));
        $this->assertFalse($method->invoke($controller, [9], 8));
    }

    public function test_busca_normaliza_local_sem_perder_a_matriz(): void
    {
        $controller = new ProdutoController();
        $method = new \ReflectionMethod($controller, 'normalizarFilialConsulta');

        $this->assertNull($method->invoke($controller, null));
        $this->assertNull($method->invoke($controller, 'todos'));
        $this->assertSame(-1, $method->invoke($controller, '-1'));
        $this->assertSame(8, $method->invoke($controller, '8'));
    }
}

class OsDashboardTenantTestRoute
{
    public function __construct(private string $action)
    {
    }

    public function getActionName(): string
    {
        return $this->action;
    }

    public function parameters(): array
    {
        return [];
    }
}

class OsDashboardRecordingTenantGuard extends FiscalTenantGuardService
{
    public array $calls = [];

    public function empresaIdDaSessao(Request $request): int
    {
        $request->merge(['empresa_id' => 22]);
        $request->attributes->set(self::VERIFIED_TENANT_ATTRIBUTE, 22);
        $this->calls[] = ['web_tenant', 22];

        return 22;
    }
}
