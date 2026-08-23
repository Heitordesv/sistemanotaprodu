<?php

namespace Tests\Feature;

use App\Models\ContaReceber;
use App\Services\ContaReceberPagamentoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ContaReceberPagamentoIdempotenciaTest extends TestCase
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

        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'super' => 0,
        ]]);
    }

    public function test_retry_do_mesmo_lote_nao_duplica_pagamento_nem_historico(): void
    {
        $this->criarAbertura(100);
        $this->criarConta(200, 45.00);
        $lote = '77777777-7777-4777-8777-777777777777';

        $service = app(ContaReceberPagamentoService::class);

        $primeiro = $service->registrarMultiplos(
            ContaReceber::findOrFail(200),
            [['forma_pagamento' => '01', 'valor' => 20.00]],
            now()->toDateString(),
            $lote
        );

        $segundo = $service->registrarMultiplos(
            ContaReceber::findOrFail(200),
            [['forma_pagamento' => '01', 'valor' => 20.00]],
            now()->toDateString(),
            $lote
        );

        $this->assertFalse((bool) $primeiro->getAttribute('recebimento_idempotente'));
        $this->assertTrue((bool) $segundo->getAttribute('recebimento_idempotente'));
        $this->assertSame(20.0, (float) ContaReceber::findOrFail(200)->valor_recebido);
        $this->assertSame(1, DB::table('conta_receber_pagamentos')->where('lote_uuid', $lote)->count());
        $this->assertSame(1, DB::table('conta_receber_recebimentos')->where('conta_receber_id', 200)->count());
    }

    public function test_servico_rejeita_crediario_como_forma_de_recebimento(): void
    {
        $this->assertFormaProibida('06');
    }

    public function test_servico_rejeita_sem_pagamento_como_forma_de_recebimento(): void
    {
        $this->assertFormaProibida('90');
    }

    private function assertFormaProibida(string $forma): void
    {
        $this->criarAbertura(101);
        $this->criarConta(201, 45.00);

        try {
            app(ContaReceberPagamentoService::class)->registrarMultiplos(
                ContaReceber::findOrFail(201),
                [['forma_pagamento' => $forma, 'valor' => 45.00]],
                now()->toDateString(),
                '88888888-8888-4888-8888-' . ($forma === '06' ? '000000000006' : '000000000090')
            );

            $this->fail('A forma ' . $forma . ' deveria ser rejeitada como recebimento.');
        } catch (RuntimeException $e) {
            $this->assertSame('Forma de pagamento inválida para recebimento.', $e->getMessage());
        }

        $this->assertSame(0.0, (float) ContaReceber::findOrFail(201)->valor_recebido);
        $this->assertSame(0, DB::table('conta_receber_pagamentos')->where('conta_receber_id', 201)->count());
        $this->assertSame(0, DB::table('conta_receber_recebimentos')->where('conta_receber_id', 201)->count());
    }

    private function criarAbertura(int $id): void
    {
        DB::table('abertura_caixas')->insert([
            'id' => $id,
            'usuario_id' => 7,
            'empresa_id' => 1,
            'filial_id' => null,
            'status' => 0,
            'valor' => 0,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);
    }

    private function criarConta(int $id, float $valor): void
    {
        DB::table('conta_recebers')->insert([
            'id' => $id,
            'empresa_id' => 1,
            'filial_id' => null,
            'valor_integral' => $valor,
            'valor_recebido' => 0,
            'status' => 0,
            'tipo_pagamento' => '06',
            'data_recebimento' => null,
            'abertura_caixa_id' => null,
            'received_by_user_id' => null,
            'received_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function criarSchemaMinimo(): void
    {
        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('filial_id')->nullable();
            $table->boolean('status')->default(false);
            $table->decimal('valor', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('filial_id')->nullable();
            $table->decimal('valor_integral', 15, 2);
            $table->decimal('valor_recebido', 15, 2)->default(0);
            $table->boolean('status')->default(false);
            $table->string('tipo_pagamento', 10)->nullable();
            $table->date('data_recebimento')->nullable();
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->unsignedBigInteger('received_by_user_id')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_receber_pagamentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('conta_receber_id')->index();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->decimal('valor', 15, 2);
            $table->string('forma_pagamento', 10);
            $table->dateTime('data_pagamento');
            $table->string('origem', 30)->nullable();
            $table->string('provedor', 60)->nullable();
            $table->string('external_id', 191)->nullable();
            $table->uuid('lote_uuid')->nullable()->index();
            $table->string('status', 30)->default('confirmado');
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_receber_recebimentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('conta_receber_id')->index();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('abertura_caixa_id')->nullable()->index();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->decimal('valor', 15, 7);
            $table->string('tipo_pagamento', 10)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }
}
