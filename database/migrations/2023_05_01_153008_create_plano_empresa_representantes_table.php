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
        Schema::create('plano_empresa_representantes', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
                ->on('empresas')->onDelete('cascade');

            $table->integer('plano_id')->unsigned();
            $table->foreign('plano_id')->references('id')
                ->on('planos')->onDelete('cascade');

            $table->integer('representante_id')->unsigned();
            $table->foreign('representante_id')->references('id')
                ->on('representantes')->onDelete('cascade');

            $table->date('expiracao');
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
        Schema::dropIfExists('plano_empresa_representantes');
    }
};
