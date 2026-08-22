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
        Schema::create('record_logs', function (Blueprint $table) {
            $table->increments('id');

            $table->enum('tipo', ['criar', 'atualizar', 'deletar', 'emissao', 'cancelamento']);

            $table->integer('usuario_log_id')->unsigned();
            $table->foreign('usuario_log_id')->references('id')->on('usuarios');

            $table->string('tabela', 40);
            $table->integer('registro_id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

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
        Schema::dropIfExists('record_logs');
    }
};
