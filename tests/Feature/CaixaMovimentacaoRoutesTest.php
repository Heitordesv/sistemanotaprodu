<?php

namespace Tests\Feature;

use App\Http\Controllers\CaixaFechamentoController;
use App\Http\Controllers\FrontBoxResumoController;
use App\Http\Controllers\VendaSeguraController;
use App\Models\AberturaCaixa;
use App\Services\CaixaFechamentoService;
use Illuminate\Http\Request;
use Tests\TestCase;

class CaixaMovimentacaoRoutesTest extends TestCase
{
    public function test_movimentos_que_afetam_caixa_estao_protegidos_pelo_middleware_de_lock(): void
    {
        $this->assertRouteHasMiddleware('/frenteCaixa', 'POST', 'caixaMovimento:obrigatorio');
        $this->assertRouteHasMiddleware('/sangriaCaixa', 'POST', 'caixaMovimento:obrigatorio');
        $this->assertRouteHasMiddleware('/suprimentoCaixa', 'POST', 'caixaMovimento:obrigatorio');
        $this->assertRouteHasMiddleware('/vendas', 'POST', 'caixaMovimento:venda-opcional');
    }

    public function test_rotas_de_venda_e_pdv_usam_controllers_endurecidos(): void
    {
        $vendaStore = app('router')->getRoutes()->match(Request::create('/vendas', 'POST'));
        $vendaUpdate = app('router')->getRoutes()->match(Request::create('/vendas/10', 'PUT'));
        $vendaDestroy = app('router')->getRoutes()->match(Request::create('/vendas/10', 'DELETE'));
        $pdvIndex = app('router')->getRoutes()->match(Request::create('/frenteCaixa', 'GET'));
        $pdvStore = app('router')->getRoutes()->match(Request::create('/frenteCaixa', 'POST'));

        $this->assertStringContainsString(VendaSeguraController::class, $vendaStore->getActionName());
        $this->assertStringContainsString(VendaSeguraController::class, $vendaUpdate->getActionName());
        $this->assertStringContainsString(VendaSeguraController::class, $vendaDestroy->getActionName());
        $this->assertStringContainsString(FrontBoxResumoController::class, $pdvIndex->getActionName());
        $this->assertStringContainsString(FrontBoxResumoController::class, $pdvStore->getActionName());
    }

    public function test_tela_caixa_nao_bloqueia_fechamento_quando_nao_ha_vendas(): void
    {
        $blade = (string) file_get_contents(resource_path('views/caixa/index.blade.php'));

        $this->assertStringNotContainsString(
            'Não é possível fechar o Caixa',
            $blade,
            'A tela /caixa não pode exigir venda para permitir o fechamento.'
        );
        $this->assertStringNotContainsString(
            "sizeof(\$caixa['vendas']) == 0) disabled",
            $blade,
            'O botão de fechamento não pode ser desabilitado quando vendas == 0.'
        );
        $this->assertStringContainsString(
            "\$caixa['dinheiroNaGaveta']",
            $blade,
            'A tela deve exibir o dinheiro da gaveta calculado pelo servidor.'
        );
        $this->assertStringContainsString(
            "\$caixa['totalRecebimentos']",
            $blade,
            'A tela deve considerar recebimentos mesmo sem vendas.'
        );
    }

    public function test_sangria_usa_dinheiro_na_gaveta_e_nao_valor_bruto_das_vendas(): void
    {
        $controller = (string) file_get_contents(app_path('Http/Controllers/SangriaCaixaController.php'));

        $this->assertStringContainsString('CaixaResumoService', $controller);
        $this->assertStringContainsString("['dinheiroNaGaveta']", $controller);
        $this->assertStringNotContainsString("sum('valor_total')", $controller);
        $this->assertStringNotContainsString('use App\\Models\\VendaCaixa;', $controller);
        $this->assertStringNotContainsString('use App\\Models\\Venda;', $controller);
    }

    public function test_fechamento_nao_redireciona_para_host_externo_informado_pelo_cliente(): void
    {
        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
        ]]);

        $request = Request::create('/frenteCaixa/fechar', 'POST', [
            'abertura_id' => 10,
            'redirect' => 'https://evil.example/roubar-sessao',
        ]);

        $service = new class extends CaixaFechamentoService {
            public function fechar(int $aberturaId, int $empresaId, int $usuarioId): AberturaCaixa
            {
                return new AberturaCaixa();
            }
        };

        $response = (new CaixaFechamentoController())->fechar($request, $service);

        $this->assertSame(route('frenteCaixa.list'), $response->getTargetUrl());
    }

    public function test_fechamento_preserva_redirect_interno_relativo(): void
    {
        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
        ]]);

        $request = Request::create('/frenteCaixa/fechar', 'POST', [
            'abertura_id' => 10,
            'redirect' => '/frenteCaixa/list?origem=fechamento',
        ]);

        $service = new class extends CaixaFechamentoService {
            public function fechar(int $aberturaId, int $empresaId, int $usuarioId): AberturaCaixa
            {
                return new AberturaCaixa();
            }
        };

        $response = (new CaixaFechamentoController())->fechar($request, $service);

        $this->assertStringEndsWith('/frenteCaixa/list?origem=fechamento', $response->getTargetUrl());
    }

    private function assertRouteHasMiddleware(string $uri, string $method, string $middleware): void
    {
        $request = Request::create($uri, $method);
        $route = app('router')->getRoutes()->match($request);

        $this->assertContains(
            $middleware,
            $route->gatherMiddleware(),
            "A rota {$method} {$uri} precisa usar {$middleware}."
        );
    }
}
