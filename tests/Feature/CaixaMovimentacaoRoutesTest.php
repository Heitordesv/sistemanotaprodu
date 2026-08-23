<?php

namespace Tests\Feature;

use App\Http\Controllers\CaixaFechamentoController;
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
