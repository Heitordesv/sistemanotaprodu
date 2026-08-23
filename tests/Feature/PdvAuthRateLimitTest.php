<?php

namespace Tests\Feature;

use App\Http\Controllers\Pdv\LoginController;
use App\Http\Middleware\AuthPdv;
use App\Models\Usuario;
use App\Services\PdvTokenService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PdvAuthRateLimitTest extends TestCase
{
    private string $usuariosTable;
    private string $rateKey = 'pdv-login|127.0.0.1|operador';

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuariosTable = (new Usuario())->getTable();
        Schema::dropIfExists($this->usuariosTable);

        Schema::create($this->usuariosTable, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome')->nullable();
            $table->string('login');
            $table->string('senha');
            $table->unsignedTinyInteger('ativo')->nullable();
            $table->string('img')->nullable();
        });

        DB::table($this->usuariosTable)->insert([
            'id' => 7,
            'empresa_id' => 10,
            'nome' => 'Operador PDV',
            'login' => 'operador',
            'senha' => md5('senha-correta'),
            'ativo' => 1,
            'img' => null,
        ]);

        RateLimiter::clear($this->rateKey);
    }

    protected function tearDown(): void
    {
        RateLimiter::clear($this->rateKey);
        Schema::dropIfExists($this->usuariosTable);
        parent::tearDown();
    }

    public function test_request_sem_token_falha_fechado(): void
    {
        $request = Request::create('/api/pdv/produtos', 'GET', ['empresa_id' => 999]);

        $response = (new AuthPdv(new PdvTokenService()))->handle(
            $request,
            fn () => response()->json(['ok' => true])
        );

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(999, (int) $request->empresa_id);
    }

    public function test_login_e_bloqueado_apos_dez_tentativas_invalidas(): void
    {
        $controller = new LoginController(new PdvTokenService());

        for ($i = 0; $i < 10; $i++) {
            $response = $controller->login($this->loginRequest('senha-errada'));
            $this->assertSame(401, $response->getStatusCode());
        }

        $blocked = $controller->login($this->loginRequest('senha-correta'));

        $this->assertSame(429, $blocked->getStatusCode());
        $this->assertTrue(RateLimiter::tooManyAttempts($this->rateKey, 10));
    }

    private function loginRequest(string $senha): Request
    {
        $request = Request::create('/api/pdv/login', 'POST', [
            'login' => 'operador',
            'senha' => $senha,
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        return $request;
    }
}
