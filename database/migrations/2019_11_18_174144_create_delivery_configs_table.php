<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_configs', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('cidade_id')->unsigned()->nullable();
            $table->foreign('cidade_id')->references('id')->on('cidade_deliveries')
            ->onDelete('cascade');

            $table->string('link_face');
            $table->string('link_twiteer');
            $table->string('link_google');
            $table->string('link_instagram');
            $table->string('telefone', 20);

            $table->string('rua', 80);
            $table->string('numero', 15);
            $table->string('bairro', 30);
            $table->string('cep', 9);

            // alter table delivery_configs drop column endereco;
            // alter table delivery_configs add column rua varchar(80) default '';
            // alter table delivery_configs add column numero varchar(15) default '';
            // alter table delivery_configs add column bairro varchar(30) default '';
            // alter table delivery_configs add column cep varchar(9) default '';

            $table->string('tempo_medio_entrega', 10);
            $table->string('tempo_maximo_cancelamento', 10);
            $table->decimal('valor_entrega', 10, 2);
            $table->string('nome', 30);
            $table->string('descricao', 200);
            $table->string('latitude', 15);
            $table->string('longitude', 15);
            $table->string('politica_privacidade', 255);
            $table->decimal('valor_km', 10, 2);
            $table->integer('valor_entrega_gratis');
            $table->integer('maximo_km_entrega');
            $table->string('tipo_entrega', 20);
            $table->boolean('usar_bairros');
            $table->boolean('status')->default(0);

            $table->string('mercadopago_public_key', 120);
            $table->string('mercadopago_access_token', 120);

            $table->integer('maximo_adicionais');
            $table->integer('maximo_adicionais_pizza');
            $table->integer('tipo_divisao_pizza');
            $table->integer('maximo_sabores_pizza');

            $table->string('logo', 30);
            $table->string('one_signal_app_id', 50);
            $table->string('one_signal_key', 50);
            $table->string('tipos_pagamento', 255)->default('[]');

            $table->decimal('pedido_minimo', 10, 2);
            $table->decimal('avaliacao_media', 10, 2);
            $table->string('api_token', 50)->nullable();
            $table->boolean('notificacao_novo_pedido')->default(1);

            $table->boolean('autenticacao_sms')->default(0);
            $table->boolean('confirmacao_pedido_cliente')->default(0);
            // alter table delivery_configs add column tipos_pagamento varchar(255) default '[]';
            // alter table delivery_configs add column logo varchar(25);
            // alter table delivery_configs add column one_signal_app_id varchar(50);
            // alter table delivery_configs add column one_signal_key varchar(50);
            // alter table delivery_configs add column status boolean default false;
            // alter table delivery_configs add column tipo_divisao_pizza integer default 1;
            // alter table delivery_configs add column tipo_entrega varchar(20) default '';

            // alter table delivery_configs add column pedido_minimo decimal(10, 2) default 0;

            // alter table delivery_configs add column mercadopago_public_key varchar(120) default '';
            // alter table delivery_configs add column mercadopago_access_token varchar(120) default '';

            // alter table delivery_configs add column avaliacao_media decimal(10, 2) default 0;
            // alter table delivery_configs add column maximo_sabores_pizza integer default 0;
            // alter table delivery_configs add column notificacao_novo_pedido boolean default true;
            // alter table delivery_configs add column autenticacao_sms boolean default false;
            // alter table delivery_configs add column confirmacao_pedido_cliente boolean default false;
            
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
        Schema::dropIfExists('delivery_configs');
    }
}
