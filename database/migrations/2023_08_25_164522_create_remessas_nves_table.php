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
        Schema::create('remessa_nves', function (Blueprint $table) {
            $table->id();

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->integer('natureza_id')->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->integer('transportadora_id')->nullable()->unsigned();
            $table->foreign('transportadora_id')->references('id')->on('transportadoras')
            ->onDelete('cascade');

            $table->integer('numero_sequencial')->deafult(0);

            $table->date('data_entrega')->nullable();
            $table->decimal('valor_total', 16,7);
            $table->decimal('desconto', 10,2);
            $table->decimal('acrescimo', 10,2);

            $table->string('forma_pagamento', 2);
            $table->string('observacao');
            $table->enum('estado_emissao', ['novo', 'rejeitado', 'cancelado', 'aprovado']);
            $table->integer('sequencia_cce');
            $table->integer('nSerie')->default(0);
            $table->integer('numero_nfe')->default(0);
            $table->string('chave',48);

            $table->string('descricao_pag_outros', 80)->nullable();
            $table->timestamp('data_emissao')->nullable();

            $table->boolean('baixa_estoque');
            $table->boolean('gerar_conta_receber');
            $table->enum('tipo_nfe', ['normal', 'remessa', 'estorno', 'entrada']);

            $table->string('placa', 9)->nullable();
            $table->string('uf', 2)->nullable();
            $table->decimal('valor_frete', 10, 2)->nullable();
            $table->integer('tipo_frete')->nullable();
            $table->integer('qtd_volumes')->nullable();
            $table->string('numeracao_volumes', 20)->nullable();
            $table->string('especie', 20)->nullable();
            $table->decimal('peso_liquido', 8, 3)->nullable();
            $table->decimal('peso_bruto', 8, 3)->nullable();
            $table->date('data_retroativa')->nullable();
            $table->integer('venda_caixa_id')->nullable();
            
            // alter table remessa_nves add column venda_caixa_id integer default null;
            // alter table remessa_nves modify column tipo_nfe enum('normal', 'remessa', 'estorno', 'entrada');

            // alter table remessa_nves MODIFY COLUMN tipo_nfe enum('normal', 'remessa', 'estorno', 'entrada');

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
        Schema::dropIfExists('remessa_nves');
    }
};
