<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigCaixasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_caixas', function (Blueprint $table) {
            $table->increments('id');

            $table->string('finalizar', 15);
            $table->string('reiniciar', 15);
            $table->string('editar_desconto', 15);
            $table->string('editar_acrescimo', 15);
            $table->string('editar_observacao', 15);
            $table->string('setar_valor_recebido', 15);

            $table->string('forma_pagamento_dinheiro', 15);
            $table->string('forma_pagamento_debito', 15);
            $table->string('forma_pagamento_credito', 15);
            $table->string('forma_pagamento_pix', 15);
            $table->string('setar_leitor', 15);
            $table->string('setar_quantidade', 15);

            $table->string('finalizar_fiscal', 15);
            $table->string('finalizar_nao_fiscal', 15);

            $table->boolean('valor_recebido_automatico');
            $table->boolean('balanca_valor_peso'); // 1 = valor, 0 = quantidade
            $table->integer('balanca_digito_verificador');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->string('mercadopago_public_key', 120);
            $table->string('mercadopago_access_token', 120);
            $table->integer('impressora_modelo')->default(80);

            $table->integer('modelo_pdv')->default(0);

            $table->string('tipos_pagamento', 255)->default('[]');
            $table->string('pagamento_padrao', 15)->default('');


            // alter table config_caixas add column mercadopago_public_key varchar(120) default '';
            // alter table config_caixas add column setar_quantidade varchar(15) default '';
            // alter table config_caixas add column finalizar_fiscal varchar(15) default '';
            // alter table config_caixas add column finalizar_nao_fiscal varchar(15) default '';
            // alter table config_caixas add column mercadopago_access_token varchar(120) default '';
            // alter table config_caixas add column modelo_pdv integer default 0;
            // alter table config_caixas add column impressora_modelo integer default 80;

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
        Schema::dropIfExists('config_caixas');
    }
}
