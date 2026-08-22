<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidoMotoboysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_motoboys', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('motoboy_id')->nullable()->unsigned();
            $table->foreign('motoboy_id')->references('id')->on('motoboys')->onDelete('cascade');

            $table->integer('pedido_id')->nullable()->unsigned();
            $table->foreign('pedido_id')->references('id')->on('pedido_deliveries')->onDelete('cascade');
            $table->decimal('valor', 7, 2);
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('pedido_motoboys');
    }
}
