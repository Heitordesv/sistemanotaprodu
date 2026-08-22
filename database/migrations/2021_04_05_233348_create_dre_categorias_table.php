<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDreCategoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dre_categorias', function (Blueprint $table) {
            $table->increments('id');

            $table->string('nome', 100);

            $table->integer('dre_id')->unsigned();
            $table->foreign('dre_id')->references('id')->on('dres')->onDelete('cascade');

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
        Schema::dropIfExists('dre_categorias');
    }
}
