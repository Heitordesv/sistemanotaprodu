<?php

namespace Tests\Feature;

use App\Http\Controllers\VendaSeguraController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VendaErroSeguroTest extends TestCase
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

        Schema::create('empresas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
        });

        Schema::create('natureza_operacaos', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('produtos', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->boolean('status')->default(false);
            $table->unsignedBigInteger('primeira_venda_nfe')->default(0);
            $table->unsignedBigInteger('ultima_venda_nfe')->default(0);
        });

        Schema::create('vendas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('abertura_caixa_id')->nullable();
            $table->unsignedBigInteger('frete_id')->nullable();
        });

        DB::table('empresas')->insert(['id' => 1]);
        DB::table('natureza_operacaos')->insert([
            'id' => 10,
            'empresa_id' => 1,
        ]);
        DB::table('produtos')->insert([
            'id' => 100,
            'empresa_id' => 1,
        ]);
        DB::table('clientes')->insert([
            'id' => 1000,
            'empresa_id' => 1,
        ]);

        session(['user_logged' => [
            'id' => 7,
            'empresa' => 1,
            'adm' => 1,
            'super' => 0,
        ]]);
    }

    public function test_erro_interno_da_criacao_nao_e_exposto_ao_usuario(): void
    {
        $request = Request::create('/vendas', 'POST', [
            'type' => 'venda',
            'empresa_id' => 1,
            'natureza_id' => 10,
            'tipo_frete' => '9',
            'produto_id' => [],
            'cliente_id' => null,
            'transportadora_id' => null,
            'filial_id' => -1,
            'subtotal_item' => [],
            // Sem tipo_pagamentos de propósito: dispara erro controlado dentro
            // do fluxo seguro antes de qualquer gravação financeira.
        ]);

        $response = app(VendaSeguraController::class)->store($request);

        $this->assertTrue($response->isRedirect());
        $mensagem = (string) session('flash_erro');
        $this->assertSame(
            'Não foi possível adicionar a venda. Verifique os dados e tente novamente.',
            $mensagem
        );
        $this->assertStringNotContainsString('Forma de pagamento da venda não informada', $mensagem);
        $this->assertStringNotContainsString('SQLSTATE', $mensagem);
    }

    public function test_erro_interno_do_update_nao_expoe_sql_ou_linha_da_excecao(): void
    {
        DB::table('vendas')->insert([
            'id' => 50,
            'empresa_id' => 1,
            'usuario_id' => 7,
            'abertura_caixa_id' => null,
            'frete_id' => 999,
        ]);

        $request = Request::create('/vendas/50', 'PUT', [
            'type' => 'venda',
            'empresa_id' => 1,
            'natureza_id' => 10,
            'produto_id' => [100],
            'cliente_id' => 1000,
            'transportadora_id' => null,
            'filial_id' => -1,
            'tipo_frete' => '9',
            'forma_pagamento' => 'a_vista',
            'subtotal_item' => ['10,00'],
            'quantidade' => ['1,00'],
            'valor_unitario' => ['10,00'],
        ]);

        // item_vendas/fretes não existem intencionalmente: o acesso a uma
        // relação dispara QueryException dentro da transação segura.
        $response = app(VendaSeguraController::class)->update($request, 50);

        $this->assertTrue($response->isRedirect());
        $mensagem = (string) session('flash_erro');
        $this->assertSame('Não foi possível atualizar a venda.', $mensagem);
        $this->assertStringNotContainsString('SQLSTATE', $mensagem);
        $this->assertStringNotContainsString('item_vendas', $mensagem);
        $this->assertStringNotContainsString('fretes', $mensagem);
    }
}
