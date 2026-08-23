<?php

namespace Tests\Integration;

use App\Exceptions\CaixaMovimentacaoException;
use App\Models\AberturaCaixa;
use App\Services\CaixaMovimentacaoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Throwable;

class CaixaMovimentacaoMysqlConcurrencyTest extends TestCase
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
                Schema::dropIfExists('suprimento_caixas');
                Schema::dropIfExists('sangria_caixas');
                Schema::dropIfExists('venda_caixas');
                Schema::dropIfExists('abertura_caixas');
            }
        }

        parent::tearDown();
    }

    /**
     * @dataProvider movimentosProvider
     */
    public function test_fechamento_vencedor_impede_movimento_de_entrar_depois_da_consolidacao(
        string $tabela,
        string $campoValor,
        float $valor
    ): void {
        $this->criarAbertura(100);

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
                app(CaixaMovimentacaoService::class)->executar(
                    1,
                    7,
                    function (?AberturaCaixa $abertura) use ($tabela, $campoValor, $valor) {
                        DB::table($tabela)->insert([
                            'empresa_id' => 1,
                            'usuario_id' => 7,
                            'abertura_caixa_id' => (int) $abertura->id,
                            $campoValor => $valor,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    },
                    true
                );

                fwrite($childSocket, json_encode([
                    'ok' => false,
                    'erro' => 'Movimento foi aceito depois do fechamento.',
                ]) . "\n");
                fclose($childSocket);
                exit(1);
            } catch (CaixaMovimentacaoException $e) {
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
        $this->assertSame(0, DB::table($tabela)->count());
    }

    /**
     * @dataProvider movimentosProvider
     */
    public function test_movimento_vencedor_faz_fechamento_esperar_o_commit_e_depois_fica_visivel(
        string $tabela,
        string $campoValor,
        float $valor
    ): void {
        $this->criarAbertura(110);

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
                app(CaixaMovimentacaoService::class)->executar(
                    1,
                    7,
                    function (?AberturaCaixa $abertura) use ($childSocket, $tabela, $campoValor, $valor) {
                        DB::table($tabela)->insert([
                            'empresa_id' => 1,
                            'usuario_id' => 7,
                            'abertura_caixa_id' => (int) $abertura->id,
                            $campoValor => $valor,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Neste momento o movimento ainda não foi commitado e o
                        // lock da abertura continua em posse deste processo.
                        fwrite($childSocket, "LOCKED\n");
                        fflush($childSocket);
                        usleep(700000);
                    },
                    true
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

            DB::table('abertura_caixas')->where('id', 110)->update([
                'status' => 1,
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
        $this->assertGreaterThanOrEqual(0.40, $tempoEspera, 'O fechamento não aguardou o lock do movimento.');
        $this->assertSame(1, DB::table($tabela)->count());
        $this->assertSame(1, (int) DB::table('abertura_caixas')->where('id', 110)->value('status'));
    }

    public static function movimentosProvider(): array
    {
        return [
            'venda PDV' => ['venda_caixas', 'valor_total', 45.00],
            'sangria' => ['sangria_caixas', 'valor', 10.00],
            'suprimento' => ['suprimento_caixas', 'valor', 15.00],
        ];
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
            'status' => 0,
            'valor' => 0,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);
    }

    private function recriarSchemaMinimo(): void
    {
        Schema::dropIfExists('suprimento_caixas');
        Schema::dropIfExists('sangria_caixas');
        Schema::dropIfExists('venda_caixas');
        Schema::dropIfExists('abertura_caixas');

        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('empresa_id');
            $table->boolean('status')->default(false);
            $table->decimal('valor', 15, 2)->default(0);
            $table->timestamps();
            $table->index(['empresa_id', 'usuario_id', 'status'], 'acaixa_mov_empresa_usuario_status_idx');
        });

        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sangria_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->decimal('valor', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('suprimento_caixas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->decimal('valor', 15, 2)->default(0);
            $table->timestamps();
        });
    }
}
