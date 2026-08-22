<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFornecedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fornecedors', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('razao_social', 100);
            $table->string('nome_fantasia', 80);
            $table->string('cpf_cnpj', 19);
            $table->string('ie_rg', 20);
            $table->string('rua', 80);
            $table->string('numero', 10);
            $table->string('bairro', 50);
            $table->string('telefone', 20);
            $table->string('celular', 20)->nullable();
            $table->string('email', 40)->nullable();
            $table->string('cep', 10)->nullable();

            $table->integer('cidade_id')->unsigned();
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('cascade');
            $table->integer('contribuinte')->default(1);

            // alter table fornecedors add column contribuinte integer default 1;

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
        Schema::dropIfExists('fornecedors');
    }
}
