<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemCotacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_cotacaos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('cotacao_id')->unsigned();
            $table->foreign('cotacao_id')->references('id')->on('cotacaos')->onDelete('cascade');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos');

            $table->decimal('valor', 10,2);
            $table->decimal('quantidade', 8,2);

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
        Schema::dropIfExists('item_cotacaos');
    }
}
