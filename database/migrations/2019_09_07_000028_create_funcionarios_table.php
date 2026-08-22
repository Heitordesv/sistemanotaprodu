<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFuncionariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->integer('usuario_id')->nullable()->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');

            $table->decimal('percentual_comissao', 6, 2)->default(0);
            $table->decimal('salario', 10, 2)->default(0);

            $table->string('nome', 100);
            $table->string('cpf', 15)->nullable();
            $table->string('rg', 15);
            $table->string('rua', 80);
            $table->string('numero', 10);
            $table->string('bairro', 50);

            $table->integer('cidade_id')->unsigned();
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('cascade');

            $table->string('telefone', 20);
            $table->string('celular', 20)->default("00 00000 0000");
            $table->string('email', 40)->default("null");
            $table->date('data_registro', 5)->nullable();
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
        Schema::dropIfExists('funcionarios');
    }
}
