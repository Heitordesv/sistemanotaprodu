<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemPedidoComplementoDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_pedido_complemento_deliveries', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('item_pedido_id')->unsigned();
            $table->foreign('item_pedido_id')->references('id')
            ->on('item_pedido_deliveries')->onDelete('cascade');

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
        Schema::dropIfExists('item_pedido_complemento_deliveries');
    }
}
