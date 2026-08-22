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
        Schema::create('fatura_pre_vendas', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('pre_venda_id')->unsigned();
            $table->foreign('pre_venda_id')->references('id')->on('venda_caixa_pre_vendas')
            ->onDelete('cascade');

            $table->decimal('valor', 16, 7);
            $table->string('forma_pagamento', 20);
            $table->date('vencimento');

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
        Schema::dropIfExists('fatura_pre_vendas');
    }
};
