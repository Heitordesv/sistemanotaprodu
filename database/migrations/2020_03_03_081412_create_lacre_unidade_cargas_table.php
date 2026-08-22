<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLacreUnidadeCargasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lacre_unidade_cargas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('info_id')->unsigned();
            $table->foreign('info_id')->references('id')
            ->on('info_descargas')->onDelete('cascade');

            $table->string('numero', 20);

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
        Schema::dropIfExists('lacre_unidade_cargas');
    }
}
