<?php

namespace Tests\Integration;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PdvDevolucaoMigrationMysqlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ((string) env('RUN_MYSQL_CONCURRENCY_TESTS') !== '1') {
            $this->markTestSkipped('Integração MySQL de devolução desabilitada.');
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('Este teste exige MySQL.');
        }

        $database = (string) DB::connection()->getDatabaseName();
        if ($database === '' || stripos($database, 'test') === false) {
            $this->fail('Teste recusado: DB_DATABASE precisa conter "test".');
        }

        $this->limpar();
    }

    protected function tearDown(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $database = (string) DB::connection()->getDatabaseName();
            if ($database !== '' && stripos($database, 'test') !== false) {
                $this->limpar();
            }
        }

        parent::tearDown();
    }

    public function test_migration_cria_ledger_unico_e_rollback_nao_apaga_auditoria(): void
    {
        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
        });

        Schema::create('alteracao_estoques', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
        });

        $migration = require database_path('migrations/2026_08_23_014500_harden_pdv_devolucao.php');
        $migration->up();

        $this->assertTrue(Schema::hasColumn('venda_caixas', 'estoque_filial_id'));
        $this->assertTrue(Schema::hasColumn('alteracao_estoques', 'filial_id'));
        $this->assertTrue(Schema::hasTable('autorizacoes_devolucao_caixa'));
        $this->assertTrue(Schema::hasTable('pdv_devolucoes'));

        DB::table('pdv_devolucoes')->insert($this->ledger(99));

        $duplicou = false;
        try {
            DB::table('pdv_devolucoes')->insert($this->ledger(99));
            $duplicou = true;
        } catch (QueryException $e) {
            $this->assertSame('23000', (string) $e->getCode());
        }

        $this->assertFalse($duplicou, 'A mesma venda não pode possuir dois ledgers de devolução.');
        $this->assertSame(1, DB::table('pdv_devolucoes')->where('venda_caixa_id', 99)->count());

        $migration->down();

        // Auditoria fiscal/financeira é deliberadamente não destrutiva.
        $this->assertTrue(Schema::hasTable('pdv_devolucoes'));
        $this->assertSame(1, DB::table('pdv_devolucoes')->where('venda_caixa_id', 99)->count());
        $this->assertTrue(Schema::hasColumn('venda_caixas', 'estoque_filial_id'));
    }

    private function ledger(int $vendaId): array
    {
        return [
            'empresa_id' => 1,
            'venda_caixa_id' => $vendaId,
            'tipo' => 'nao_fiscal',
            'status' => 'concluida',
            'usuario_solicitante_id' => 10,
            'usuario_solicitante_nome' => 'Operador',
            'usuario_autorizador_id' => 20,
            'usuario_autorizador_nome' => 'Administrador',
            'motivo' => 'Teste de devolução segura',
            'valor_venda' => 100,
            'valor_reembolso_dinheiro' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function limpar(): void
    {
        Schema::dropIfExists('pdv_devolucoes');
        Schema::dropIfExists('autorizacoes_devolucao_caixa');
        Schema::dropIfExists('alteracao_estoques');
        Schema::dropIfExists('venda_caixas');
    }
}
