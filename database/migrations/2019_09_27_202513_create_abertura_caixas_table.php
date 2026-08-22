<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAberturaCaixasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abertura_caixas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->timestamp('data_registro')->useCurrent();
            $table->decimal('valor', 10,2);
            $table->integer('ultima_venda_nfe')->default(0);
            $table->integer('primeira_venda_nfe')->default(0);
            $table->integer('ultima_venda_nfce')->default(0);
            $table->integer('primeira_venda_nfce')->default(0);
            $table->boolean('status')->default(0);
            $table->decimal('valor_dinheiro_caixa', 10,2)->default(0);

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');
            
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
        Schema::dropIfExists('abertura_caixas');
    }
}
