<?php

namespace Tests\Feature;

use App\Http\Controllers\DfeController;
use App\Http\Controllers\API\ProdutoController;
use Illuminate\Http\Request;
use Tests\TestCase;

class DfeEntradaMercadoriaRegressionTest extends TestCase
{
    public function test_consulta_e_cadastro_rapido_usam_rotas_web(): void
    {
        $consulta = app('router')->getRoutes()->match(
            Request::create('/dfe/getDocumentosNovos', 'GET')
        );
        $produto = app('router')->getRoutes()->match(
            Request::create('/dfe/produtos/store', 'POST')
        );

        $this->assertSame(DfeController::class . '@getDocumentosNovos', $consulta->getActionName());
        $this->assertSame(ProdutoController::class . '@store', $produto->getActionName());
        $this->assertContains('web', $consulta->gatherMiddleware());
        $this->assertContains('web', $produto->gatherMiddleware());
    }

    public function test_javascript_do_dfe_nao_chama_endpoints_api_sem_sessao(): void
    {
        $consulta = (string) file_get_contents(public_path('js/dfe.js'));
        $manifesto = (string) file_get_contents(public_path('js/manifestoDfe.js'));

        $this->assertStringContainsString('window.dfeEndpoints', $consulta);
        $this->assertStringContainsString('window.dfeEndpoints', $manifesto);
        $this->assertStringNotContainsString('api/dfe/', $consulta);
        $this->assertStringNotContainsString('api/produtos/store', $manifesto);
        $this->assertStringNotContainsString('api/categorias/buscarSubCategoria', $manifesto);
        $this->assertStringNotContainsString('api/conta-pagar/faturaManifesto', $manifesto);
    }

    public function test_controller_isola_recursos_e_grava_compra_em_transacao(): void
    {
        $controller = (string) file_get_contents(app_path('Http/Controllers/DfeController.php'));
        $middleware = (string) file_get_contents(
            app_path('Http/Middleware/ResolveFiscalWebTenantContext.php')
        );

        $this->assertStringContainsString('DfeController::class', $middleware);
        $this->assertStringContainsString("where('empresa_id', \$empresaId)", $controller);
        $this->assertStringContainsString('DB::transaction(function () use ($request)', $controller);
        $this->assertStringNotContainsString('ManifestaDfe::findOrFail', $controller);
        $this->assertStringNotContainsString('Fornecedor::findOrFail', $controller);
        $this->assertStringNotContainsString('Produto::findOrFail', $controller);
    }
}
