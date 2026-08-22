<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemPedidoDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_pedido_deliveries', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produto_deliveries')->onDelete('cascade');

            $table->integer('pedido_id')->unsigned();
            $table->foreign('pedido_id')->references('id')->on('pedido_deliveries')->onDelete('cascade');

            $table->boolean('status'); 
            
            $table->decimal('quantidade', 8,2);
            $table->string('observacao', 50);
            $table->decimal('valor', 14,2);

            $table->integer('tamanho_id')->nullable()->unsigned();
            $table->foreign('tamanho_id')->references('id')->on('tamanho_pizzas')->onDelete('cascade');

            // alter table item_pedido_deliveries add column valor decimal(14,2) default 0;
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
        Schema::dropIfExists('item_pedido_deliveries');
    }
}
