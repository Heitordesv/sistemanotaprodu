<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListaPrecosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lista_precos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('nome', 40);
            $table->integer('tipo'); // 1 - valor de compra | 2 - valor de venda
            $table->integer('tipo_inc_red'); // 1 - incremento | 2 - reduçao
            $table->decimal('percentual_alteracao', 4, 2);
            $table->timestamps();

            // alter table lista_precos add column tipo integer default 1;
            // alter table lista_precos add column tipo_inc_red integer default 1;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lista_precos');
    }
}
