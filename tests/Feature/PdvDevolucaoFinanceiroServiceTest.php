<?php

namespace Tests\Feature;

use App\Models\PdvDevolucao;
use App\Models\Usuario;
use App\Models\VendaCaixa;
use App\Services\PdvDevolucaoFinanceiroService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PdvDevolucaoFinanceiroServiceTest extends TestCase
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

    public function test_conta_e_comissao_pendentes_sao_canceladas_sem_delete_e_com_vinculo_da_devolucao(): void
    {
        $venda = $this->venda(['tipo_pagamento' => '17']);
        $devolucao = $this->devolucao($venda);
        $operador = $this->operador();

        DB::table('conta_recebers')->insert([
            'id' => 101,
            'empresa_id' => 1,
            'venda_caixa_id' => $venda->id,
            'valor_integral' => 100,
            'valor_recebido' => 0,
            'status' => 0,
            'tipo_pagamento' => '06',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('comissao_vendas')->insert([
            'id' => 201,
            'empresa_id' => 1,
            'venda_id' => $venda->id,
            'tabela' => 'venda_caixas',
            'funcionario_id' => 30,
            'valor' => 10,
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resultado = (new PdvDevolucaoFinanceiroService())->processar(
            $venda,
            $devolucao,
            $operador
        );

        $this->assertFalse($resultado['pendente']);
        $this->assertSame(1, DB::table('conta_recebers')->where('id', 101)->count());
        $this->assertSame(1, DB::table('comissao_vendas')->where('id', 201)->count());

        $conta = DB::table('conta_recebers')->where('id', 101)->first();
        $comissao = DB::table('comissao_vendas')->where('id', 201)->first();

        $this->assertSame(2, (int) $conta->status);
        $this->assertSame((int) $devolucao->id, (int) $conta->pdv_devolucao_id);
        $this->assertSame(7, (int) $conta->cancelado_por_usuario_id);
        $this->assertNotNull($conta->cancelado_em);

        $this->assertSame(2, (int) $comissao->status);
        $this->assertSame((int) $devolucao->id, (int) $comissao->pdv_devolucao_id);
        $this->assertSame(7, (int) $comissao->cancelado_por_usuario_id);
        $this->assertNotNull($comissao->cancelado_em);
    }

    public function test_conta_parcialmente_recebida_bloqueia_e_preserva_registro(): void
    {
        $venda = $this->venda(['tipo_pagamento' => '17']);

        DB::table('conta_recebers')->insert([
            'id' => 102,
            'empresa_id' => 1,
            'venda_caixa_id' => $venda->id,
            'valor_integral' => 100,
            'valor_recebido' => 20,
            'status' => 0,
            'tipo_pagamento' => '06',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            (new PdvDevolucaoFinanceiroService())->validarPreCondicoes($venda, $this->operador());
            $this->fail('Era esperado bloqueio para conta parcialmente recebida.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('liquidada', $e->getMessage());
        }

        $conta = DB::table('conta_recebers')->where('id', 102)->first();
        $this->assertSame(0, (int) $conta->status);
        $this->assertSame(20.0, (float) $conta->valor_recebido);
        $this->assertNull($conta->pdv_devolucao_id);
    }

    public function test_comissao_liquidada_bloqueia_e_preserva_registro(): void
    {
        $venda = $this->venda(['tipo_pagamento' => '17']);

        DB::table('comissao_vendas')->insert([
            'id' => 202,
            'empresa_id' => 1,
            'venda_id' => $venda->id,
            'tabela' => 'venda_caixas',
            'funcionario_id' => 30,
            'valor' => 10,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        try {
            (new PdvDevolucaoFinanceiroService())->validarPreCondicoes($venda, $this->operador());
        } finally {
            $comissao = DB::table('comissao_vendas')->where('id', 202)->first();
            $this->assertSame(1, (int) $comissao->status);
            $this->assertNull($comissao->pdv_devolucao_id);
        }
    }

    public function test_venda_em_dinheiro_de_caixa_fechado_cria_compensacao_no_caixa_atual_sem_reabrir_historico(): void
    {
        DB::table('abertura_caixas')->insert([
            [
                'id' => 10,
                'empresa_id' => 1,
                'usuario_id' => 7,
                'status' => 1,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(2),
            ],
            [
                'id' => 11,
                'empresa_id' => 1,
                'usuario_id' => 7,
                'status' => 0,
                'created_at' => now()->subHour(),
                'updated_at' => now()->subHour(),
            ],
        ]);

        $venda = $this->venda([
            'tipo_pagamento' => '01',
            'valor_total' => 100,
            'abertura_caixa_id' => 10,
        ]);
        $devolucao = $this->devolucao($venda);

        $resultado = (new PdvDevolucaoFinanceiroService())->processar(
            $venda,
            $devolucao,
            $this->operador()
        );

        $this->assertFalse($resultado['pendente']);
        $this->assertSame(10, (int) $resultado['abertura_original_id']);
        $this->assertSame(11, (int) $resultado['abertura_compensacao_id']);

        $this->assertSame(1, (int) DB::table('abertura_caixas')->where('id', 10)->value('status'));
        $this->assertSame(0, (int) DB::table('abertura_caixas')->where('id', 11)->value('status'));

        $sangria = DB::table('sangria_caixas')->first();
        $this->assertNotNull($sangria);
        $this->assertSame(11, (int) $sangria->abertura_caixa_id);
        $this->assertSame(100.0, (float) $sangria->valor);
        $this->assertStringContainsString('VENDA #' . $venda->id, (string) $sangria->observacao);
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
            'abertura_caixa_id' => null,
        ], $override));
    }

    private function devolucao(VendaCaixa $venda): PdvDevolucao
    {
        return PdvDevolucao::create([
            'empresa_id' => 1,
            'venda_caixa_id' => $venda->id,
            'tipo' => 'nao_fiscal',
            'status' => 'processando',
            'usuario_solicitante_id' => 7,
            'usuario_solicitante_nome' => 'Operador',
            'usuario_autorizador_id' => 8,
            'usuario_autorizador_nome' => 'Administrador',
            'valor_venda' => 100,
        ]);
    }

    private function operador(): Usuario
    {
        $usuario = new Usuario();
        $usuario->id = 7;
        $usuario->empresa_id = 1;
        $usuario->nome = 'Operador';
        return $usuario;
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
            $table->unsignedInteger('abertura_caixa_id')->nullable();
            $table->timestamps();
        });

        Schema::create('pdv_devolucoes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('venda_caixa_id');
            $table->string('tipo');
            $table->string('status');
            $table->unsignedInteger('usuario_solicitante_id');
            $table->string('usuario_solicitante_nome');
            $table->unsignedInteger('usuario_autorizador_id');
            $table->string('usuario_autorizador_nome');
            $table->decimal('valor_venda', 16, 7)->default(0);
            $table->unsignedInteger('abertura_caixa_original_id')->nullable();
            $table->unsignedInteger('abertura_caixa_compensacao_id')->nullable();
            $table->decimal('valor_reembolso_dinheiro', 16, 7)->default(0);
            $table->longText('financeiro_json')->nullable();
            $table->timestamps();
        });

        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('venda_caixa_id')->nullable();
            $table->decimal('valor_integral', 16, 7)->default(0);
            $table->decimal('valor_recebido', 16, 7)->default(0);
            $table->integer('status')->default(0);
            $table->string('tipo_pagamento', 20)->nullable();
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
            $table->string('forma_pagamento', 20)->nullable();
            $table->decimal('valor', 16, 7)->default(0);
            $table->timestamps();
        });

        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('usuario_id');
            $table->integer('status')->default(0);
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
