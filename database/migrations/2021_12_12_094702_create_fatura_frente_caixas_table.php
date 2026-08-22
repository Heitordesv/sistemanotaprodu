<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaturaFrenteCaixasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fatura_frente_caixas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('venda_caixa_id')->unsigned();
            $table->foreign('venda_caixa_id')->references('id')->on('venda_caixas')
            ->onDelete('cascade');

            $table->decimal('valor', 16, 7);
            $table->string('forma_pagamento', 20);
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
        Schema::dropIfExists('fatura_frente_caixas');
    }
}
