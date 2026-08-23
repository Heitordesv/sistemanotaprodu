<?php

namespace Tests\Integration;

use App\Services\PdvDevolucaoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Throwable;

class PdvDevolucaoMysqlConcurrencyTest extends TestCase
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
            $this->markTestSkipped('A extensão pcntl é necessária.');
        }

        $this->recriarSchema();
        $this->seedCenario();
    }

    protected function tearDown(): void
    {
        if (isset($this->connectionName)) {
            $database = (string) DB::connection($this->connectionName)->getDatabaseName();
            if ($database !== '' && stripos($database, 'test') !== false) {
                foreach ([
                    'sangria_caixas', 'fatura_frente_caixas', 'comissao_vendas',
                    'conta_recebers', 'autorizacoes_devolucao_caixa', 'pdv_devolucoes',
                    'alteracao_estoques', 'item_venda_caixas', 'estoques', 'produtos',
                    'abertura_caixas', 'venda_caixas', 'usuarios',
                ] as $table) {
                    Schema::dropIfExists($table);
                }
            }
        }

        parent::tearDown();
    }

    public function test_duas_devolucoes_simultaneas_movimentam_estoque_uma_unica_vez(): void
    {
        [$parent1, $child1] = $this->socketPair();
        $pid1 = pcntl_fork();
        if ($pid1 === -1) {
            $this->fail('Não foi possível criar o primeiro processo.');
        }

        if ($pid1 === 0) {
            fclose($parent1);
            $this->worker($child1);
        }

        fclose($child1);

        [$parent2, $child2] = $this->socketPair();
        $pid2 = pcntl_fork();
        if ($pid2 === -1) {
            $this->fail('Não foi possível criar o segundo processo.');
        }

        if ($pid2 === 0) {
            fclose($parent2);
            fclose($parent1);
            $this->worker($child2);
        }

        fclose($child2);
        $this->reconectar();

        $this->assertSame('READY', trim((string) fgets($parent1)));
        $this->assertSame('READY', trim((string) fgets($parent2)));

        // Libera os dois processos somente depois de ambos estarem prontos.
        fwrite($parent1, "GO\n");
        fwrite($parent2, "GO\n");

        $resultado1 = json_decode(trim((string) fgets($parent1)), true);
        $resultado2 = json_decode(trim((string) fgets($parent2)), true);

        $status1 = 0;
        $status2 = 0;
        pcntl_waitpid($pid1, $status1);
        pcntl_waitpid($pid2, $status2);
        fclose($parent1);
        fclose($parent2);

        $this->assertTrue((bool) ($resultado1['ok'] ?? false), $resultado1['erro'] ?? 'Worker 1 falhou.');
        $this->assertTrue((bool) ($resultado2['ok'] ?? false), $resultado2['erro'] ?? 'Worker 2 falhou.');

        $idempotencias = [
            (bool) ($resultado1['idempotente'] ?? false),
            (bool) ($resultado2['idempotente'] ?? false),
        ];
        sort($idempotencias);
        $this->assertSame([false, true], $idempotencias);

        $this->assertSame(1, DB::table('pdv_devolucoes')->count());
        $this->assertSame('concluida', DB::table('pdv_devolucoes')->value('status'));
        $this->assertSame(1, DB::table('autorizacoes_devolucao_caixa')->count());

        // Filial começou em 5 e a venda tinha 2 unidades: deve terminar em 7,
        // nunca 9. A matriz permanece intocada.
        $this->assertSame(7.0, (float) DB::table('estoques')->where('produto_id', 50)->where('filial_id', 9)->value('quantidade'));
        $this->assertSame(50.0, (float) DB::table('estoques')->where('produto_id', 50)->whereNull('filial_id')->value('quantidade'));
        $this->assertSame(1, DB::table('alteracao_estoques')->where('tipo', 'devolucao')->count());
        $this->assertSame(1, (int) DB::table('venda_caixas')->where('id', 1)->value('retorno_estoque'));
    }

    private function worker($socket): void
    {
        $this->reconectar();
        fwrite($socket, "READY\n");
        fgets($socket);

        try {
            session(['user_logged' => [
                'id' => 7,
                'empresa' => 1,
                'empresa_id' => 1,
                'adm' => 1,
                'super' => 0,
            ]]);

            $resultado = app(PdvDevolucaoService::class)->devolverNaoFiscal(
                1,
                1,
                null,
                null,
                'Devolução concorrente de teste com motivo válido.'
            );

            fwrite($socket, json_encode([
                'ok' => true,
                'idempotente' => (bool) $resultado['idempotente'],
            ]) . "\n");
            fclose($socket);
            exit(0);
        } catch (Throwable $e) {
            fwrite($socket, json_encode([
                'ok' => false,
                'erro' => get_class($e) . ': ' . $e->getMessage(),
            ]) . "\n");
            fclose($socket);
            exit(1);
        }
    }

    private function socketPair(): array
    {
        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($pair === false) {
            $this->fail('Não foi possível criar canal IPC.');
        }
        return $pair;
    }

    private function reconectar(): void
    {
        DB::purge($this->connectionName);
        DB::reconnect($this->connectionName);
    }

    private function seedCenario(): void
    {
        DB::table('usuarios')->insert([
            'id' => 7,
            'empresa_id' => 1,
            'nome' => 'Administrador',
            'adm' => 1,
            'ativo' => 1,
            'senha' => password_hash('teste', PASSWORD_BCRYPT),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('venda_caixas')->insert([
            'id' => 1,
            'empresa_id' => 1,
            'usuario_id' => 7,
            'valor_total' => 20,
            'numero_nfce' => 0,
            'estado_emissao' => 'novo',
            'tipo_pagamento' => '17',
            'retorno_estoque' => 0,
            'filial_id' => 9,
            'estoque_filial_id' => 9,
            'abertura_caixa_id' => null,
            'chave' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('produtos')->insert([
            'id' => 50,
            'empresa_id' => 1,
            'nome' => 'Produto concorrente',
            'valor_compra' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('estoques')->insert([
            [
                'produto_id' => 50,
                'empresa_id' => 1,
                'filial_id' => null,
                'quantidade' => 50,
                'valor_compra' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'produto_id' => 50,
                'empresa_id' => 1,
                'filial_id' => 9,
                'quantidade' => 5,
                'valor_compra' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('item_venda_caixas')->insert([
            'produto_id' => 50,
            'venda_caixa_id' => 1,
            'quantidade' => 2,
            'valor' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function recriarSchema(): void
    {
        foreach ([
            'sangria_caixas', 'fatura_frente_caixas', 'comissao_vendas',
            'conta_recebers', 'autorizacoes_devolucao_caixa', 'pdv_devolucoes',
            'alteracao_estoques', 'item_venda_caixas', 'estoques', 'produtos',
            'abertura_caixas', 'venda_caixas', 'usuarios',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->string('nome');
            $table->boolean('adm')->default(false);
            $table->boolean('ativo')->default(true);
            $table->string('senha')->nullable();
            $table->timestamps();
        });

        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('usuario_id');
            $table->decimal('valor_total', 16, 7)->default(0);
            $table->unsignedInteger('numero_nfce')->default(0);
            $table->string('estado_emissao')->nullable();
            $table->string('tipo_pagamento', 10)->nullable();
            $table->boolean('retorno_estoque')->default(false);
            $table->unsignedInteger('filial_id')->nullable();
            $table->unsignedInteger('estoque_filial_id')->nullable();
            $table->unsignedInteger('abertura_caixa_id')->nullable();
            $table->string('chave', 60)->nullable();
            $table->timestamps();
        });

        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('usuario_id');
            $table->integer('status')->default(0);
            $table->timestamps();
        });

        Schema::create('produtos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->string('nome');
            $table->decimal('valor_compra', 16, 7)->default(0);
            $table->timestamps();
        });

        Schema::create('estoques', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('produto_id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('filial_id')->nullable();
            $table->decimal('quantidade', 16, 7)->default(0);
            $table->decimal('valor_compra', 16, 7)->default(0);
            $table->timestamps();
            $table->index(['produto_id', 'filial_id']);
        });

        Schema::create('item_venda_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('produto_id');
            $table->unsignedInteger('venda_caixa_id');
            $table->decimal('quantidade', 16, 7);
            $table->decimal('valor', 16, 7)->default(0);
            $table->timestamps();
        });

        Schema::create('alteracao_estoques', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('produto_id');
            $table->unsignedInteger('usuario_id');
            $table->decimal('quantidade', 16, 7);
            $table->string('tipo');
            $table->string('observacao')->nullable();
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('filial_id')->nullable();
            $table->timestamps();
        });

        Schema::create('pdv_devolucoes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('venda_caixa_id')->unique();
            $table->string('tipo', 30);
            $table->string('status', 40);
            $table->unsignedInteger('usuario_solicitante_id');
            $table->string('usuario_solicitante_nome');
            $table->unsignedInteger('usuario_autorizador_id');
            $table->string('usuario_autorizador_nome');
            $table->string('motivo')->nullable();
            $table->decimal('valor_venda', 16, 7)->default(0);
            $table->unsignedInteger('filial_id')->nullable();
            $table->unsignedInteger('estoque_filial_id')->nullable();
            $table->unsignedInteger('abertura_caixa_original_id')->nullable();
            $table->unsignedInteger('abertura_caixa_compensacao_id')->nullable();
            $table->decimal('valor_reembolso_dinheiro', 16, 7)->default(0);
            $table->string('sefaz_cstat', 20)->nullable();
            $table->string('sefaz_protocolo', 60)->nullable();
            $table->string('sefaz_mensagem')->nullable();
            $table->longText('financeiro_json')->nullable();
            $table->timestamp('sefaz_cancelada_em')->nullable();
            $table->timestamp('estoque_processado_em')->nullable();
            $table->timestamp('financeiro_processado_em')->nullable();
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();
        });

        Schema::create('autorizacoes_devolucao_caixa', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('venda_caixa_id');
            $table->unsignedInteger('usuario_solicitante_id');
            $table->string('usuario_solicitante_nome');
            $table->unsignedInteger('usuario_autorizador_id');
            $table->string('usuario_autorizador_nome');
            $table->string('tipo', 40);
            $table->string('numero_nfce')->nullable();
            $table->decimal('valor_venda', 16, 7)->default(0);
            $table->string('motivo')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('venda_caixa_id')->nullable();
            $table->decimal('valor_integral', 16, 7)->default(0);
            $table->decimal('valor_recebido', 16, 7)->default(0);
            $table->integer('status')->default(0);
            $table->string('tipo_pagamento')->nullable();
            $table->unsignedInteger('pdv_devolucao_id')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->unsignedInteger('cancelado_por_usuario_id')->nullable();
            $table->timestamps();
        });

        Schema::create('comissao_vendas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('venda_id');
            $table->string('tabela');
            $table->unsignedInteger('funcionario_id');
            $table->decimal('valor', 16, 7)->default(0);
            $table->integer('status')->default(0);
            $table->unsignedInteger('pdv_devolucao_id')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->unsignedInteger('cancelado_por_usuario_id')->nullable();
            $table->timestamps();
        });

        Schema::create('fatura_frente_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('venda_caixa_id');
            $table->string('forma_pagamento')->nullable();
            $table->decimal('valor', 16, 7)->default(0);
            $table->timestamps();
        });

        Schema::create('sangria_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->decimal('valor', 16, 7);
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('abertura_caixa_id')->nullable();
            $table->string('observacao')->nullable();
            $table->timestamps();
        });
    }
}
