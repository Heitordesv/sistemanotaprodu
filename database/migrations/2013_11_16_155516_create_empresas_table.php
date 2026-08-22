<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('razao_social', 80);
            $table->string('nome_fantasia', 80);
            $table->string('rua', 50);
            $table->string('telefone', 15);
            $table->string('email', 50);
            $table->string('numero', 10);
            $table->string('bairro', 30);
           
            $table->integer('cidade_id')->unsigned();
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('cascade');
            $table->string('cpf_cnpj', 18);
            $table->string('hash', 30)->default('');
            $table->text('permissao');
            $table->boolean('status')->default(1);

            $table->boolean('tipo_representante')->default(0);
            $table->boolean('tipo_contador')->default(0);
            $table->integer('perfil_id')->default(0);
            $table->string('mensagem_bloqueio', 255)->default('');
            $table->string('info_contador', 255)->default('');

            $table->integer('contador_id')->default(0);

            $table->string('cep', 9)->nullable();
            $table->string('representante_legal', 100)->nullable();
            $table->string('cpf_representante_legal', 15)->nullable();

            // alter table empresas add column tipo_representante boolean default 0;
            // alter table empresas add column perfil_id integer default 0;
            // alter table empresas add column mensagem_bloqueio varchar(255) default '';
            // alter table empresas add column info_contador varchar(255) default '';
            // alter table empresas add column nome_fantasia varchar(100) default '';

            // alter table empresas add column contador_id integer default 0;
            // alter table empresas add column hash varchar(30) default '';

            // alter table empresas add column razao_social varchar(80) default '';
            // alter table empresas add column cpf_cnpj varchar(30) default '';


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
        Schema::dropIfExists('empresas');
    }
}
