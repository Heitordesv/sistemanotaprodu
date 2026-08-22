<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtividadeServicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atividade_servicos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('servico_id')->unsigned();
            $table->foreign('servico_id')->references('id')->on('servicos')
            ->onDelete('cascade');

            $table->integer('atividade_id')->unsigned();
            $table->foreign('atividade_id')->references('id')->on('atividade_eventos')
            ->onDelete('cascade');

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
        Schema::dropIfExists('atividade_servicos');
    }
}
