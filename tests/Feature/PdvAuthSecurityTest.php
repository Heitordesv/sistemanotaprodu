<?php

namespace Tests\Feature;

use App\Http\Controllers\Pdv\ConfigController;
use App\Http\Controllers\Pdv\LoginController;
use App\Http\Middleware\AuthPdv;
use App\Models\Usuario;
use App\Services\PdvTokenService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PdvAuthSecurityTest extends TestCase
{
    private string $usuariosTable;

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

        RateLimiter::clear('pdv-login|127.0.0.1|operador');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        RateLimiter::clear('pdv-login|127.0.0.1|operador');
        Schema::dropIfExists($this->usuariosTable);

        parent::tearDown();
    }

    public function test_login_emite_token_opaco_sem_expor_key_app_ou_senha(): void
    {
        $request = Request::create('/api/pdv/login', 'POST', [
            'login' => 'operador',
            'senha' => 'senha-correta',
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $response = (new LoginController(new PdvTokenService()))->login($request);
        $data = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('token_expires_at', $data);
        $this->assertArrayHasKey('token_expires_in', $data);
        $this->assertStringNotContainsString('operador', $data['token']);
        $this->assertStringNotContainsString((string) env('KEY_APP'), $data['token']);
        $this->assertArrayNotHasKey('senha', $data);

        $usuario = (new PdvTokenService())->authenticate($data['token']);
        $this->assertSame(7, (int) $usuario->id);
        $this->assertSame(10, (int) $usuario->empresa_id);
    }

    public function test_login_invalido_nao_echoa_senha_recebida(): void
    {
        $request = Request::create('/api/pdv/login', 'POST', [
            'login' => 'operador',
            'senha' => 'segredo-que-nao-pode-voltar',
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $response = (new LoginController(new PdvTokenService()))->login($request);
        $payload = json_encode($response->getData(true));

        $this->assertSame(401, $response->getStatusCode());
        $this->assertStringNotContainsString('segredo-que-nao-pode-voltar', $payload);
    }

    public function test_token_base64_legado_fabricado_e_rejeitado(): void
    {
        $legacy = base64_encode('7;operador;' . env('KEY_APP'));
        $response = $this->runMiddleware($legacy, ['empresa_id' => 999]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_token_adulterado_e_rejeitado(): void
    {
        $token = $this->issueToken();
        $last = substr($token, -1);
        $tampered = substr($token, 0, -1) . ($last === 'A' ? 'B' : 'A');

        $response = $this->runMiddleware($tampered, ['empresa_id' => 999]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_token_expirado_e_rejeitado(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-23 08:00:00'));
        config(['pdv.token_ttl_minutes' => 60]);
        $token = $this->issueToken();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-23 09:01:00'));
        $response = $this->runMiddleware($token, ['empresa_id' => 999]);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_empresa_id_do_payload_e_substituido_pelo_usuario_autenticado(): void
    {
        $token = $this->issueToken();
        $request = Request::create('/api/pdv/produtos', 'GET', ['empresa_id' => 999]);
        $request->headers->set('token', $token);

        $response = (new AuthPdv(new PdvTokenService()))->handle(
            $request,
            fn ($req) => response()->json([
                'empresa_id' => (int) $req->empresa_id,
                'auth_empresa_id' => (int) $req->attributes->get(AuthPdv::EMPRESA_ID_ATTRIBUTE),
                'auth_user_id' => (int) $req->attributes->get(AuthPdv::USER_ID_ATTRIBUTE),
            ])
        );

        $data = $response->getData(true);
        $this->assertSame(10, $data['empresa_id']);
        $this->assertSame(10, $data['auth_empresa_id']);
        $this->assertSame(7, $data['auth_user_id']);
    }

    public function test_token_perde_validade_se_usuario_mudar_de_empresa(): void
    {
        $token = $this->issueToken();
        DB::table($this->usuariosTable)->where('id', 7)->update(['empresa_id' => 20]);

        $response = $this->runMiddleware($token);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_token_perde_validade_se_usuario_for_desativado(): void
    {
        $token = $this->issueToken();
        DB::table($this->usuariosTable)->where('id', 7)->update(['ativo' => 0]);

        $response = $this->runMiddleware($token);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_operador_nao_pode_consultar_caixa_de_outro_usuario_pela_url(): void
    {
        $token = $this->issueToken();
        $request = Request::create('/api/pdv/caixa/8', 'GET');
        $request->headers->set('token', $token);
        $request->setRouteResolver(fn () => new PdvAuthTestRoute(['usuario_id' => 8]));

        $response = (new AuthPdv(new PdvTokenService()))->handle(
            $request,
            fn () => response()->json(['ok' => true])
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_pdv_nao_usa_key_app_no_endpoint_publico_de_conectividade(): void
    {
        $source = (string) file_get_contents(app_path('Http/Controllers/Pdv/ConfigController.php'));

        $this->assertStringNotContainsString('env("KEY_APP")', $source);
        $this->assertStringNotContainsString("env('KEY_APP')", $source);

        $response = (new ConfigController())->teste(Request::create('/api/pdv/teste', 'POST'));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getData(true));
    }

    public function test_controllers_pdv_usam_contexto_autenticado_nos_pontos_de_identidade(): void
    {
        $caixa = (string) file_get_contents(app_path('Http/Controllers/Pdv/CaixaController.php'));
        $venda = (string) file_get_contents(app_path('Http/Controllers/Pdv/VendaController.php'));

        $this->assertStringContainsString('AuthPdv::USER_ID_ATTRIBUTE', $caixa);
        $this->assertStringContainsString('AuthPdv::EMPRESA_ID_ATTRIBUTE', $caixa);
        $this->assertStringContainsString("where('empresa_id', \$empresaId)", $caixa);

        $this->assertStringContainsString('AuthPdv::USER_ID_ATTRIBUTE', $venda);
        $this->assertStringContainsString("->where('empresa_id', \$empresaId)", $venda);
        $this->assertStringContainsString("->where('id', (int) (\$venda['codigo_edit'] ?? 0))", $venda);
    }

    private function issueToken(): string
    {
        $usuario = Usuario::query()->findOrFail(7);
        return (new PdvTokenService())->issue($usuario)['token'];
    }

    private function runMiddleware(?string $token, array $input = [])
    {
        $request = Request::create('/api/pdv/produtos', 'GET', $input);
        if ($token !== null) {
            $request->headers->set('token', $token);
        }

        return (new AuthPdv(new PdvTokenService()))->handle(
            $request,
            fn () => response()->json(['ok' => true])
        );
    }
}

class PdvAuthTestRoute
{
    public function __construct(private array $parameters = [])
    {
    }

    public function parameter(string $key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }
}
