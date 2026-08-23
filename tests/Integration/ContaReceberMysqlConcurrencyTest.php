<?php

namespace Tests\Integration;

use App\Models\ContaReceber;
use App\Services\ContaReceberPagamentoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class ContaReceberMysqlConcurrencyTest extends TestCase
{
    private string $connectionName;

    protected function setUp(): void
    {
        parent::setUp();

        if ((string) env('RUN_MYSQL_CONCURRENCY_TESTS') !== '1') {
            $this->markTestSkipped('Defina RUN_MYSQL_CONCURRENCY_TESTS=1 para executar os testes reais de concorrência.');
        }

        $this->connectionName = (string) config('database.default');

        if (DB::connection($this->connectionName)->getDriverName() !== 'mysql') {
            $this->markTestSkipped('Este teste exige MySQL/InnoDB.');
        }

        $database = (string) DB::connection($this->connectionName)->getDatabaseName();
        if ($database === '' || stripos($database, 'test') === false) {
            $this->fail('Teste de concorrência recusado: DB_DATABASE precisa conter "test".');
        }

        if (!function_exists('pcntl_fork') || !function_exists('pcntl_waitpid')) {
            $this->markTestSkipped('A extensão pcntl é necessária para concorrência real entre processos.');
        }

        $this->recriarSchemaMinimo();

        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'super' => 0,
        ]]);
    }

    protected function tearDown(): void
    {
        if (isset($this->connectionName)) {
            $database = (string) DB::connection($this->connectionName)->getDatabaseName();
            if ($database !== '' && stripos($database, 'test') !== false) {
                Schema::dropIfExists('conta_receber_recebimentos');
                Schema::dropIfExists('conta_receber_pagamentos');
                Schema::dropIfExists('conta_recebers');
                Schema::dropIfExists('abertura_caixas');
            }
        }

        parent::tearDown();
    }

    public function test_mesmo_lote_nao_duplica_quando_segunda_requisicao_espera_lock_innodb(): void
    {
        $this->criarAbertura(80);
        $this->criarConta(103, 45.00);

        $lote = '44444444-4444-4444-8444-444444444444';
        [$parentSocket, $childSocket] = $this->socketPair();
        $pid = pcntl_fork();

        if ($pid === -1) {
            $this->fail('Não foi possível criar o processo concorrente.');
        }

        if ($pid === 0) {
            fclose($parentSocket);
            $this->reconectarNoFilho();
            fwrite($childSocket, "READY\n");
            fgets($childSocket);

            try {
                session(['user_logged' => [
                    'id' => 7,
                    'empresa' => 1,
                    'super' => 0,
                ]]);

                $conta = ContaReceber::findOrFail(103);
                $resultado = app(ContaReceberPagamentoService::class)->registrarMultiplos(
                    $conta,
                    [['forma_pagamento' => '01', 'valor' => 20.00]],
                    now()->toDateString(),
                    $lote
                );

                fwrite($childSocket, json_encode([
                    'ok' => true,
                    'idempotente' => (bool) $resultado->getAttribute('recebimento_idempotente'),
                ]) . "\n");
                fclose($childSocket);
                exit(0);
            } catch (Throwable $e) {
                fwrite($childSocket, json_encode([
                    'ok' => false,
                    'erro' => $e->getMessage(),
                ]) . "\n");
                fclose($childSocket);
                exit(1);
            }
        }

        fclose($childSocket);
        $this->reconectarNoPai();
        $this->assertSame('READY', trim((string) fgets($parentSocket)));

        DB::beginTransaction();
        try {
            DB::table('abertura_caixas')->where('id', 80)->lockForUpdate()->first();
            DB::table('conta_recebers')->where('id', 103)->lockForUpdate()->first();

            // Simula a primeira requisição ainda não commitada. O processo filho
            // não consegue enxergar este lote no fast-path e ficará esperando o
            // mesmo lock da abertura.
            DB::table('conta_receber_pagamentos')->insert([
                'conta_receber_id' => 103,
                'empresa_id' => 1,
                'valor' => 20.00,
                'forma_pagamento' => '01',
                'data_pagamento' => now(),
                'origem' => 'manual',
                'provedor' => null,
                'external_id' => null,
                'lote_uuid' => $lote,
                'status' => 'confirmado',
                'observacao' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('conta_receber_recebimentos')->insert([
                'conta_receber_id' => 103,
                'empresa_id' => 1,
                'abertura_caixa_id' => 80,
                'usuario_id' => 7,
                'valor' => 20.00,
                'tipo_pagamento' => '01',
                'received_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('conta_recebers')->where('id', 103)->update([
                'valor_recebido' => 20.00,
                'status' => 0,
                'tipo_pagamento' => '01',
                'abertura_caixa_id' => 80,
                'received_by_user_id' => 7,
                'received_at' => now(),
                'updated_at' => now(),
            ]);

            fwrite($parentSocket, "GO\n");
            usleep(500000);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            fwrite($parentSocket, "GO\n");
            throw $e;
        }

        $resposta = json_decode(trim((string) fgets($parentSocket)), true);
        $status = 0;
        pcntl_waitpid($pid, $status);
        fclose($parentSocket);

        $this->assertTrue((bool) ($resposta['ok'] ?? false), $resposta['erro'] ?? 'Processo concorrente falhou.');
        $this->assertTrue((bool) ($resposta['idempotente'] ?? false));
        $this->assertSame(1, DB::table('conta_receber_pagamentos')->where('lote_uuid', $lote)->count());
        $this->assertSame(1, DB::table('conta_receber_recebimentos')->where('conta_receber_id', 103)->count());
        $this->assertSame(20.0, (float) DB::table('conta_recebers')->where('id', 103)->value('valor_recebido'));
    }

    public function test_fechamento_commitado_enquanto_recebimento_espera_lock_impede_gravacao_posterior(): void
    {
        $this->criarAbertura(90);
        $this->criarConta(104, 45.00);

        [$parentSocket, $childSocket] = $this->socketPair();
        $pid = pcntl_fork();

        if ($pid === -1) {
            $this->fail('Não foi possível criar o processo concorrente.');
        }

        if ($pid === 0) {
            fclose($parentSocket);
            $this->reconectarNoFilho();
            fwrite($childSocket, "READY\n");
            fgets($childSocket);

            try {
                session(['user_logged' => [
                    'id' => 7,
                    'empresa' => 1,
                    'super' => 0,
                ]]);

                app(ContaReceberPagamentoService::class)->registrarMultiplos(
                    ContaReceber::findOrFail(104),
                    [['forma_pagamento' => '01', 'valor' => 45.00]],
                    now()->toDateString(),
                    '55555555-5555-4555-8555-555555555555'
                );

                fwrite($childSocket, json_encode([
                    'ok' => false,
                    'erro' => 'O recebimento foi aceito depois do fechamento.',
                ]) . "\n");
                fclose($childSocket);
                exit(1);
            } catch (RuntimeException $e) {
                fwrite($childSocket, json_encode([
                    'ok' => str_contains($e->getMessage(), 'Nenhum caixa aberto'),
                    'erro' => $e->getMessage(),
                ]) . "\n");
                fclose($childSocket);
                exit(0);
            } catch (Throwable $e) {
                fwrite($childSocket, json_encode([
                    'ok' => false,
                    'erro' => $e->getMessage(),
                ]) . "\n");
                fclose($childSocket);
                exit(1);
            }
        }

        fclose($childSocket);
        $this->reconectarNoPai();
        $this->assertSame('READY', trim((string) fgets($parentSocket)));

        DB::beginTransaction();
        try {
            DB::table('abertura_caixas')->where('id', 90)->lockForUpdate()->first();
            fwrite($parentSocket, "GO\n");
            usleep(500000);

            DB::table('abertura_caixas')->where('id', 90)->update([
                'status' => 1,
                'updated_at' => now(),
            ]);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            fwrite($parentSocket, "GO\n");
            throw $e;
        }

        $resposta = json_decode(trim((string) fgets($parentSocket)), true);
        $status = 0;
        pcntl_waitpid($pid, $status);
        fclose($parentSocket);

        $this->assertTrue((bool) ($resposta['ok'] ?? false), $resposta['erro'] ?? 'Processo concorrente falhou.');
        $this->assertSame(1, (int) DB::table('abertura_caixas')->where('id', 90)->value('status'));
        $this->assertSame(0.0, (float) DB::table('conta_recebers')->where('id', 104)->value('valor_recebido'));
        $this->assertSame(0, DB::table('conta_receber_pagamentos')->where('conta_receber_id', 104)->count());
        $this->assertSame(0, DB::table('conta_receber_recebimentos')->where('conta_receber_id', 104)->count());
    }

    private function socketPair(): array
    {
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($sockets === false) {
            $this->fail('Não foi possível criar canal IPC para o teste concorrente.');
        }

        return $sockets;
    }

    private function reconectarNoFilho(): void
    {
        DB::purge($this->connectionName);
        DB::reconnect($this->connectionName);
    }

    private function reconectarNoPai(): void
    {
        DB::purge($this->connectionName);
        DB::reconnect($this->connectionName);
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

    private function recriarSchemaMinimo(): void
    {
        Schema::dropIfExists('conta_receber_recebimentos');
        Schema::dropIfExists('conta_receber_pagamentos');
        Schema::dropIfExists('conta_recebers');
        Schema::dropIfExists('abertura_caixas');

        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('filial_id')->nullable();
            $table->boolean('status')->default(false);
            $table->decimal('valor', 15, 2)->default(0);
            $table->timestamps();
            $table->index(['empresa_id', 'usuario_id', 'status'], 'acaixa_empresa_usuario_status_idx');
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
