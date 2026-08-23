<?php

namespace Tests\Feature;

use App\Http\Controllers\AberturaCaixaController;
use App\Models\AberturaCaixa;
use App\Models\Venda;
use App\Services\CaixaResumoService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use ReflectionProperty;
use Tests\TestCase;

class CaixaVinculoAberturaTest extends TestCase
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

        $this->criarSchemaMinimo();

        DB::table('usuarios')->insert([
            'id' => 7,
            'empresa_id' => 1,
            'nome' => 'Operador Teste',
        ]);

        DB::table('filials')->insert([
            ['id' => 10, 'empresa_id' => 1],
            ['id' => 20, 'empresa_id' => 2],
        ]);

        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'super' => 0,
        ]]);
    }

    public function test_resumo_nao_mistura_movimentacoes_e_vendas_vinculadas_a_outro_caixa(): void
    {
        $inicio = now()->subMinutes(30);

        foreach ([100, 101] as $id) {
            DB::table('abertura_caixas')->insert([
                'id' => $id,
                'usuario_id' => 7,
                'valor' => 0,
                'ultima_venda_nfe' => 0,
                'ultima_venda_nfce' => 0,
                'empresa_id' => 1,
                'primeira_venda_nfe' => 0,
                'primeira_venda_nfce' => 0,
                'status' => 0,
                'valor_dinheiro_caixa' => 0,
                'filial_id' => 10,
                'created_at' => $inicio,
                'updated_at' => $inicio,
            ]);
        }

        $this->inserirVenda(1, 100, 10);
        $this->inserirVenda(2, 101, 99);
        $this->inserirVenda(3, null, 3);

        $this->inserirMovimentacao('suprimento_caixas', 1, 100, 5);
        $this->inserirMovimentacao('suprimento_caixas', 2, 101, 50);
        $this->inserirMovimentacao('suprimento_caixas', 3, null, 1);

        $this->inserirMovimentacao('sangria_caixas', 1, 100, 2);
        $this->inserirMovimentacao('sangria_caixas', 2, 101, 20);
        $this->inserirMovimentacao('sangria_caixas', 3, null, 0.5);

        $abertura = AberturaCaixa::findOrFail(100);
        $resumo = app(CaixaResumoService::class)->resumir($abertura, now());

        $this->assertSame(13.0, $resumo['totalVendasDinheiro']);
        $this->assertSame(6.0, $resumo['totalSuprimentos']);
        $this->assertSame(2.5, $resumo['totalSangrias']);
        $this->assertSame(16.5, $resumo['resultadoFinanceiro']);
        $this->assertSame(16.5, $resumo['dinheiroNaGaveta']);

        $this->assertSame([1, 3], collect($resumo['vendas'])->pluck('id')->sort()->values()->all());
        $this->assertSame([1, 3], $resumo['suprimentos']->pluck('id')->sort()->values()->all());
        $this->assertSame([1, 3], $resumo['sangrias']->pluck('id')->sort()->values()->all());
    }

    public function test_venda_persiste_abertura_caixa_id_por_mass_assignment_e_expoe_relacao(): void
    {
        $venda = new Venda([
            'empresa_id' => 1,
            'usuario_id' => 7,
            'valor_total' => 10,
            'abertura_caixa_id' => 123,
        ]);

        $this->assertSame(123, (int) $venda->abertura_caixa_id);
        $this->assertInstanceOf(BelongsTo::class, $venda->aberturaCaixa());
    }

    public function test_abertura_rejeita_filial_de_outra_empresa(): void
    {
        $controller = $this->controllerParaEmpresa(1);
        $request = Request::create('/caixa', 'POST', [
            'valor' => '10,00',
            'filial_id' => 20,
        ]);

        try {
            $controller->store($request);
            $this->fail('Era esperada uma ValidationException para filial de outra empresa.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('filial_id', $e->errors());
        }

        $this->assertSame(0, DB::table('abertura_caixas')->count());
    }

    public function test_abertura_rejeita_valor_negativo(): void
    {
        $controller = $this->controllerParaEmpresa(1);
        $request = Request::create('/caixa', 'POST', [
            'valor' => '-1,00',
            'filial_id' => 10,
        ]);

        try {
            $controller->store($request);
            $this->fail('Era esperada uma ValidationException para valor negativo.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('valor', $e->errors());
        }

        $this->assertSame(0, DB::table('abertura_caixas')->count());
    }

    public function test_abertura_aceita_filial_da_empresa_e_valor_monetario_brasileiro(): void
    {
        $controller = $this->controllerParaEmpresa(1);
        $request = Request::create('/caixa', 'POST', [
            'valor' => '10,50',
            'filial_id' => 10,
        ]);

        $controller->store($request);

        $this->assertDatabaseHas('abertura_caixas', [
            'empresa_id' => 1,
            'usuario_id' => 7,
            'filial_id' => 10,
            'status' => 0,
        ]);

        $abertura = DB::table('abertura_caixas')->first();
        $this->assertSame(10.5, (float) $abertura->valor);
    }

    private function inserirVenda(int $id, ?int $aberturaId, float $valor): void
    {
        DB::table('vendas')->insert([
            'id' => $id,
            'empresa_id' => 1,
            'usuario_id' => 7,
            'abertura_caixa_id' => $aberturaId,
            'tipo_pagamento' => '01',
            'valor_total' => $valor,
            'estado_emissao' => 'aprovado',
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);
    }

    private function inserirMovimentacao(string $tabela, int $id, ?int $aberturaId, float $valor): void
    {
        DB::table($tabela)->insert([
            'id' => $id,
            'usuario_id' => 7,
            'empresa_id' => 1,
            'abertura_caixa_id' => $aberturaId,
            'valor' => $valor,
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);
    }

    private function controllerParaEmpresa(int $empresaId): AberturaCaixaController
    {
        $controller = app(AberturaCaixaController::class);
        $property = new ReflectionProperty($controller, 'empresa_id');
        $property->setAccessible(true);
        $property->setValue($controller, $empresaId);

        return $controller;
    }

    private function criarSchemaMinimo(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome')->nullable();
        });

        Schema::create('filials', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('valor', 12, 2)->default(0);
            $table->unsignedBigInteger('ultima_venda_nfe')->default(0);
            $table->unsignedBigInteger('ultima_venda_nfce')->default(0);
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('primeira_venda_nfe')->default(0);
            $table->unsignedBigInteger('primeira_venda_nfce')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
            $table->decimal('valor_dinheiro_caixa', 12, 2)->default(0);
            $table->unsignedBigInteger('filial_id')->nullable();
            $table->timestamps();
        });

        Schema::create('vendas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->string('tipo_pagamento')->nullable();
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->string('estado_emissao')->nullable();
            $table->timestamps();
        });

        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('venda_id')->nullable();
        });

        foreach (['suprimento_caixas', 'sangria_caixas'] as $tabela) {
            Schema::create($tabela, function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
                $table->unsignedBigInteger('usuario_id');
                $table->unsignedBigInteger('empresa_id');
                $table->unsignedBigInteger('abertura_caixa_id')->nullable();
                $table->decimal('valor', 12, 2);
                $table->string('observacao')->nullable();
                $table->timestamps();
            });
        }
    }
}
