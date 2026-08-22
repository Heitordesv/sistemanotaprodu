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
        Schema::create('troca_venda_caixas', function (Blueprint $table) {
            $table->id();

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('antiga_venda_caixas_id')->unsigned();
            $table->foreign('antiga_venda_caixas_id')->references('id')->on('venda_caixas')
            ->onDelete('cascade');

            $table->integer('nova_venda_caixas_id')->unsigned();
            $table->foreign('nova_venda_caixas_id')->references('id')->on('venda_caixas')
            ->onDelete('cascade');

            $table->text('prod_removidos');
            $table->text('prod_adicionados');
            $table->text('observacao');
            
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
        Schema::dropIfExists('troca_venda_caixas');
    }
};
