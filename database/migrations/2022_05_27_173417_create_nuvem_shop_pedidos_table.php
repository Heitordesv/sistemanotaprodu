<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNuvemShopPedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nuvem_shop_pedidos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned()->nullable();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('pedido_id', 30);
            $table->string('rua', 80);
            $table->string('numero', 80);
            $table->string('bairro', 50);
            $table->string('cidade', 40);
            $table->string('cep', 10);

            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('desconto', 10, 2);

            $table->string('observacao', 150);

            $table->string('cliente_id', 30);
            $table->string('nome', 50);
            $table->string('email', 50);
            $table->string('documento', 20);

            $table->integer('numero_nfe')->default(0);
            $table->string('status_envio', 20);
            $table->string('gateway', 30);
            $table->string('status_pagamento', 30);
            $table->string('data', 30);

            $table->integer('venda_id')->default(0);

            // alter table nuvem_shop_pedidos add column venda_id integer default 0;

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
        Schema::dropIfExists('nuvem_shop_pedidos');
    }
}
