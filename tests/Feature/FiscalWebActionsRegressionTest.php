<?php

namespace Tests\Feature;

use App\Http\Controllers\API\NFCeController;
use App\Http\Controllers\API\NFeController;
use Illuminate\Http\Request;
use Tests\TestCase;

class FiscalWebActionsRegressionTest extends TestCase
{
    /**
     * @dataProvider fiscalWebRoutes
     */
    public function test_acoes_fiscais_do_navegador_usam_sessao_web(
        string $uri,
        string $controller,
        string $method
    ): void {
        $route = app('router')->getRoutes()->match(
            Request::create($uri, 'POST')
        );

        $this->assertSame($controller . '@' . $method, $route->getActionName());
        $this->assertContains('web', $route->gatherMiddleware());
    }

    public static function fiscalWebRoutes(): array
    {
        return [
            'transmitir NFe' => ['/fiscal/nfe/transmitir', NFeController::class, 'transmitir'],
            'consultar NFe' => ['/fiscal/nfe/consultar', NFeController::class, 'consultarNfe'],
            'status NFe' => ['/fiscal/nfe/status-sefaz', NFeController::class, 'consultaStatusSefaz'],
            'transmitir NFCe' => ['/fiscal/nfce/transmitir', NFCeController::class, 'transmitir'],
            'consultar NFCe' => ['/fiscal/nfce/consultar', NFCeController::class, 'consultar'],
            'status NFCe' => ['/fiscal/nfce/status-sefaz', NFCeController::class, 'consultaStatusSefaz'],
        ];
    }

    public function test_javascript_nao_usa_api_sem_sessao_para_acoes_fiscais(): void
    {
        $nfe = (string) file_get_contents(public_path('js/nf.js'));
        $nfce = (string) file_get_contents(public_path('js/nfce.js'));

        $this->assertStringContainsString('fiscal/nfe/transmitir', $nfe);
        $this->assertStringContainsString('fiscal/nfe/consultar', $nfe);
        $this->assertStringContainsString('fiscal/nfce/transmitir', $nfce);
        $this->assertStringContainsString('fiscal/nfce/consultar', $nfce);
        $this->assertStringNotContainsString('api/nfe/', $nfe);
        $this->assertStringNotContainsString('api/nfce/', $nfce);
    }

    public function test_ajax_web_envia_csrf(): void
    {
        $scripts = (string) file_get_contents(
            resource_path('views/default/components/scripts.blade.php')
        );

        $this->assertStringContainsString("'X-CSRF-TOKEN'", $scripts);
        $this->assertStringContainsString('meta[name="csrf-token"]', $scripts);
    }

    public function test_middleware_web_valida_venda_e_venda_caixa_pelo_tenant_da_sessao(): void
    {
        $middleware = (string) file_get_contents(
            app_path('Http/Middleware/ResolveFiscalWebTenantContext.php')
        );

        $this->assertStringContainsString('ApiNFeController::class', $middleware);
        $this->assertStringContainsString('ApiNFCeController::class', $middleware);
        $this->assertStringContainsString('$this->guard->venda($empresaId, $resourceId)', $middleware);
        $this->assertStringContainsString('$this->guard->vendaCaixa($empresaId, $resourceId)', $middleware);
    }
}
