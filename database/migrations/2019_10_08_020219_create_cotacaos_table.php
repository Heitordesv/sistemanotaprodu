<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCotacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotacaos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->integer('fornecedor_id')->unsigned();
            $table->foreign('fornecedor_id')->references('id')->on('fornecedors');

            $table->string('forma_pagamento', 50);
            $table->string('responsavel', 50);
            $table->string('referencia', 20);
            $table->string('link', 20);
            $table->string('observacao', 100);
            $table->boolean('resposta');
            $table->boolean('ativa');
            $table->decimal('valor', 10,2);
            $table->decimal('desconto', 10,2);
            $table->boolean('escolhida');
            $table->timestamp('data_registro')->useCurrent();

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
        Schema::dropIfExists('cotacaos');
    }
}
