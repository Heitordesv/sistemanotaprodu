<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFretesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fretes', function (Blueprint $table) {
            $table->increments('id');

            $table->string('placa', 9);
            $table->string('uf', 2);
            $table->decimal('valor', 10, 2);
            $table->integer('tipo');
            $table->integer('qtdVolumes');
            $table->string('numeracaoVolumes', 20);
            $table->string('especie', 20);
            $table->decimal('peso_liquido',8, 3);
            $table->decimal('peso_bruto',8, 3);

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
        Schema::dropIfExists('fretes');
    }
}
