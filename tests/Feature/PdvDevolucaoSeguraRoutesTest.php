<?php

namespace Tests\Feature;

use App\Http\Controllers\PdvDevolucaoController;
use App\Services\NFCeCancelamentoSeguroService;
use App\Services\PdvDevolucaoFinanceiroService;
use App\Services\PdvDevolucaoService;
use Illuminate\Http\Request;
use Tests\TestCase;

class PdvDevolucaoSeguraRoutesTest extends TestCase
{
    public function test_cancelamento_nfce_legado_aponta_para_controller_seguro_web(): void
    {
        $route = app('router')->getRoutes()->match(
            Request::create('/api/nfce/cancelar', 'POST')
        );

        $this->assertStringContainsString(PdvDevolucaoController::class, $route->getActionName());

        $middlewares = $route->gatherMiddleware();
        $this->assertContains('web', $middlewares);
        $this->assertContains('verificaEmpresa', $middlewares);
        $this->assertContains('validaAcesso', $middlewares);
        $this->assertContains('verificaContratoAssinado', $middlewares);
        $this->assertContains('limiteArmazenamento', $middlewares);
        $this->assertContains('throttle:20,1', $middlewares);
        $this->assertNotContains('api', $middlewares);
    }

    public function test_delete_do_pdv_aponta_para_controller_de_devolucao_segura(): void
    {
        $route = app('router')->getRoutes()->match(
            Request::create('/frenteCaixa/123', 'DELETE')
        );

        $this->assertStringContainsString(PdvDevolucaoController::class, $route->getActionName());

        $middlewares = $route->gatherMiddleware();
        $this->assertContains('web', $middlewares);
        $this->assertContains('verificaEmpresa', $middlewares);
        $this->assertContains('validaAcesso', $middlewares);
    }

    public function test_servicos_novos_sao_autoloadaveis(): void
    {
        // class_exists força o autoload e detecta erros de sintaxe/namespaces nos
        // componentes centrais mesmo sem chamar a SEFAZ durante o teste.
        $this->assertTrue(class_exists(PdvDevolucaoController::class));
        $this->assertTrue(class_exists(PdvDevolucaoService::class));
        $this->assertTrue(class_exists(PdvDevolucaoFinanceiroService::class));
        $this->assertTrue(class_exists(NFCeCancelamentoSeguroService::class));
    }
}
