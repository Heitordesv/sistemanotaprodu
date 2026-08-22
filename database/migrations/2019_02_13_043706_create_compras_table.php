<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComprasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->integer('fornecedor_id')->unsigned();
            $table->foreign('fornecedor_id')->references('id')->on('fornecedors')
            ->onDelete('cascade');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios')
            ->onDelete('cascade');

            $table->string('observacao')->nullable();
            $table->string('chave', 44)->nullable();
            $table->string('numero_nfe', 20)->nullable();
            $table->integer('numero_emissao')->nullable();
            $table->enum('estado', ['novo', 'aprovado', 'rejeitado', 'cancelado']);
            
            $table->decimal('total', 16,7);
            $table->decimal('desconto', 10,2);
            $table->integer('sequencia_cce')->default(0);

            $table->string('placa', 9)->nullable();
            $table->string('uf', 2)->nullable();
            $table->decimal('valor_frete', 10, 2)->default(0);
            $table->integer('tipo')->default(0);
            $table->integer('qtd_volumes')->default(0);
            $table->string('numeracao_volumes', 20)->nullable();
            $table->string('especie', 20)->nullable();
            $table->decimal('peso_liquido', 8, 3)->default(0);
            $table->decimal('peso_bruto', 8, 3)->default(0);

            $table->string('forma_pagamento', 20)->nullable();

            $table->integer('transportadora_id')->nullable()->unsigned();
            $table->foreign('transportadora_id')->references('id')->on('transportadoras')
            ->onDelete('cascade');

            $table->string('tipo_pagamento', 2)->default('');
            $table->integer('natureza_id')->default(0);

            $table->timestamp('data_emissao')->nullable();
            
            // alter table compras add column total decimal(16, 7) default 0;

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
        Schema::dropIfExists('compras');
    }
}
