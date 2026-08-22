<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nfses', function (Blueprint $table) {
            $table->increments('id');


            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            // $table->integer('filial_id')->unsigned()->nullable();
            // $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->decimal('valor_total', 16, 7);

            $table->enum('estado_emissao', ['novo', 'rejeitado', 'aprovado', 'cancelado', 'processando']);
            $table->string('serie', 3);
            $table->string('codigo_verificacao', 20);
            $table->integer('numero_nfse');

            $table->string('url_xml', 255);
            $table->string('url_pdf_nfse', 255);
            $table->string('url_pdf_rps', 255);

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes')
            ->onDelete('cascade');

            $table->string('documento', 18);
            $table->string('razao_social', 60);
            $table->string('im', 20)->nullable();
            $table->string('ie', 20)->nullable();
            $table->string('cep', 9);
            $table->string('rua', 80);
            $table->string('numero', 20);
            $table->string('bairro', 40);
            $table->string('complemento', 80)->nullable();
            $table->integer('cidade_id')->unsigned();
            $table->foreign('cidade_id')->references('id')->on('cidades')
            ->onDelete('cascade');

            $table->string('email', 80)->nullable();
            $table->string('telefone', 20)->nullable();

            $table->string('natureza_operacao', 100)->nullable();
            $table->string('uuid', 100)->nullable();

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            // alter table nfses add column natureza_operacao varchar(100) default null;
            // alter table nfses add column uuid varchar(100) default null;
            // alter table nfses modify column estado enum('novo', 'rejeitado', 'aprovado', 'cancelado', 'processando');


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
        Schema::dropIfExists('nfses');
    }
};
