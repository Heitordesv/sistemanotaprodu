<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEscritorioContabilsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('escritorio_contabils', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('razao_social', 100);
            $table->string('nome_fantasia', 80);
            $table->string('cnpj', 19);
            $table->string('ie', 20);
            $table->string('logradouro', 80);

            $table->string('numero', 10);
            $table->string('bairro', 50);
            $table->string('fone', 20);
            $table->string('cep', 10);
            $table->string('email', 80);

            $table->string('token_sieg', 255);
            $table->boolean('envio_automatico_xml_contador')->default(0);
            $table->integer('cidade_id');

            // alter table escritorio_contabils add column cidade_id integer default 0;

            // alter table escritorio_contabils add column token_sieg varchar(255) default '';
            // alter table escritorio_contabils add column envio_automatico_xml_contador boolean default false;
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
        Schema::dropIfExists('escritorio_contabils');
    }
}
