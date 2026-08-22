<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValePedagiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vale_pedagios', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('mdfe_id')->unsigned();
            $table->foreign('mdfe_id')->references('id')
            ->on('mdves')->onDelete('cascade');

            $table->string('cnpj_fornecedor', 18);
            $table->string('cnpj_fornecedor_pagador', 18);
            $table->string('numero_compra', 18);
            $table->decimal('valor', 10, 2);
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
        Schema::dropIfExists('vale_pedagios');
    }
}
