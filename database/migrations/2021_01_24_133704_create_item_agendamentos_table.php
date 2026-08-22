<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemAgendamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_agendamentos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('servico_id')->unsigned();
            $table->foreign('servico_id')->references('id')->on('servicos');

            $table->integer('agendamento_id')->unsigned();
            $table->foreign('agendamento_id')->references('id')->on('agendamentos');

            $table->decimal('quantidade', 10, 2);
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
        Schema::dropIfExists('item_agendamentos');
    }
}
