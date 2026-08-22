<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venda_caixa_pre_vendas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('cliente_id')->nullable()->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->integer('funcionario_id')->unsigned();
            $table->foreign('funcionario_id')->references('id')->on('funcionarios');

            $table->integer('natureza_id')->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->decimal('valor_total', 16,7);
            $table->decimal('desconto', 10,2);
            $table->decimal('acrescimo', 10,2);

            $table->string('forma_pagamento', 20);
            $table->string('tipo_pagamento', 2);

            $table->string('observacao', 150);
            $table->integer('pedido_delivery_id');

            $table->string('bandeira_cartao', 2)->default('99');
            $table->string('cnpj_cartao', 18)->default('');
            $table->string('cAut_cartao', 20)->default('');
            $table->string('descricao_pag_outros', 80)->default('');

            $table->boolean('rascunho')->default(false);

            $table->boolean('status');

            // alter table venda_caixa_pre_vendas add column status boolean default 0;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('venda_caixa_pre_vendas');
    }
};
