<?php

namespace Tests\Feature;

use App\Http\Controllers\FrontBoxResumoController;
use App\Http\Controllers\VendaSeguraController;
use App\Http\Middleware\LockCaixaAbertoParaMovimentacao;
use App\Services\CaixaMovimentacaoService;
use App\Services\VendaTenantGuardService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VendaTenantIsolationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        $this->criarSchema();

        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'super' => 0,
        ]]);

        DB::table('natureza_operacaos')->insert([
            ['id' => 10, 'empresa_id' => 1],
            ['id' => 20, 'empresa_id' => 2],
        ]);

        DB::table('produtos')->insert([
            ['id' => 100, 'empresa_id' => 1],
            ['id' => 200, 'empresa_id' => 2],
        ]);

        DB::table('clientes')->insert([
            ['id' => 1000, 'empresa_id' => 1],
            ['id' => 2000, 'empresa_id' => 2],
        ]);

        DB::table('filials')->insert([
            ['id' => 11, 'empresa_id' => 1],
            ['id' => 22, 'empresa_id' => 2],
        ]);
    }

    public function test_update_de_venda_de_outro_tenant_nao_e_localizado(): void
    {
        DB::table('vendas')->insert([
            'id' => 50,
            'empresa_id' => 2,
            'abertura_caixa_id' => 900,
        ]);

        $request = $this->requestValido();

        $this->expectException(ModelNotFoundException::class);
        app(VendaTenantGuardService::class)->validar($request, 50);
    }

    public function test_natureza_de_outro_tenant_e_rejeitada(): void
    {
        $request = $this->requestValido([
            'natureza_id' => 20,
        ]);

        try {
            app(VendaTenantGuardService::class)->validar($request);
            $this->fail('Era esperada ValidationException para natureza de outro tenant.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('natureza_id', $e->errors());
        }
    }

    public function test_produto_de_outro_tenant_e_rejeitado_antes_de_movimentar_estoque(): void
    {
        $request = $this->requestValido([
            'produto_id' => [100, 200],
        ]);

        try {
            app(VendaTenantGuardService::class)->validar($request);
            $this->fail('Era esperada ValidationException para produto de outro tenant.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('produto_id', $e->errors());
        }
    }

    public function test_referencias_da_mesma_empresa_sao_aceitas(): void
    {
        $request = $this->requestValido();

        $this->assertNull(app(VendaTenantGuardService::class)->validar($request));
    }

    public function test_post_sem_caixa_aberto_descarta_abertura_forjada(): void
    {
        $request = Request::create('/vendas', 'POST', [
            'type' => 'venda',
            'abertura_caixa_id' => 999,
        ]);

        $middleware = new LockCaixaAbertoParaMovimentacao(app(CaixaMovimentacaoService::class));

        $aberturaRecebida = $middleware->handle(
            $request,
            fn (Request $req) => $req->input('abertura_caixa_id'),
            'venda-opcional'
        );

        $this->assertNull($aberturaRecebida);
    }

    public function test_post_com_caixa_aberto_substitui_abertura_forjada_pela_abertura_real(): void
    {
        DB::table('abertura_caixas')->insert([
            'id' => 77,
            'empresa_id' => 1,
            'usuario_id' => 7,
            'status' => 0,
            'valor' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/vendas', 'POST', [
            'type' => 'venda',
            'abertura_caixa_id' => 999,
        ]);

        $middleware = new LockCaixaAbertoParaMovimentacao(app(CaixaMovimentacaoService::class));

        $aberturaRecebida = $middleware->handle(
            $request,
            fn (Request $req) => (int) $req->input('abertura_caixa_id'),
            'venda-opcional'
        );

        $this->assertSame(77, $aberturaRecebida);
    }

    public function test_rotas_criticas_apontam_para_controllers_endurecidos(): void
    {
        $updateRoute = app('router')->getRoutes()->match(Request::create('/vendas/50', 'PUT'));
        $pdvRoute = app('router')->getRoutes()->match(Request::create('/frenteCaixa', 'GET'));

        $this->assertStringContainsString(VendaSeguraController::class, $updateRoute->getActionName());
        $this->assertStringContainsString(FrontBoxResumoController::class, $pdvRoute->getActionName());
    }

    private function requestValido(array $override = []): Request
    {
        return Request::create('/vendas', 'POST', array_merge([
            'empresa_id' => 1,
            'natureza_id' => 10,
            'produto_id' => [100],
            'cliente_id' => 1000,
            'transportadora_id' => null,
            'filial_id' => 11,
        ], $override));
    }

    private function criarSchema(): void
    {
        Schema::create('natureza_operacaos', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('produtos', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('transportadoras', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('filials', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('vendas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
        });

        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedTinyInteger('status')->default(0);
            $table->decimal('valor', 12, 2)->default(0);
            $table->timestamps();
        });
    }
}
