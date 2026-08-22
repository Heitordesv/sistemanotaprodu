<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokenClienteDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token_cliente_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 200);
            $table->string('user_id', 40);
            $table->integer('cliente_id')->nullable()->unsigned();
            $table->foreign('cliente_id')->references('id')
            ->on('cliente_deliveries')->onDelete('cascade');
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
        Schema::dropIfExists('token_cliente_deliveries');
    }
}
