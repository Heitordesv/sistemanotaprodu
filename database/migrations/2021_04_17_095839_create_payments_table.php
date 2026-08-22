<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->integer('plano_id')->unsigned();
            $table->foreign('plano_id')->references('id')->on('plano_empresas')->onDelete('cascade');

            $table->decimal('valor', 10, 2);
            $table->string('transacao_id', 100);
            $table->string('forma_pagamento', 10);

            $table->string('status', 15);
            $table->string('status_detalhe', 100);
            $table->string('descricao', 200);

            $table->text('link_boleto');
            $table->text('qr_code_base64');
            $table->text('qr_code');

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
        Schema::dropIfExists('payments');
    }
}
