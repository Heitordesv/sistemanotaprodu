<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompraReferenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compra_referencias', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('compra_id')->unsigned();
            $table->foreign('compra_id')->references('id')->on('compras')
            ->onDelete('cascade');

            $table->string('chave', 44);
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
        Schema::dropIfExists('compra_referencias');
    }
}
