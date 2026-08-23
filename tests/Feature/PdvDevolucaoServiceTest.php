<?php

namespace Tests\Feature;

use App\Models\PdvDevolucao;
use App\Models\Usuario;
use App\Models\VendaCaixa;
use App\Services\AutorizacaoDevolucaoService;
use App\Services\DevolucaoEstoqueService;
use App\Services\NFCeCancelamentoSeguroService;
use App\Services\PdvDevolucaoFinanceiroService;
use App\Services\PdvDevolucaoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class PdvDevolucaoServiceTest extends TestCase
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

        $this->criarSchema();
    }

    public function test_devolucao_nao_fiscal_preserva_estoque_filial_e_segunda_execucao_e_idempotente(): void
    {
        [$solicitante, $autorizador] = $this->usuarios();
        $venda = $this->venda([
            'estado_emissao' => 'novo',
            'estoque_filial_id' => 9,
            'filial_id' => 9,
        ]);

        // Prova também que o novo campo está realmente mass-assignable/persistido.
        $this->assertSame(9, (int) $venda->fresh()->estoque_filial_id);

        $auth = Mockery::mock(AutorizacaoDevolucaoService::class);
        $auth->shouldReceive('autorizar')->twice()->andReturn([
            'solicitante' => $solicitante,
            'autorizador' => $autorizador,
        ]);
        $auth->shouldReceive('registrar')->once()->andReturnUsing(function () {
            return new \App\Models\AutorizacaoDevolucao();
        });

        $financeiro = Mockery::mock(PdvDevolucaoFinanceiroService::class);
        $financeiro->shouldReceive('validarPreCondicoes')->once()->andReturn(['valor_dinheiro' => 0]);
        $financeiro->shouldReceive('processar')->once()->andReturn($this->financeiroOk());

        $estoque = Mockery::mock(DevolucaoEstoqueService::class);
        $estoque->shouldReceive('devolver')
            ->once()
            ->withArgs(function (VendaCaixa $sale) {
                return (int) $sale->estoque_filial_id === 9;
            });

        $nfce = Mockery::mock(NFCeCancelamentoSeguroService::class);
        $nfce->shouldNotReceive('cancelar');

        $service = new PdvDevolucaoService($auth, $estoque, $financeiro, $nfce);

        $primeira = $service->devolverNaoFiscal(1, (int) $venda->id, null, null, 'Cliente devolveu o produto sem uso.');
        $segunda = $service->devolverNaoFiscal(1, (int) $venda->id, null, null, 'Repetição da mesma solicitação.');

        $this->assertFalse($primeira['idempotente']);
        $this->assertTrue($segunda['idempotente']);
        $this->assertSame(1, PdvDevolucao::query()->count());
        $this->assertSame(9, (int) PdvDevolucao::query()->first()->estoque_filial_id);
        $this->assertSame('cancelado', $venda->fresh()->estado_emissao);
        $this->assertSame(1, (int) $venda->fresh()->retorno_estoque);
    }

    public function test_aguardando_sefaz_recente_bloqueia_requisicao_concorrente(): void
    {
        [$solicitante, $autorizador] = $this->usuarios();
        $venda = $this->venda([
            'estado_emissao' => 'aprovado',
            'chave' => str_repeat('3', 44),
        ]);
        $this->ledger($venda, 'aguardando_sefaz', now());

        $auth = Mockery::mock(AutorizacaoDevolucaoService::class);
        $auth->shouldReceive('autorizar')->once()->andReturn([
            'solicitante' => $solicitante,
            'autorizador' => $autorizador,
        ]);

        $financeiro = Mockery::mock(PdvDevolucaoFinanceiroService::class);
        $financeiro->shouldNotReceive('validarPreCondicoes');
        $financeiro->shouldNotReceive('processar');

        $estoque = Mockery::mock(DevolucaoEstoqueService::class);
        $estoque->shouldNotReceive('devolver');

        $nfce = Mockery::mock(NFCeCancelamentoSeguroService::class);
        $nfce->shouldNotReceive('cancelar');

        $service = new PdvDevolucaoService($auth, $estoque, $financeiro, $nfce);

        $this->expectException(ValidationException::class);
        $service->cancelarFiscal(1, (int) $venda->id, null, null, 'Cancelamento solicitado pelo cliente.');
    }

    public function test_aguardando_sefaz_expirado_reconsulta_e_conclui_sem_duplicar_ledger(): void
    {
        [$solicitante, $autorizador] = $this->usuarios(true);
        $venda = $this->venda([
            'estado_emissao' => 'aprovado',
            'chave' => str_repeat('4', 44),
            'estoque_filial_id' => 7,
            'filial_id' => 7,
        ]);
        $ledger = $this->ledger($venda, 'aguardando_sefaz', now()->subMinutes(3));

        $auth = Mockery::mock(AutorizacaoDevolucaoService::class);
        $auth->shouldReceive('autorizar')->once()->andReturn([
            'solicitante' => $solicitante,
            'autorizador' => $autorizador,
        ]);
        $auth->shouldReceive('registrar')->once()->andReturnUsing(function () {
            return new \App\Models\AutorizacaoDevolucao();
        });

        $financeiro = Mockery::mock(PdvDevolucaoFinanceiroService::class);
        $financeiro->shouldReceive('validarPreCondicoes')->once()->andReturn(['valor_dinheiro' => 0]);
        $financeiro->shouldReceive('processar')->once()->andReturn($this->financeiroOk());

        $estoque = Mockery::mock(DevolucaoEstoqueService::class);
        $estoque->shouldReceive('devolver')->once()->withArgs(function (VendaCaixa $sale) {
            return (int) $sale->estoque_filial_id === 7;
        });

        $nfce = Mockery::mock(NFCeCancelamentoSeguroService::class);
        $nfce->shouldReceive('cancelar')->once()->andReturn([
            'ok' => true,
            'cstat' => '135',
            'protocolo' => '135260000000999',
            'mensagem' => 'Evento de cancelamento já registrado e vinculado.',
            'ja_cancelada' => true,
            'data' => [
                'retEvento' => [
                    'infEvento' => [
                        'tpEvento' => '110111',
                        'cStat' => '135',
                        'xMotivo' => 'Evento de cancelamento já registrado e vinculado.',
                        'nProt' => '135260000000999',
                    ],
                ],
            ],
        ]);

        $service = new PdvDevolucaoService($auth, $estoque, $financeiro, $nfce);
        $resultado = $service->cancelarFiscal(1, (int) $venda->id, null, null, 'Cancelamento solicitado pelo cliente.');

        $this->assertTrue($resultado['ok']);
        $this->assertSame(1, PdvDevolucao::query()->count());
        $this->assertSame((int) $ledger->id, (int) PdvDevolucao::query()->first()->id);
        $this->assertSame('concluida', PdvDevolucao::query()->first()->status);
        $this->assertSame('135', PdvDevolucao::query()->first()->sefaz_cstat);
        $this->assertSame('cancelado', $venda->fresh()->estado_emissao);
    }

    private function usuarios(bool $persistir = false): array
    {
        if ($persistir) {
            DB::table('usuarios')->insert([
                ['id' => 7, 'empresa_id' => 1, 'nome' => 'Operador', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 8, 'empresa_id' => 1, 'nome' => 'Administrador', 'created_at' => now(), 'updated_at' => now()],
            ]);

            return [Usuario::findOrFail(7), Usuario::findOrFail(8)];
        }

        $solicitante = new Usuario();
        $solicitante->id = 7;
        $solicitante->empresa_id = 1;
        $solicitante->nome = 'Operador';

        $autorizador = new Usuario();
        $autorizador->id = 8;
        $autorizador->empresa_id = 1;
        $autorizador->nome = 'Administrador';

        return [$solicitante, $autorizador];
    }

    private function venda(array $override = []): VendaCaixa
    {
        return VendaCaixa::create(array_merge([
            'empresa_id' => 1,
            'usuario_id' => 7,
            'valor_total' => 100,
            'numero_nfce' => 0,
            'estado_emissao' => 'novo',
            'tipo_pagamento' => '17',
            'retorno_estoque' => 0,
            'filial_id' => null,
            'estoque_filial_id' => null,
            'abertura_caixa_id' => null,
            'chave' => null,
        ], $override));
    }

    private function ledger(VendaCaixa $venda, string $status, $updatedAt): PdvDevolucao
    {
        $ledger = PdvDevolucao::create([
            'empresa_id' => 1,
            'venda_caixa_id' => (int) $venda->id,
            'tipo' => 'cancelamento_fiscal',
            'status' => $status,
            'usuario_solicitante_id' => 7,
            'usuario_solicitante_nome' => 'Operador',
            'usuario_autorizador_id' => 8,
            'usuario_autorizador_nome' => 'Administrador',
            'motivo' => 'Cancelamento solicitado pelo cliente.',
            'valor_venda' => 100,
            'filial_id' => $venda->filial_id,
            'estoque_filial_id' => $venda->estoque_filial_id,
        ]);

        DB::table('pdv_devolucoes')->where('id', $ledger->id)->update([
            'updated_at' => $updatedAt,
        ]);

        return $ledger->fresh();
    }

    private function financeiroOk(): array
    {
        return [
            'pendente' => false,
            'motivo_pendencia' => null,
            'snapshot' => [],
            'abertura_original_id' => null,
            'abertura_compensacao_id' => null,
            'valor_dinheiro' => 0,
        ];
    }

    private function criarSchema(): void
    {
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

        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('usuario_id');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('autorizacoes_devolucao_caixa', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('venda_caixa_id');
            $table->string('tipo');
            $table->timestamps();
        });
    }
}
