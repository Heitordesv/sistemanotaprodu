<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemPedidoComplementoLocalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_pedido_complemento_locals', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('item_pedido')->unsigned();
            $table->foreign('item_pedido')->references('id')->on('item_pedidos')
            ->onDelete('cascade');

            $table->integer('complemento_id')->unsigned();
            $table->foreign('complemento_id')->references('id')
            ->on('complemento_deliveries')->onDelete('cascade');

            $table->integer('quantidade');

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
        Schema::dropIfExists('item_pedido_complemento_locals');
    }
}
