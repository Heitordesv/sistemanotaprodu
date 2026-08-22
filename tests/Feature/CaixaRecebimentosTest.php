<?php

namespace Tests\Feature;

use App\Models\AberturaCaixa;
use App\Services\CaixaFechamentoService;
use App\Services\CaixaResumoService;
use App\Services\ContaReceberPagamentoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CaixaRecebimentosTest extends TestCase
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

    public function test_recebimento_em_dinheiro_entra_no_resultado_e_na_gaveta_mas_pix_nao_entra_na_gaveta(): void
    {
        $inicio = now()->subHour();

        DB::table('conta_receber_recebimentos')->insert([
            [
                'conta_receber_id' => 2680,
                'empresa_id' => 1,
                'abertura_caixa_id' => 10,
                'usuario_id' => 7,
                'valor' => 45,
                'tipo_pagamento' => '01',
                'received_at' => now()->subMinutes(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'conta_receber_id' => 2681,
                'empresa_id' => 1,
                'abertura_caixa_id' => 11,
                'usuario_id' => 7,
                'valor' => 45,
                'tipo_pagamento' => '17',
                'received_at' => now()->subMinutes(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $caixaDinheiro = $this->aberturaEmMemoria(10, $inicio);
        $caixaPix = $this->aberturaEmMemoria(11, $inicio);

        $service = app(CaixaResumoService::class);
        $resumoDinheiro = $service->resumir($caixaDinheiro, now());
        $resumoPix = $service->resumir($caixaPix, now());

        $this->assertSame(45.0, $resumoDinheiro['totalRecebimentos']);
        $this->assertSame(45.0, $resumoDinheiro['resultadoFinanceiro']);
        $this->assertSame(45.0, $resumoDinheiro['dinheiroNaGaveta']);

        $this->assertSame(45.0, $resumoPix['totalRecebimentos']);
        $this->assertSame(45.0, $resumoPix['resultadoFinanceiro']);
        $this->assertSame(0.0, $resumoPix['dinheiroNaGaveta']);
    }

    public function test_pagamentos_parciais_em_caixas_diferentes_ficam_isolados_por_abertura(): void
    {
        $inicio = now()->subHour();

        DB::table('conta_receber_recebimentos')->insert([
            [
                'conta_receber_id' => 2680,
                'empresa_id' => 1,
                'abertura_caixa_id' => 20,
                'usuario_id' => 7,
                'valor' => 20,
                'tipo_pagamento' => '01',
                'received_at' => now()->subMinutes(20),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'conta_receber_id' => 2680,
                'empresa_id' => 1,
                'abertura_caixa_id' => 21,
                'usuario_id' => 7,
                'valor' => 25,
                'tipo_pagamento' => '17',
                'received_at' => now()->subMinutes(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $service = app(CaixaResumoService::class);
        $primeiro = $service->resumir($this->aberturaEmMemoria(20, $inicio), now());
        $segundo = $service->resumir($this->aberturaEmMemoria(21, $inicio), now());

        $this->assertSame(20.0, $primeiro['totalRecebimentos']);
        $this->assertSame(20.0, $primeiro['dinheiroNaGaveta']);
        $this->assertCount(1, $primeiro['recebimentos']);

        $this->assertSame(25.0, $segundo['totalRecebimentos']);
        $this->assertSame(0.0, $segundo['dinheiroNaGaveta']);
        $this->assertCount(1, $segundo['recebimentos']);
    }

    public function test_caixa_com_apenas_recebimento_pode_ser_fechado_e_persiste_dinheiro_da_gaveta(): void
    {
        $inicio = now()->subHour();

        DB::table('abertura_caixas')->insert([
            'id' => 30,
            'usuario_id' => 7,
            'valor' => 0,
            'ultima_venda_nfe' => 0,
            'ultima_venda_nfce' => 0,
            'empresa_id' => 1,
            'primeira_venda_nfe' => 0,
            'primeira_venda_nfce' => 0,
            'status' => 0,
            'valor_dinheiro_caixa' => 0,
            'filial_id' => null,
            'created_at' => $inicio,
            'updated_at' => $inicio,
        ]);

        DB::table('conta_receber_recebimentos')->insert([
            'conta_receber_id' => 2680,
            'empresa_id' => 1,
            'abertura_caixa_id' => 30,
            'usuario_id' => 7,
            'valor' => 45,
            'tipo_pagamento' => '01',
            'received_at' => now()->subMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fechado = app(CaixaFechamentoService::class)->fechar(30, 1, 7);

        $this->assertSame(1, (int) $fechado->status);
        $this->assertSame(45.0, (float) $fechado->valor_dinheiro_caixa);
        $this->assertSame(0, DB::table('venda_caixas')->count());
        $this->assertSame(0, DB::table('vendas')->count());
    }

    public function test_recebimento_em_massa_grava_forma_real_e_historico_no_caixa_atual(): void
    {
        $inicio = now()->subHour();

        DB::table('abertura_caixas')->insert([
            'id' => 40,
            'usuario_id' => 7,
            'valor' => 0,
            'ultima_venda_nfe' => 0,
            'ultima_venda_nfce' => 0,
            'empresa_id' => 1,
            'primeira_venda_nfe' => 0,
            'primeira_venda_nfce' => 0,
            'status' => 0,
            'valor_dinheiro_caixa' => 0,
            'filial_id' => null,
            'created_at' => $inicio,
            'updated_at' => $inicio,
        ]);

        DB::table('conta_recebers')->insert([
            [
                'id' => 1,
                'empresa_id' => 1,
                'valor_integral' => 20,
                'valor_recebido' => 0,
                'status' => 0,
                'tipo_pagamento' => '06',
                'filial_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'empresa_id' => 1,
                'valor_integral' => 25,
                'valor_recebido' => 0,
                'status' => 0,
                'tipo_pagamento' => '06',
                'filial_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $resultado = app(ContaReceberPagamentoService::class)->registrarMassa(
            [1, 2],
            1,
            '01',
            now()->toDateString(),
            (string) Str::uuid()
        );

        $this->assertSame(2, $resultado['quantidade']);
        $this->assertSame(45.0, $resultado['total']);
        $this->assertSame(2, DB::table('conta_receber_pagamentos')->where('forma_pagamento', '01')->count());
        $this->assertSame(2, DB::table('conta_receber_recebimentos')->where('abertura_caixa_id', 40)->where('tipo_pagamento', '01')->count());
        $this->assertSame(45.0, (float) DB::table('conta_receber_recebimentos')->where('abertura_caixa_id', 40)->sum('valor'));
        $this->assertSame(0, DB::table('conta_recebers')->where('tipo_pagamento', 'MASSA')->count());
    }

    private function aberturaEmMemoria(int $id, $inicio): AberturaCaixa
    {
        $abertura = new AberturaCaixa([
            'usuario_id' => 7,
            'valor' => 0,
            'empresa_id' => 1,
            'primeira_venda_nfe' => 0,
            'primeira_venda_nfce' => 0,
            'ultima_venda_nfe' => 0,
            'ultima_venda_nfce' => 0,
            'status' => 0,
        ]);

        $abertura->id = $id;
        $abertura->created_at = $inicio;
        $abertura->updated_at = now();
        $abertura->exists = true;

        return $abertura;
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
