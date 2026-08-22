<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendaCaixasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('cliente_id')->nullable()->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->integer('natureza_id')->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->timestamp('data_registro')->useCurrent();
            $table->decimal('valor_total', 16,7);
            $table->decimal('dinheiro_recebido', 10,2);
            $table->decimal('troco', 10,2);
            $table->decimal('desconto', 10,2);
            $table->decimal('acrescimo', 10,2);

            $table->string('forma_pagamento', 20);
            $table->string('tipo_pagamento', 2);
            
            $table->enum('estado_emissao', ['novo', 'aprovado', 'rejeitado', 'cancelado']);
            $table->integer('numero_nfce')->nullable();
            $table->string('chave', 48)->nullable();

            $table->string('nome', 50)->nullable();
            $table->string('cpf', 18)->nullable();
            $table->string('observacao', 150)->nullable();
            $table->integer('pedido_delivery_id')->nullable();

            $table->text('qr_code_base64')->nullable();

            $table->string('bandeira_cartao', 2)->default('99');
            $table->string('cnpj_cartao', 18)->nullable();
            $table->string('cAut_cartao', 20)->nullable();
            $table->string('descricao_pag_outros', 80)->nullable();

            $table->boolean('rascunho')->default(0);
            $table->boolean('consignado')->default(0);
            $table->boolean('pdv_java')->default(0);
            $table->boolean('retorno_estoque')->default(0);

            $table->boolean('contigencia')->default(0);
            $table->boolean('reenvio_contigencia')->default(0);
            
            // alter table venda_caixas add column contigencia boolean default null;
            // alter table venda_caixas add column reenvio_contigencia boolean default null;
            // alter table venda_caixas add column estado_emissao enum('novo', 'aprovado', 'rejeitado', 'cancelado');
            
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
        Schema::dropIfExists('venda_caixas');
    }
}
