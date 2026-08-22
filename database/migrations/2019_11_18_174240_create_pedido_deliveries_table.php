<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidoDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_deliveries', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            
            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')->on('cliente_deliveries')->onDelete('cascade');
            $table->timestamp('data_registro')->useCurrent();

            $table->decimal('valor_total', 10,2);
            $table->decimal('troco_para', 10,2);

            $table->string('forma_pagamento', 20);
            $table->string('observacao', 50);

            $table->string('telefone', 15);
            
            $table->enum('estado', ['novo', 'aprovado', 'cancelado', 'finalizado']);
            // alter table pedido_deliveries MODIFY COLUMN estado enum('novo', 'aprovado', 'cancelado', 'finalizado');
            $table->string('motivoEstado', 50);

            $table->integer('endereco_id')->nullable()->unsigned();
            $table->foreign('endereco_id')->references('id')->on('endereco_deliveries')->onDelete('cascade');

            $table->integer('cupom_id')->nullable()->unsigned();
            $table->foreign('cupom_id')->references('id')->on('codigo_descontos')->onDelete('cascade');
            $table->decimal('desconto', 10,2);
            $table->decimal('valor_entrega', 10,2);

            $table->boolean('app');

            $table->text('qr_code_base64');
            $table->text('qr_code');
            $table->string('transacao_id', 50)->default('');
            $table->string('status_pagamento', 100)->default('');
            $table->boolean('pedido_lido')->default(0);

            $table->string('horario_cricao', 5);
            $table->string('horario_leitura', 5);
            $table->string('horario_entrega', 5);

            // alter table pedido_deliveries add column valor_entrega decimal(10,2) default 0;

            // alter table pedido_deliveries add column qr_code_base64 text;
            // alter table pedido_deliveries add column qr_code text;
            // alter table pedido_deliveries add column transacao_id varchar(50) default '';
            // alter table pedido_deliveries add column status_pagamento varchar(100) default '';
            // alter table pedido_deliveries add column pedido_lido boolean default 0;

            // alter table pedido_deliveries add column horario_cricao varchar(5) default '';
            // alter table pedido_deliveries add column horario_leitura varchar(5) default '';
            // alter table pedido_deliveries add column horario_entrega varchar(5) default '';


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
        Schema::dropIfExists('pedido_deliveries');
    }
}
