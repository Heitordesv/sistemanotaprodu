<?php

namespace Tests\Feature;

use App\Http\Controllers\SangriaCaixaController;
use App\Models\AberturaCaixa;
use App\Services\CaixaResumoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SangriaDinheiroGavetaTest extends TestCase
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

    public function test_pix_nao_aumenta_limite_de_sangria(): void
    {
        $this->criarAbertura(10, 0);

        DB::table('vendas')->insert([
            'id' => 1,
            'empresa_id' => 1,
            'usuario_id' => 7,
            'tipo_pagamento' => '17',
            'valor_total' => 100,
            'estado_emissao' => 'aprovado',
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);

        $abertura = AberturaCaixa::findOrFail(10);
        $resumo = app(CaixaResumoService::class)->resumir($abertura);
        $this->assertSame(0.0, $resumo['dinheiroNaGaveta']);

        $request = Request::create('/sangriaCaixa', 'POST', ['valor' => '1,00']);
        $request->attributes->set('abertura_caixa_bloqueada', $abertura);

        (new SangriaCaixaController())->store($request);

        $this->assertSame(0, DB::table('sangria_caixas')->count());
        $this->assertStringContainsString('dinheiro disponível', (string) session('flash_erro'));
    }

    public function test_recebimento_de_conta_em_dinheiro_aumenta_limite_de_sangria(): void
    {
        // A venda PIX do teste anterior não existe neste banco porque cada teste
        // recebe um SQLite em memória novo. primeira_venda_nfe=0 é suficiente.
        $this->criarAbertura(20, 0);

        DB::table('conta_receber_recebimentos')->insert([
            'conta_receber_id' => 2680,
            'empresa_id' => 1,
            'abertura_caixa_id' => 20,
            'usuario_id' => 7,
            'valor' => 45,
            'tipo_pagamento' => '01',
            'received_at' => now()->subMinute(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $abertura = AberturaCaixa::findOrFail(20);
        $resumoAntes = app(CaixaResumoService::class)->resumir($abertura);
        $this->assertSame(45.0, $resumoAntes['dinheiroNaGaveta']);

        $request = Request::create('/sangriaCaixa', 'POST', ['valor' => '40,00']);
        $request->attributes->set('abertura_caixa_bloqueada', $abertura);

        (new SangriaCaixaController())->store($request);

        $this->assertSame(1, DB::table('sangria_caixas')->count());
        $this->assertSame(40.0, (float) DB::table('sangria_caixas')->value('valor'));
        $this->assertSame(20, (int) DB::table('sangria_caixas')->value('abertura_caixa_id'));

        $resumoDepois = app(CaixaResumoService::class)->resumir($abertura->fresh());
        $this->assertSame(5.0, $resumoDepois['dinheiroNaGaveta']);
    }

    private function criarAbertura(int $id, int $primeiraVendaNfe): void
    {
        DB::table('abertura_caixas')->insert([
            'id' => $id,
            'usuario_id' => 7,
            'valor' => 0,
            'ultima_venda_nfe' => $primeiraVendaNfe,
            'ultima_venda_nfce' => 0,
            'empresa_id' => 1,
            'primeira_venda_nfe' => $primeiraVendaNfe,
            'primeira_venda_nfce' => 0,
            'status' => 0,
            'valor_dinheiro_caixa' => 0,
            'filial_id' => null,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
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

        Schema::create('vendas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->string('tipo_pagamento', 10)->nullable();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->string('estado_emissao')->nullable();
            $table->timestamps();
        });

        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->string('tipo_pagamento', 10)->nullable();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->boolean('rascunho')->default(false);
            $table->boolean('consignado')->default(false);
            $table->string('estado_emissao')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('venda_id')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->decimal('valor_integral', 15, 2)->default(0);
            $table->decimal('valor_recebido', 15, 2)->default(0);
            $table->string('tipo_pagamento', 10)->nullable();
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->unsignedBigInteger('received_by_user_id')->nullable();
            $table->timestamp('received_at')->nullable();
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

        foreach (['suprimento_caixas', 'sangria_caixas'] as $tabela) {
            Schema::create($tabela, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('empresa_id');
                $table->unsignedBigInteger('usuario_id');
                $table->unsignedBigInteger('abertura_caixa_id')->nullable();
                $table->decimal('valor', 15, 2)->default(0);
                $table->string('observacao')->nullable();
                $table->timestamps();
            });
        }
    }
}
