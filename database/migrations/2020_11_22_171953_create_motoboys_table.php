<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMotoboysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('motoboys', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            
            $table->string('nome', 60);
            $table->string('telefone1', 15);
            $table->string('telefone2', 15);
            $table->string('telefone3', 15);
            $table->string('cpf', 15);
            $table->string('rg', 15);
            $table->string('endereco', 60);
            $table->string('tipo_transporte', 30);
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
        Schema::dropIfExists('motoboys');
    }
}
