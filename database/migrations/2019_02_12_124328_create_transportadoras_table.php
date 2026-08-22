<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportadorasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transportadoras', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('razao_social', 100);
            $table->string('cnpj_cpf', 19)->default("000.000.000-00");
            // $table->string('ie', 20);
            $table->string('logradouro', 80);

            $table->integer('cidade_id')->unsigned();
            $table->foreign('cidade_id')->references('id')
            ->on('cidades')->onDelete('cascade');

            $table->string('email', 40)->default("");
            $table->string('telefone', 20)->default("");

            $table->timestamps();

            // alter table transportadoras add column email varchar(40) default "";
            // alter table transportadoras add column telefone varchar(20) default "";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transportadoras');
    }
}
