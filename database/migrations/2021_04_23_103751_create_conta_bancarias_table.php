<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContaBancariasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conta_bancarias', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            
            $table->string('banco', 30);
            $table->string('agencia', 10);
            $table->string('conta', 15);
            $table->string('titular', 45);

            $table->boolean('padrao')->default(0);
            $table->boolean('usar_logo')->default(0);

            $table->string('cnpj', 18);
            $table->string('endereco', 50);
            $table->string('cep', 9);
            $table->string('bairro', 30);
            $table->integer('cidade_id')->unsigned();
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('cascade');

            $table->string('carteira', 10)->default('');
            $table->string('convenio', 20)->default('');
            $table->decimal('juros', 10, 2)->default(0);
            $table->decimal('multa', 10, 2)->default(0);
            $table->integer('juros_apos')->default(0);
            $table->string('tipo', 7); // Cnab400 ou Cnab240
            
            // alter table conta_bancarias add column usar_logo boolean default false;
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
        Schema::dropIfExists('conta_bancarias');
    }
}
