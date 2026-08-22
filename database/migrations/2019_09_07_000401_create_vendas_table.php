<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->integer('natureza_id')->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->integer('frete_id')->nullable()->unsigned();
            $table->foreign('frete_id')->references('id')->on('fretes')->onDelete('cascade');

            $table->integer('transportadora_id')->nullable()->unsigned();
            $table->foreign('transportadora_id')->references('id')->on('transportadoras')
            ->onDelete('cascade');

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->integer('nSerie')->default(0);
            $table->timestamp('data_emissao')->nullable();

            $table->timestamp('data_registro')->useCurrent();
            $table->date('data_entrega')->nullable();
            $table->decimal('valor_total', 16,7);
            $table->decimal('desconto', 10,2);
            $table->decimal('acrescimo', 10,2);

            $table->string('forma_pagamento', 20);
            $table->string('tipo_pagamento', 2);
            $table->string('observacao');

            $table->integer('numero_nfe')->default(0);
            $table->enum('estado_emissao', ['novo', 'aprovado', 'rejeitado', 'cancelado']);
            $table->string('chave',48);
            $table->integer('sequencia_cce');

            $table->integer('pedido_ecommerce_id')->default(0);
            $table->integer('pedido_nuvemshop_id')->default(0);

            $table->string('bandeira_cartao', 2)->default('99');
            $table->string('cnpj_cartao', 18)->default('');
            $table->string('cAut_cartao', 20)->default('');
            $table->string('descricao_pag_outros', 80)->default('');

            $table->boolean('contigencia')->default(0);
            $table->boolean('reenvio_contigencia')->default(0);

            // alter table vendas add column contigencia boolean default null;
            // alter table vendas add column reenvio_contigencia boolean default null;

            // alter table vendas add column estado_emissao enum('novo', 'aprovado', 'rejeitado', 'cancelado');

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
        Schema::dropIfExists('vendas');
    }
}
