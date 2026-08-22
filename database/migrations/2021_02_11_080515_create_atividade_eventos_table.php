<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtividadeEventosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atividade_eventos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('responsavel_nome', 50);
            $table->string('responsavel_telefone', 15);
            $table->string('crianca_nome', 50);
            $table->time('inicio');
            $table->time('fim');
            $table->decimal('total', 10, 2);
            $table->boolean('status')->default(false);

            $table->integer('evento_id')->unsigned();
            $table->foreign('evento_id')->references('id')->on('eventos')->onDelete('cascade');

            $table->integer('funcionario_id')->unsigned();
            $table->foreign('funcionario_id')->references('id')->on('funcionarios')
            ->onDelete('cascade');

            $table->string('forma_pagamento', 25);

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
        Schema::dropIfExists('atividade_eventos');
    }
}
