<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinanceiroRepresentantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financeiro_representantes', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('representante_empresa_id')->unsigned();
            $table->foreign('representante_empresa_id')->references('id')
            ->on('representante_empresas')->onDelete('cascade');

            $table->string('forma_pagamento', 30);
            $table->decimal('valor', 8, 2);

            $table->boolean('pagamento_comissao')->deafult(0);

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
        Schema::dropIfExists('financeiro_representantes');
    }
}
