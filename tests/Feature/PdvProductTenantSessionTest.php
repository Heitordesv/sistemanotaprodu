<?php

namespace Tests\Feature;

use App\Http\Controllers\FrontBoxController;
use App\Http\Middleware\ResolveCashTenantContext;
use Illuminate\Http\Request;
use Tests\TestCase;

class PdvProductTenantSessionTest extends TestCase
{
    public function test_consulta_de_produtos_substitui_empresa_do_navegador_pela_empresa_da_sessao(): void
    {
        session(['user_logged' => [
            'id' => 7,
            'empresa' => 22,
        ]]);

        $request = Request::create(
            '/frenteCaixa/produtos/pesquisa',
            'GET',
            ['empresa_id' => 999, 'pesquisa' => 'produto']
        );
        $request->setRouteResolver(fn () => new PdvProductTenantTestRoute(
            FrontBoxController::class . '@produtosPesquisa'
        ));

        $response = (new ResolveCashTenantContext())->handle(
            $request,
            fn ($resolvedRequest) => response()->json([
                'empresa_id' => (int) $resolvedRequest->empresa_id,
            ])
        );

        $this->assertSame(22, $response->getData(true)['empresa_id']);
        $this->assertSame(22, (int) $request->empresa_id);
    }

    public function test_endpoints_de_produto_do_pdv_sao_rotas_web_com_sessao(): void
    {
        foreach ([
            '/frenteCaixa/produtos/pesquisa',
            '/frenteCaixa/produtos/find/10',
            '/frenteCaixa/produtos/findByBarcode',
            '/frenteCaixa/produtos/findByBarcodeReference',
        ] as $uri) {
            $route = app('router')->getRoutes()->match(Request::create($uri, 'GET'));

            $this->assertStringContainsString(FrontBoxController::class, $route->getActionName());
            $this->assertContains('web', $route->gatherMiddleware());
        }
    }

    public function test_javascript_do_pdv_usa_endpoints_de_produto_da_sessao(): void
    {
        $layout = (string) file_get_contents(resource_path('views/frontBox/index.blade.php'));
        $main = (string) file_get_contents(public_path('js/main.js'));
        $frontBox = (string) file_get_contents(public_path('js/frontBox.js'));

        $this->assertStringContainsString('window.pdvProdutoEndpoints', $layout);
        $this->assertStringContainsString("route('frenteCaixa.produtos.pesquisa')", $layout);
        $this->assertStringContainsString('window.pdvProdutoEndpoints.pesquisa', $main);
        $this->assertStringContainsString('window.pdvProdutoEndpoints.findByBarcode', $frontBox);
        $this->assertStringContainsString('window.pdvProdutoEndpoints.findByBarcodeReference', $frontBox);
    }
}

class PdvProductTenantTestRoute
{
    public function __construct(private string $action)
    {
    }

    public function getActionName(): string
    {
        return $this->action;
    }
}
