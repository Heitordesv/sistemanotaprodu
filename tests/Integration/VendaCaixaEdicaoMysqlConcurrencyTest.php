<?php

namespace Tests\Integration;

use App\Exceptions\CaixaMovimentacaoException;
use App\Models\Venda;
use App\Services\VendaCaixaEdicaoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Throwable;

class VendaCaixaEdicaoMysqlConcurrencyTest extends TestCase
{
    private string $connectionName;

    protected function setUp(): void
    {
        parent::setUp();

        if ((string) env('RUN_MYSQL_CONCURRENCY_TESTS') !== '1') {
            $this->markTestSkipped('Defina RUN_MYSQL_CONCURRENCY_TESTS=1 para executar concorrência real.');
        }

        $this->connectionName = (string) config('database.default');

        if (DB::connection($this->connectionName)->getDriverName() !== 'mysql') {
            $this->markTestSkipped('Este teste exige MySQL/InnoDB.');
        }

        $database = (string) DB::connection($this->connectionName)->getDatabaseName();
        if ($database === '' || stripos($database, 'test') === false) {
            $this->fail('Teste recusado: DB_DATABASE precisa conter "test".');
        }

        if (!function_exists('pcntl_fork') || !function_exists('pcntl_waitpid')) {
            $this->markTestSkipped('A extensão pcntl é necessária para concorrência real.');
        }

        $this->recriarSchemaMinimo();
    }

    protected function tearDown(): void
    {
        if (isset($this->connectionName)) {
            $database = (string) DB::connection($this->connectionName)->getDatabaseName();
            if ($database !== '' && stripos($database, 'test') !== false) {
                Schema::dropIfExists('vendas');
                Schema::dropIfExists('abertura_caixas');
            }
        }

        parent::tearDown();
    }

    public function test_fechamento_vencedor_impede_edicao_de_nfe_depois_da_consolidacao(): void
    {
        $this->criarAberturaEVenda(100, 11, 45.00);

        [$parentSocket, $childSocket] = $this->socketPair();
        $pid = pcntl_fork();

        if ($pid === -1) {
            $this->fail('Não foi possível criar processo concorrente.');
        }

        if ($pid === 0) {
            fclose($parentSocket);
            $this->reconectarNoFilho();
            fwrite($childSocket, "READY\n");
            fflush($childSocket);
            fgets($childSocket);

            try {
                app(VendaCaixaEdicaoService::class)->executar(11, 1, function (Venda $venda) {
                    $venda->valor_total = 99;
                    $venda->save();
                });

                fwrite($childSocket, json_encode([
                    'ok' => false,
                    'erro' => 'A edição foi aceita depois do fechamento.',
                ]) . "\n");
                fclose($childSocket);
                exit(1);
            } catch (CaixaMovimentacaoException $e) {
                fwrite($childSocket, json_encode([
                    'ok' => str_contains($e->getMessage(), 'caixa já fechado'),
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
            $abertura = DB::table('abertura_caixas')
                ->where('id', 100)
                ->where('status', 0)
                ->lockForUpdate()
                ->first();
            $this->assertNotNull($abertura);

            fwrite($parentSocket, "GO\n");
            fflush($parentSocket);
            usleep(500000);

            DB::table('abertura_caixas')->where('id', 100)->update([
                'status' => 1,
                'ultima_venda_nfe' => 11,
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
        $this->assertSame(1, (int) DB::table('abertura_caixas')->where('id', 100)->value('status'));
        $this->assertSame(45.0, (float) DB::table('vendas')->where('id', 11)->value('valor_total'));
    }

    public function test_edicao_vencedora_faz_fechamento_esperar_e_consolidar_a_versao_nova(): void
    {
        $this->criarAberturaEVenda(110, 21, 45.00);

        [$parentSocket, $childSocket] = $this->socketPair();
        $pid = pcntl_fork();

        if ($pid === -1) {
            $this->fail('Não foi possível criar processo concorrente.');
        }

        if ($pid === 0) {
            fclose($parentSocket);
            $this->reconectarNoFilho();
            fwrite($childSocket, "READY\n");
            fflush($childSocket);
            fgets($childSocket);

            try {
                app(VendaCaixaEdicaoService::class)->executar(
                    21,
                    1,
                    function (Venda $venda) use ($childSocket) {
                        $venda->valor_total = 99;
                        $venda->save();

                        // O valor novo ainda não foi commitado e o lock da
                        // abertura precisa continuar retido até o fim da edição.
                        fwrite($childSocket, "LOCKED\n");
                        fflush($childSocket);
                        usleep(700000);
                    }
                );

                fwrite($childSocket, "DONE\n");
                fclose($childSocket);
                exit(0);
            } catch (Throwable $e) {
                fwrite($childSocket, 'ERROR:' . $e->getMessage() . "\n");
                fclose($childSocket);
                exit(1);
            }
        }

        fclose($childSocket);
        $this->reconectarNoPai();
        $this->assertSame('READY', trim((string) fgets($parentSocket)));
        fwrite($parentSocket, "GO\n");
        fflush($parentSocket);
        $this->assertSame('LOCKED', trim((string) fgets($parentSocket)));

        $inicioEspera = microtime(true);

        DB::beginTransaction();
        try {
            $abertura = DB::table('abertura_caixas')
                ->where('id', 110)
                ->where('status', 0)
                ->lockForUpdate()
                ->first();

            $tempoEspera = microtime(true) - $inicioEspera;
            $this->assertNotNull($abertura);
            $this->assertSame(99.0, (float) DB::table('vendas')->where('id', 21)->value('valor_total'));

            DB::table('abertura_caixas')->where('id', 110)->update([
                'status' => 1,
                'ultima_venda_nfe' => 21,
                'updated_at' => now(),
            ]);
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $mensagemFilho = trim((string) fgets($parentSocket));
        $status = 0;
        pcntl_waitpid($pid, $status);
        fclose($parentSocket);

        $this->assertSame('DONE', $mensagemFilho);
        $this->assertGreaterThanOrEqual(0.40, $tempoEspera, 'O fechamento não aguardou o lock da edição da NFe.');
        $this->assertSame(99.0, (float) DB::table('vendas')->where('id', 21)->value('valor_total'));
        $this->assertSame(1, (int) DB::table('abertura_caixas')->where('id', 110)->value('status'));
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

    private function criarAberturaEVenda(int $aberturaId, int $vendaId, float $valor): void
    {
        DB::table('abertura_caixas')->insert([
            'id' => $aberturaId,
            'usuario_id' => 7,
            'empresa_id' => 1,
            'status' => 0,
            'valor' => 0,
            'primeira_venda_nfe' => $vendaId - 1,
            'ultima_venda_nfe' => $vendaId - 1,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        DB::table('vendas')->insert([
            'id' => $vendaId,
            'empresa_id' => 1,
            'usuario_id' => 7,
            'abertura_caixa_id' => $aberturaId,
            'valor_total' => $valor,
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);
    }

    private function recriarSchemaMinimo(): void
    {
        Schema::dropIfExists('vendas');
        Schema::dropIfExists('abertura_caixas');

        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('empresa_id');
            $table->boolean('status')->default(false);
            $table->decimal('valor', 15, 2)->default(0);
            $table->unsignedBigInteger('primeira_venda_nfe')->default(0);
            $table->unsignedBigInteger('ultima_venda_nfe')->default(0);
            $table->timestamps();
            $table->index(['empresa_id', 'usuario_id', 'status'], 'acaixa_venda_empresa_usuario_status_idx');
        });

        Schema::create('vendas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->timestamps();
            $table->index(['empresa_id', 'usuario_id', 'abertura_caixa_id'], 'vendas_venda_caixa_idx');
        });
    }
}
