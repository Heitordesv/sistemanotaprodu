<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnidadeCargasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unidade_cargas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('info_id')->unsigned();
            $table->foreign('info_id')->references('id')
            ->on('info_descargas')->onDelete('cascade');

            $table->string('id_unidade_carga', 20);
            $table->decimal('quantidade_rateio', 5, 2);

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
        Schema::dropIfExists('unidade_cargas');
    }
}
