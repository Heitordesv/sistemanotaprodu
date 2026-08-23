<?php

namespace Tests\Feature;

use App\Http\Controllers\OperacionalSeguroController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SegurancaOperacionalTest extends TestCase
{
    public function test_get_legado_de_limpar_cache_foi_neutralizado(): void
    {
        $response = $this->get('/limpar-cache');

        $response->assertStatus(405);
        $response->assertJsonMissing(['status' => 'ok']);
    }

    public function test_get_legado_de_enviar_cobranca_foi_neutralizado(): void
    {
        $response = $this->get('/enviar-cobranca');

        $response->assertStatus(405);
        $response->assertJsonMissing(['status' => 'ok']);
    }

    public function test_post_operacional_aponta_para_controller_seguro_e_middlewares(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/limpar-cache', 'POST'));

        $this->assertStringContainsString(OperacionalSeguroController::class, $route->getActionName());
        $middlewares = $route->gatherMiddleware();
        $this->assertContains('verificaEmpresa', $middlewares);
        $this->assertContains('validaAcesso', $middlewares);
        $this->assertContains('verificaContratoAssinado', $middlewares);
        $this->assertContains('limiteArmazenamento', $middlewares);
    }

    public function test_controller_rejeita_usuario_comum(): void
    {
        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'adm' => 0,
            'super' => 0,
        ]]);

        $this->expectException(HttpException::class);

        (new OperacionalSeguroController())->limparCache(Request::create('/limpar-cache', 'POST'));
    }

    public function test_admin_de_empresa_sem_super_tambem_e_rejeitado(): void
    {
        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'adm' => 1,
            'super' => 0,
        ]]);

        $this->expectException(HttpException::class);

        (new OperacionalSeguroController())->limparCache(Request::create('/limpar-cache', 'POST'));
    }

    public function test_super_usuario_pode_limpar_cache_sem_expor_saida_interna(): void
    {
        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'adm' => 1,
            'super' => 1,
        ]]);

        Artisan::shouldReceive('call')
            ->once()
            ->with('optimize:clear')
            ->andReturn(0);

        $response = (new OperacionalSeguroController())
            ->limparCache(Request::create('/limpar-cache', 'POST'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('sucesso', (string) $response->getContent());
    }

    public function test_falha_de_cobranca_nao_expoe_excecao_interna(): void
    {
        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'adm' => 1,
            'super' => 1,
        ]]);

        Artisan::shouldReceive('call')
            ->once()
            ->with('notificacao:empresa-vencimento')
            ->andThrow(new \RuntimeException('SQLSTATE credencial interna secreta'));

        $response = (new OperacionalSeguroController())
            ->enviarCobranca(Request::create('/enviar-cobranca', 'POST'));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringNotContainsString('SQLSTATE', (string) $response->getContent());
        $this->assertStringNotContainsString('secreta', (string) $response->getContent());
    }
}
