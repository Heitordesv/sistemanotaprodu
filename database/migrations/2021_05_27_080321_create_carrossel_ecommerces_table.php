<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarrosselEcommercesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carrossel_ecommerces', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->string('titulo', 30);
            $table->string('cor_titulo', 7)->default('#000');

            $table->string('descricao', 200);
            $table->string('cor_descricao', 7)->default('#000');

            $table->string('link_acao', 200);
            $table->string('nome_botao', 20);
            $table->string('img', 40);

            // alter table carrossel_ecommerces add column cor_titulo varchar(7) default '#000';
            // alter table carrossel_ecommerces add column cor_descricao varchar(7) default '#000';
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
        Schema::dropIfExists('carrossel_ecommerces');
    }
}
