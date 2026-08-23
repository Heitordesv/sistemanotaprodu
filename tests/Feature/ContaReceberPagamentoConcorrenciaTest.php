<?php

namespace Tests\Feature;

use App\Models\ContaReceber;
use App\Services\CaixaFechamentoService;
use App\Services\CaixaResumoService;
use App\Services\ContaReceberPagamentoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ContaReceberPagamentoConcorrenciaTest extends TestCase
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

        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'super' => 0,
        ]]);
    }

    public function test_pagamento_misto_grava_historico_por_forma_e_somente_dinheiro_entra_na_gaveta(): void
    {
        $this->criarAbertura(50, 0);
        $this->criarConta(100, 45.00);

        $conta = ContaReceber::findOrFail(100);

        app(ContaReceberPagamentoService::class)->registrarMultiplos(
            $conta,
            [
                ['forma_pagamento' => '01', 'valor' => 20.00],
                ['forma_pagamento' => '17', 'valor' => 25.00],
            ],
            now()->toDateString(),
            '11111111-1111-4111-8111-111111111111'
        );

        $historico = DB::table('conta_receber_recebimentos')
            ->where('conta_receber_id', 100)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $historico);
        $this->assertSame('01', $historico[0]->tipo_pagamento);
        $this->assertSame(20.0, (float) $historico[0]->valor);
        $this->assertSame('17', $historico[1]->tipo_pagamento);
        $this->assertSame(25.0, (float) $historico[1]->valor);
        $this->assertSame(0, DB::table('conta_receber_recebimentos')->where('tipo_pagamento', '99')->count());

        $contaAtualizada = ContaReceber::findOrFail(100);
        $this->assertSame('99', $contaAtualizada->tipo_pagamento);
        $this->assertSame(45.0, (float) $contaAtualizada->valor_recebido);
        $this->assertSame(50, (int) $contaAtualizada->abertura_caixa_id);

        $resumo = app(CaixaResumoService::class)->resumir(
            \App\Models\AberturaCaixa::findOrFail(50),
            now()
        );

        $this->assertSame(45.0, $resumo['totalRecebimentos']);
        $this->assertSame(45.0, $resumo['resultadoFinanceiro']);
        $this->assertSame(20.0, $resumo['dinheiroNaGaveta']);
    }

    public function test_fechamento_vencedor_impede_recebimento_de_entrar_no_caixa_consolidado(): void
    {
        $this->criarAbertura(60, 0);
        $this->criarConta(101, 45.00);

        app(CaixaFechamentoService::class)->fechar(60, 1, 7);

        $this->assertSame(1, (int) DB::table('abertura_caixas')->where('id', 60)->value('status'));

        try {
            app(ContaReceberPagamentoService::class)->registrarMultiplos(
                ContaReceber::findOrFail(101),
                [['forma_pagamento' => '01', 'valor' => 45.00]],
                now()->toDateString(),
                '22222222-2222-4222-8222-222222222222'
            );

            $this->fail('O recebimento não poderia ser associado a um caixa já fechado.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Nenhum caixa aberto', $e->getMessage());
        }

        $this->assertSame(0.0, (float) DB::table('conta_recebers')->where('id', 101)->value('valor_recebido'));
        $this->assertSame(0, DB::table('conta_receber_pagamentos')->where('conta_receber_id', 101)->count());
        $this->assertSame(0, DB::table('conta_receber_recebimentos')->where('conta_receber_id', 101)->count());
    }

    public function test_recebimento_vencedor_e_incluido_no_fechamento_subsequente(): void
    {
        $this->criarAbertura(70, 0);
        $this->criarConta(102, 45.00);

        app(ContaReceberPagamentoService::class)->registrarMultiplos(
            ContaReceber::findOrFail(102),
            [['forma_pagamento' => '01', 'valor' => 45.00]],
            now()->toDateString(),
            '33333333-3333-4333-8333-333333333333'
        );

        $fechado = app(CaixaFechamentoService::class)->fechar(70, 1, 7);

        $this->assertSame(1, (int) $fechado->status);
        $this->assertSame(45.0, (float) $fechado->valor_dinheiro_caixa);
        $this->assertSame(45.0, (float) DB::table('conta_receber_recebimentos')->where('abertura_caixa_id', 70)->sum('valor'));
    }

    private function criarAbertura(int $id, int $status): void
    {
        DB::table('abertura_caixas')->insert([
            'id' => $id,
            'usuario_id' => 7,
            'valor' => 0,
            'ultima_venda_nfe' => 0,
            'ultima_venda_nfce' => 0,
            'empresa_id' => 1,
            'primeira_venda_nfe' => 0,
            'primeira_venda_nfce' => 0,
            'status' => $status,
            'valor_dinheiro_caixa' => 0,
            'filial_id' => null,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);
    }

    private function criarConta(int $id, float $valor): void
    {
        DB::table('conta_recebers')->insert([
            'id' => $id,
            'empresa_id' => 1,
            'valor_integral' => $valor,
            'valor_recebido' => 0,
            'status' => 0,
            'tipo_pagamento' => '06',
            'data_recebimento' => null,
            'filial_id' => null,
            'abertura_caixa_id' => null,
            'received_by_user_id' => null,
            'received_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function criarSchemaMinimo(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
        });

        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('valor', 15, 2)->default(0);
            $table->unsignedBigInteger('ultima_venda_nfe')->default(0);
            $table->unsignedBigInteger('ultima_venda_nfce')->default(0);
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('primeira_venda_nfe')->default(0);
            $table->unsignedBigInteger('primeira_venda_nfce')->default(0);
            $table->boolean('status')->default(false);
            $table->decimal('valor_dinheiro_caixa', 15, 2)->default(0);
            $table->unsignedBigInteger('filial_id')->nullable();
            $table->timestamps();
        });

        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
        });

        Schema::create('vendas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
        });

        Schema::create('suprimento_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('valor', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sangria_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('valor', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->decimal('valor_integral', 15, 2);
            $table->decimal('valor_recebido', 15, 2)->default(0);
            $table->boolean('status')->default(false);
            $table->string('tipo_pagamento', 10)->nullable();
            $table->date('data_recebimento')->nullable();
            $table->unsignedBigInteger('filial_id')->nullable();
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->unsignedBigInteger('received_by_user_id')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_receber_pagamentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('conta_receber_id');
            $table->unsignedBigInteger('empresa_id');
            $table->decimal('valor', 15, 2);
            $table->string('forma_pagamento', 10);
            $table->dateTime('data_pagamento');
            $table->string('origem')->nullable();
            $table->string('provedor')->nullable();
            $table->string('external_id')->nullable();
            $table->uuid('lote_uuid')->nullable();
            $table->string('status')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_receber_recebimentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('conta_receber_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->decimal('valor', 15, 7);
            $table->string('tipo_pagamento', 10)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }
}
