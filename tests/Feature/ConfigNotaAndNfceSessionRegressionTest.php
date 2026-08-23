<?php

namespace Tests\Feature;

use App\Http\Controllers\FrontBoxController;
use App\Http\Middleware\ResolveCashTenantContext;
use Illuminate\Http\Request;
use Tests\TestCase;

class ConfigNotaAndNfceSessionRegressionTest extends TestCase
{
    public function test_emissao_nfce_do_pdv_e_uma_rota_web_da_sessao(): void
    {
        $route = app('router')->getRoutes()->match(
            Request::create('/frenteCaixa/nfce/transmitir', 'POST')
        );

        $this->assertSame(
            FrontBoxController::class . '@transmitirNfce',
            $route->getActionName()
        );
        $this->assertContains('web', $route->gatherMiddleware());
    }

    public function test_emissao_nfce_substitui_empresa_do_navegador_pela_da_sessao(): void
    {
        session(['user_logged' => [
            'id' => 7,
            'empresa' => 22,
        ]]);

        $request = Request::create(
            '/frenteCaixa/nfce/transmitir',
            'POST',
            ['id' => 10, 'empresa_id' => 999]
        );
        $request->setRouteResolver(fn () => new NfceSessionTestRoute(
            FrontBoxController::class . '@transmitirNfce'
        ));

        $response = (new ResolveCashTenantContext())->handle(
            $request,
            fn ($resolvedRequest) => response()->json([
                'empresa_id' => (int) $resolvedRequest->empresa_id,
            ])
        );

        $this->assertSame(22, $response->getData(true)['empresa_id']);
    }

    public function test_javascript_da_nfce_usa_rota_web_com_csrf(): void
    {
        $layout = (string) file_get_contents(resource_path('views/frontBox/index.blade.php'));
        $frontBox = (string) file_get_contents(public_path('js/frontBox.js'));

        $this->assertStringContainsString("route('frenteCaixa.nfce.transmitir')", $layout);
        $this->assertStringContainsString("'X-CSRF-TOKEN'", $layout);
        $this->assertStringContainsString('window.pdvNfceTransmitirUrl', $frontBox);
    }

    public function test_configuracao_fiscal_persiste_campos_necessarios_para_nfce(): void
    {
        $controller = (string) file_get_contents(
            app_path('Http/Controllers/ConfigNotaController.php')
        );

        foreach ([
            "'ambiente' =>",
            "'numero_serie_nfce' =>",
            "'ultimo_numero_nfce' =>",
            "'CST_CSOSN_padrao' =>",
            "'CST_PIS_padrao' =>",
            "'CST_COFINS_padrao' =>",
            "'csc' =>",
            "'csc_id' =>",
        ] as $campo) {
            $this->assertStringContainsString($campo, $controller);
        }
    }
}

class NfceSessionTestRoute
{
    public function __construct(private string $action)
    {
    }

    public function getActionName(): string
    {
        return $this->action;
    }
}
