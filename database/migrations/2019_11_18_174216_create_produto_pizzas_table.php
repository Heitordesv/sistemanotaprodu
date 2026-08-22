<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProdutoPizzasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produto_pizzas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('produto_id')->nullable()->unsigned();
            $table->foreign('produto_id')->references('id')->on('produto_deliveries')->onDelete('cascade');

            $table->integer('tamanho_id')->nullable()->unsigned();
            $table->foreign('tamanho_id')->references('id')->on('tamanho_pizzas')->onDelete('cascade');

            $table->decimal('valor', 10,2);

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
        Schema::dropIfExists('produto_pizzas');
    }
}
