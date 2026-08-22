<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedidaCtesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medida_ctes', function (Blueprint $table) {
            $table->increments('id');

            $table->string('cod_unidade', 2);
            $table->string('tipo_medida', 20);
            $table->decimal('quantidade_carga', 10, 4);

            $table->integer('cte_id')->unsigned();
            $table->foreign('cte_id')->references('id')
            ->on('ctes')->onDelete('cascade');

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
        Schema::dropIfExists('medida_ctes');
    }
}
