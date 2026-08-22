<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnderecoEcommercesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('endereco_ecommerces', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')
            ->on('cliente_ecommerces')->onDelete('cascade');

            $table->string('rua', 60);
            $table->string('numero', 10);
            $table->string('bairro', 30);
            $table->string('cidade', 30);
            $table->string('uf', 2);
            $table->string('cep', 9);
            $table->string('complemento', 30);

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
        Schema::dropIfExists('endereco_ecommerces');
    }
}
