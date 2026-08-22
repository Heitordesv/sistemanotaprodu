<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigEcommercesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_ecommerces', function (Blueprint $table) {
            $table->increments('id');

            $table->string('nome', 30);
            $table->string('link', 30);
            $table->string('imagem', 80);
            $table->string('rua', 80);
            $table->string('numero', 10);
            $table->string('bairro', 30);
            $table->string('cidade_id', 30);
            $table->string('uf', 2);

            $table->string('cep', 10);
            $table->string('telefone', 15);
            $table->string('email', 60);
            $table->string('link_facebook', 120);
            $table->string('link_twiter', 120);
            $table->string('link_instagram', 120);
            $table->decimal('frete_gratis_valor', 10, 2);
            $table->string('mercadopago_public_key', 120);
            $table->string('mercadopago_access_token', 120);
            $table->string('funcionamento', 120);
            $table->string('latitude', 10);
            $table->string('longitude', 10);
            $table->text('politica_privacidade');
            $table->text('src_mapa');
            $table->string('cor_principal', 8);
            $table->string('tema_ecommerce', 30);
            $table->string('token', 30);

            $table->boolean('habilitar_retirada')->default(false);
            $table->decimal('desconto_padrao_boleto', 4, 2);
            $table->decimal('desconto_padrao_pix', 4, 2);
            $table->decimal('desconto_padrao_cartao', 4, 2);

            $table->string('google_api', 40)->default('');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->string('api_token', 25)->default('');
            $table->boolean('usar_api')->default(false);

            $table->string('cor_fundo', 7)->default('#000');
            $table->string('cor_btn', 7)->default('#000');
            $table->text('mensagem_agradecimento');

            $table->string('img_contato', 80)->default('');
            $table->string('fav_icon', 80)->default('');
            $table->integer('timer_carrossel')->default(5);
            $table->text('formas_pagamento');

            // alter table config_ecommerces add column habilitar_retirada boolean default 0;
            // alter table config_ecommerces add column desconto_padrao_boleto decimal(4,2) default 0;
            // alter table config_ecommerces add column desconto_padrao_pix decimal(4,2) default 0;
            // alter table config_ecommerces add column desconto_padrao_cartao decimal(4,2) default 0;
            // alter table config_ecommerces add column api_token varchar(25) default '';
            // alter table config_ecommerces add column usar_api boolean default 0;

            // alter table config_ecommerces add column mensagem_agradecimento text;
            // alter table config_ecommerces add column img_contato varchar(80) default '';
            // alter table config_ecommerces add column fav_icon varchar(80) default '';
            // alter table config_ecommerces add column timer_carrossel integer default 5;

            // alter table config_ecommerces add column cor_fundo varchar(7) default '#000';
            // alter table config_ecommerces add column cor_btn varchar(7) default '#000';
            // alter table config_ecommerces add column formas_pagamento text;
            // alter table config_ecommerces add column uf varchar(2) default '';

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
        Schema::dropIfExists('config_ecommerces');
    }
}
