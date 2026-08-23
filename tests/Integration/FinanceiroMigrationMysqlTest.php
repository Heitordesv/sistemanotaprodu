<?php

namespace Tests\Integration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinanceiroMigrationMysqlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ((string) env('RUN_MYSQL_CONCURRENCY_TESTS') !== '1') {
            $this->markTestSkipped('Integração MySQL financeira desabilitada.');
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('Este teste exige MySQL.');
        }

        $database = (string) DB::connection()->getDatabaseName();
        if ($database === '' || stripos($database, 'test') === false) {
            $this->fail('Teste de migration recusado: DB_DATABASE precisa conter "test".');
        }

        $this->limparTabelas();
    }

    protected function tearDown(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $database = (string) DB::connection()->getDatabaseName();
            if ($database !== '' && stripos($database, 'test') !== false) {
                $this->limparTabelas();
            }
        }

        parent::tearDown();
    }

    public function test_migrations_completam_tabelas_legadas_parciais_e_down_nao_apaga_auditoria(): void
    {
        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
        });

        // Simula objetos existentes antes desta migration, com schema incompleto.
        Schema::create('conta_receber_recebimentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('legado')->nullable();
        });

        Schema::create('conta_receber_pagamentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('legado')->nullable();
        });

        $tracking = require database_path('migrations/2026_08_22_230000_add_cash_receipt_tracking_to_conta_recebers.php');
        $pagamentos = require database_path('migrations/2026_08_22_231000_create_conta_receber_pagamentos_if_missing.php');

        $tracking->up();
        $pagamentos->up();

        foreach (['abertura_caixa_id', 'received_by_user_id', 'received_at'] as $coluna) {
            $this->assertTrue(Schema::hasColumn('conta_recebers', $coluna), 'Coluna ausente em conta_recebers: ' . $coluna);
        }

        foreach ([
            'conta_receber_id', 'empresa_id', 'abertura_caixa_id', 'usuario_id',
            'valor', 'tipo_pagamento', 'received_at', 'created_at', 'updated_at',
        ] as $coluna) {
            $this->assertTrue(
                Schema::hasColumn('conta_receber_recebimentos', $coluna),
                'Coluna ausente em conta_receber_recebimentos: ' . $coluna
            );
        }

        foreach ([
            'conta_receber_id', 'empresa_id', 'valor', 'forma_pagamento',
            'data_pagamento', 'origem', 'provedor', 'external_id', 'lote_uuid',
            'status', 'observacao', 'created_at', 'updated_at',
        ] as $coluna) {
            $this->assertTrue(
                Schema::hasColumn('conta_receber_pagamentos', $coluna),
                'Coluna ausente em conta_receber_pagamentos: ' . $coluna
            );
        }

        DB::table('conta_receber_recebimentos')->insert([
            'legado' => 'preservar',
            'conta_receber_id' => 10,
            'empresa_id' => 1,
            'valor' => 20,
            'tipo_pagamento' => '01',
            'received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('conta_receber_pagamentos')->insert([
            'legado' => 'preservar',
            'conta_receber_id' => 10,
            'empresa_id' => 1,
            'valor' => 20,
            'forma_pagamento' => '01',
            'data_pagamento' => now(),
            'lote_uuid' => '66666666-6666-4666-8666-666666666666',
            'status' => 'confirmado',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Rollback deliberadamente conservador: dados financeiros pré-existentes
        // não podem ser removidos porque a migration não sabe quem os criou.
        $pagamentos->down();
        $tracking->down();

        $this->assertTrue(Schema::hasTable('conta_receber_recebimentos'));
        $this->assertTrue(Schema::hasTable('conta_receber_pagamentos'));
        $this->assertTrue(Schema::hasColumn('conta_recebers', 'received_at'));
        $this->assertSame(1, DB::table('conta_receber_recebimentos')->where('legado', 'preservar')->count());
        $this->assertSame(1, DB::table('conta_receber_pagamentos')->where('legado', 'preservar')->count());
    }

    public function test_migration_010000_down_preserva_vinculos_e_historico_financeiro(): void
    {
        foreach (['venda_caixas', 'vendas', 'sangria_caixas', 'suprimento_caixas'] as $tabela) {
            Schema::create($tabela, function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('empresa_id')->default(1);
            });
        }

        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id')->default(1);
        });

        $migration = require database_path('migrations/2026_08_22_010000_add_abertura_caixa_id_to_cash_movements.php');
        $migration->up();

        foreach (['venda_caixas', 'vendas', 'sangria_caixas', 'suprimento_caixas'] as $tabela) {
            $this->assertTrue(Schema::hasColumn($tabela, 'abertura_caixa_id'));
        }

        foreach (['abertura_caixa_id', 'received_by_user_id', 'received_at'] as $coluna) {
            $this->assertTrue(Schema::hasColumn('conta_recebers', $coluna));
        }

        $this->assertTrue(Schema::hasTable('conta_receber_recebimentos'));

        DB::table('conta_receber_recebimentos')->insert([
            'conta_receber_id' => 10,
            'empresa_id' => 1,
            'abertura_caixa_id' => 20,
            'usuario_id' => 7,
            'valor' => 45,
            'tipo_pagamento' => '01',
            'received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration->down();

        // O rollback não pode apagar trilha financeira nem os vínculos que já
        // podem ter sido usados por caixas fechados em produção.
        $this->assertTrue(Schema::hasTable('conta_receber_recebimentos'));
        $this->assertSame(1, DB::table('conta_receber_recebimentos')->count());

        foreach (['venda_caixas', 'vendas', 'sangria_caixas', 'suprimento_caixas'] as $tabela) {
            $this->assertTrue(Schema::hasColumn($tabela, 'abertura_caixa_id'));
        }

        foreach (['abertura_caixa_id', 'received_by_user_id', 'received_at'] as $coluna) {
            $this->assertTrue(Schema::hasColumn('conta_recebers', $coluna));
        }
    }

    private function limparTabelas(): void
    {
        Schema::dropIfExists('conta_receber_recebimentos');
        Schema::dropIfExists('conta_receber_pagamentos');
        Schema::dropIfExists('conta_recebers');
        Schema::dropIfExists('suprimento_caixas');
        Schema::dropIfExists('sangria_caixas');
        Schema::dropIfExists('vendas');
        Schema::dropIfExists('venda_caixas');
    }
}
