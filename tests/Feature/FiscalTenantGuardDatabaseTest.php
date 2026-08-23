<?php

namespace Tests\Feature;

use App\Http\Middleware\HashEmpresa;
use App\Models\ConfigNota;
use App\Models\Empresa;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Services\FiscalTenantGuardService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

class FiscalTenantGuardDatabaseTest extends TestCase
{
    private array $tables = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->tables = [
            'empresas' => (new Empresa())->getTable(),
            'vendas' => (new Venda())->getTable(),
            'venda_caixas' => (new VendaCaixa())->getTable(),
            'naturezas' => (new NaturezaOperacao())->getTable(),
            'produtos' => (new Produto())->getTable(),
            'config_notas' => (new ConfigNota())->getTable(),
        ];

        foreach (array_reverse(array_values($this->tables)) as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create($this->tables['empresas'], function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('hash')->nullable();
        });

        foreach (['vendas', 'venda_caixas', 'naturezas', 'produtos'] as $key) {
            Schema::create($this->tables[$key], function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->unsignedBigInteger('empresa_id');
            });
        }

        Schema::create($this->tables['config_notas'], function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('nat_op_padrao')->nullable();
        });

        DB::table($this->tables['empresas'])->insert([
            ['id' => 10, 'hash' => 'hash-empresa-a'],
            ['id' => 20, 'hash' => 'hash-empresa-b'],
        ]);

        foreach (['vendas', 'venda_caixas', 'produtos'] as $key) {
            DB::table($this->tables[$key])->insert([
                ['id' => 1, 'empresa_id' => 10],
                ['id' => 2, 'empresa_id' => 20],
            ]);
        }

        DB::table($this->tables['naturezas'])->insert([
            ['id' => 11, 'empresa_id' => 10],
            ['id' => 22, 'empresa_id' => 20],
        ]);

        DB::table($this->tables['config_notas'])->insert([
            ['id' => 100, 'empresa_id' => 10, 'nat_op_padrao' => 11],
            ['id' => 200, 'empresa_id' => 20, 'nat_op_padrao' => 22],
        ]);
    }

    protected function tearDown(): void
    {
        foreach (array_reverse(array_values($this->tables)) as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_empresa_a_nao_resolve_recursos_fiscais_da_empresa_b(): void
    {
        $guard = new FiscalTenantGuardService();

        $this->assertSame(1, (int) $guard->venda(10, 1)->id);
        $this->assertModelNotFound(fn () => $guard->venda(10, 2));

        $this->assertSame(1, (int) $guard->vendaCaixa(10, 1)->id);
        $this->assertModelNotFound(fn () => $guard->vendaCaixa(10, 2));

        $this->assertSame(11, (int) $guard->natureza(10, 11)->id);
        $this->assertModelNotFound(fn () => $guard->natureza(10, 22));

        $this->assertSame(1, (int) $guard->produto(10, 1)->id);
        $this->assertModelNotFound(fn () => $guard->produto(10, 2));

        $this->assertSame(100, (int) $guard->configNota(10, 100)->id);
        $this->assertModelNotFound(fn () => $guard->configNota(10, 200));
    }

    public function test_lote_de_produtos_falha_se_um_item_for_de_outro_tenant(): void
    {
        $guard = new FiscalTenantGuardService();

        $guard->produtos(10, [1]);
        $this->addToAssertionCount(1);

        $this->assertModelNotFound(fn () => $guard->produtos(10, [1, 2]));
    }

    public function test_natureza_padrao_da_config_precisa_pertencer_ao_mesmo_tenant(): void
    {
        $guard = new FiscalTenantGuardService();

        $this->assertSame(11, (int) $guard->naturezaPadraoDaConfig(10)->id);

        DB::table($this->tables['config_notas'])
            ->where('empresa_id', 10)
            ->update(['nat_op_padrao' => 22]);

        $this->assertModelNotFound(fn () => $guard->naturezaPadraoDaConfig(10));
    }

    public function test_hash_invalido_falha_fechado_e_nao_executa_proximo_middleware(): void
    {
        $request = Request::create('/api/nfe/transmitir', 'POST', [
            'empresa_id' => 999,
            'hash' => 'hash-inexistente',
        ]);
        $nextExecuted = false;

        $response = (new HashEmpresa())->handle(
            $request,
            function () use (&$nextExecuted) {
                $nextExecuted = true;
                return response()->json(['ok' => true]);
            }
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertFalse($nextExecuted);
    }

    public function test_hash_valido_substitui_empresa_id_do_cliente_e_marca_tenant_verificado(): void
    {
        $request = Request::create('/api/nfe/transmitir', 'POST', [
            'empresa_id' => 999,
            'hash' => 'hash-empresa-a',
        ]);

        $response = (new HashEmpresa())->handle(
            $request,
            fn ($req) => response()->json(['empresa_id' => (int) $req->empresa_id])
        );

        $this->assertSame(10, (int) $request->empresa_id);
        $this->assertSame(10, (int) $request->attributes->get(FiscalTenantGuardService::VERIFIED_TENANT_ATTRIBUTE));
        $this->assertSame(10, (int) $response->getData(true)['empresa_id']);
    }

    public function test_appfiscal_sem_token_falha_fechado(): void
    {
        $this->expectException(AccessDeniedHttpException::class);

        (new FiscalTenantGuardService())->empresaIdPorTokenApp(
            Request::create('/api/appFiscal/vendas', 'GET', ['empresa_id' => 999])
        );
    }

    public function test_venda_appfiscal_busca_ambiente_e_filtros_pelo_empresa_id_resolvido(): void
    {
        $source = (string) file_get_contents(app_path('Http/Controllers/AppFiscal/VendaController.php'));

        $this->assertStringContainsString("ConfigNota::where('empresa_id', \$request->empresa_id)", $source);
        $this->assertStringContainsString('Venda::filtroDataApp(', $source);
        $this->assertStringContainsString('Venda::filtroDataClienteApp(', $source);
        $this->assertStringContainsString('Venda::filtroClienteApp(', $source);
        $this->assertStringContainsString('Venda::filtroEstadoApp(', $source);
    }

    private function assertModelNotFound(callable $callback): void
    {
        try {
            $callback();
            $this->fail('Era esperado ModelNotFoundException para recurso de outro tenant.');
        } catch (ModelNotFoundException $e) {
            $this->addToAssertionCount(1);
        }
    }
}
