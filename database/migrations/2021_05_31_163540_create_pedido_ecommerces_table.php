<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidoEcommercesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_ecommerces', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');
            
            $table->integer('cliente_id')->nullable()->unsigned();
            $table->foreign('cliente_id')->references('id')
            ->on('cliente_ecommerces')->onDelete('cascade');

            $table->integer('endereco_id')->nullable()->unsigned();
            $table->foreign('endereco_id')->references('id')
            ->on('endereco_ecommerces')->onDelete('cascade');

            $table->integer('status');

            $table->integer('status_preparacao')->deafult(0);
            $table->string('codigo_rastreio', 20)->default('');

            // 0 - Novo
            // 1 - Aprovado
            // 2 - Cancelado
            // 3 - Aguardando Envio
            // 4 - Enviado
            // 5 - Entregue

            $table->decimal('valor_total', 10, 2);
            $table->decimal('valor_frete', 10, 2);
            $table->string('tipo_frete', 10);

            $table->integer('venda_id')->default(0);
            $table->integer('numero_nfe')->default(0);

            $table->string('observacao', 100);
            $table->string('rand_pedido', 20);
            $table->string('token', 20)->default('');

            $table->text('link_boleto');
            $table->text('qr_code_base64');
            $table->text('qr_code');

            $table->string('transacao_id', 100)->default('');
            $table->string('forma_pagamento', 10)->default('');

            $table->string('status_pagamento', 15)->default('');
            $table->string('status_detalhe', 100)->default('');
            $table->string('hash', 20)->default('');
            $table->string('cupom_desconto', 6)->nullable();
            $table->decimal('desconto', 10, 2);

            // alter table pedido_ecommerces add column token varchar(20) default '';
            // alter table pedido_ecommerces add column cupom_desconto varchar(6) default null;
            // alter table pedido_ecommerces add column desconto decimal(10,2) default 0;
            
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
        Schema::dropIfExists('pedido_ecommerces');
    }
}
