<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWsPedidosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ws_pedidos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->string('codigo_pedido');
            $table->dateTime('data');
            $table->string('data_chart')->nullable();
            $table->string('data_chart2')->nullable();
            $table->text('resumo_pedidos')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->decimal('valor_troco', 10, 2)->nullable();
            $table->string('opcao_delivery')->nullable();
            $table->decimal('valor_taxa', 10, 2)->nullable();
            $table->string('telefone_empresa')->nullable();
            $table->text('adicionais')->nullable();
            $table->decimal('sub_total', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->string('nome')->nullable();
            $table->string('telefone')->nullable();
            $table->string('rua')->nullable();
            $table->string('unidade')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('complemento')->nullable();
            $table->text('observacao')->nullable();
            $table->string('name_observacao_mesa')->nullable();
            $table->string('status')->nullable();
            $table->string('mes')->nullable();
            $table->string('ano')->nullable();
            $table->boolean('view')->default(false);
            $table->decimal('desconto', 10, 2)->nullable();
            $table->boolean('confirm_whatsapp')->default(false);
            $table->boolean('msg_delivery_false')->default(false);
            $table->tinyInteger('avaliacao')->nullable();
            $table->text('comentario')->nullable();
            $table->date('dataatualizada')->nullable();
            $table->time('hora')->nullable();
            $table->string('statucach')->nullable(); // (erro de digitação? deveria ser "statuscash"?)
            $table->boolean('inviomg')->default(false); // (erro de digitação? "enviomsg"?)
            $table->dateTime('dataenvio')->nullable();
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ws_pedidos');
    }
}
