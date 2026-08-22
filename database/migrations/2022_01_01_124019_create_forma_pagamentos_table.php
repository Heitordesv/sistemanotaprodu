<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormaPagamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forma_pagamentos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned()->nullable();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('nome', 40);
            $table->string('chave', 30);
            $table->decimal('taxa', 10, 2)->default(0);
            $table->string('tipo_taxa', 5)->default('perc');
            $table->integer('prazo_dias');
            $table->boolean('status');

            $table->string('infos', 100)->default('');

            // alter table forma_pagamentos add column infos varchar(100) default '';

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
        Schema::dropIfExists('forma_pagamentos');
    }
}
