<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcessorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acessors', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('razao_social', 100);

            $table->string('cpf_cnpj', 19)->default("000.000.000-00");
            $table->string('rua', 80);
            $table->string('ie_rg', 20);
            $table->string('numero', 10);
            $table->string('bairro', 50);
            $table->string('telefone', 20);
            $table->string('celular', 20)->default("00 00000 0000");
            $table->string('email', 40)->default("null");
            $table->string('cep', 10)->default("null");

            $table->decimal('percentual_comissao', 6, 2)->default(0);
            $table->date('data_registro', 5);

            $table->integer('funcionario_id');
            $table->boolean('ativo')->default(true);

            $table->integer('cidade_id')->unsigned();
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('cascade');

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
        Schema::dropIfExists('acessors');
    }
}
