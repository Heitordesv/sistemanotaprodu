<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdemServicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordem_servicos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')
            ->on('clientes')->onDelete('cascade');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')
            ->on('usuarios')->onDelete('cascade');

            $table->enum('estado', ['pendente', 'aprovado', 'reprovado', 'finalizado']);

            $table->string('descricao')->nullable();
            $table->string('forma_pagamento', 10)->nullable();
            $table->decimal('valor', 10,2)->default(0);
            $table->date('data_prevista_finalizacao')->nullable();

            $table->decimal('desconto', 10,2)->nullable();
            $table->decimal('acrescimo', 10,2)->nullable();
            $table->string('observacao', 100)->nullable();

            $table->integer('venda_id')->default(0);
            $table->integer('nfse_id')->default(0);
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
        Schema::dropIfExists('ordem_servicos');
    }
}
