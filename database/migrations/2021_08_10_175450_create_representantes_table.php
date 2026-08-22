<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepresentantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('representantes', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('nome', 80);
            $table->string('rua', 50);
            $table->string('telefone', 15);
            $table->string('email', 50);
            $table->string('numero', 10);
            $table->string('bairro', 30);

            $table->integer('cidade_id')->unsigned();
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('cascade');
            
            $table->string('cpf_cnpj', 18);

            $table->boolean('status')->default(1);
            $table->decimal('comissao', 5, 2)->default(0);

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')
            ->on('usuarios')->onDelete('cascade');

            $table->boolean('acesso_xml')->default(0);
            $table->integer('limite_cadastros')->default(1);

            // alter table representantes add column acesso_xml boolean default 0;
            // alter table representantes add column limite_cadastros integer default 1;


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
        Schema::dropIfExists('representantes');
    }
}
