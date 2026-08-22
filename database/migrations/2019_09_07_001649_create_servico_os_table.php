<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicoOsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servico_os', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('servico_id')->unsigned();
            $table->foreign('servico_id')->references('id')->on('servicos');

            $table->integer('ordem_servico_id')->unsigned();
            $table->foreign('ordem_servico_id')->references('id')->on('ordem_servicos');

            $table->integer('quantidade');
            $table->decimal('valor_unitario', 16,7);
            $table->decimal('sub_total', 16,7);
            $table->boolean('status')->default(false);
            
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
        Schema::dropIfExists('servico_os');
    }
}
