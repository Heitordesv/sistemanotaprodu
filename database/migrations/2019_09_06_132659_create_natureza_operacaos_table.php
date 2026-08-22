<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNaturezaOperacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('natureza_operacaos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->string('natureza', 80);
            $table->string('CFOP_entrada_estadual', 5)->default("");
            $table->string('CFOP_entrada_inter_estadual', 5)->default("");
            $table->string('CFOP_saida_estadual', 5)->default("");
            $table->string('CFOP_saida_inter_estadual', 5)->default("");

            $table->boolean('sobrescreve_cfop')->default(0);
            $table->integer('finNFe')->default(1);
            $table->boolean('nao_movimenta_estoque')->default(0);

            // alter table natureza_operacaos add column nao_movimenta_estoque boolean default 0;
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
        Schema::dropIfExists('natureza_operacaos');
    }
}
