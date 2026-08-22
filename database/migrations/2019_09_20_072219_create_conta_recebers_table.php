<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContaRecebersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('venda_id')->nullable()->unsigned();
            $table->foreign('venda_id')->references('id')->on('vendas')
            ->onDelete('cascade');

            $table->integer('remessa_nfe_id')->nullable();

            $table->integer('cliente_id')->nullable()->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes')
            ->onDelete('cascade');
            
            $table->integer('categoria_id')->unsigned();
            $table->foreign('categoria_id')->references('id')->on('categoria_contas')
            ->onDelete('cascade');

            $table->string('referencia');
            $table->decimal('valor_integral', 16,7);
            $table->decimal('valor_recebido', 16,7)->default(0);
            $table->timestamp('date_register')->useCurrent();
            $table->date('data_vencimento');
            $table->date('data_recebimento');
            $table->boolean('status')->default(false);

            $table->decimal('juros', 16,7)->default(0);
            $table->decimal('multa', 16,7)->default(0);

            $table->integer('venda_caixa_id')->nullable()->unsigned();
            $table->foreign('venda_caixa_id')->references('id')
            ->on('venda_caixas')->onDelete('cascade');

            $table->string('observacao', 100)->deafault('');
            $table->string('tipo_pagamento', 20)->deafault('');

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');
            
            // alter table conta_recebers add column juros decimal(10, 4) default 0;
            // alter table conta_recebers add column multa decimal(10, 4) default 0;
            // alter table conta_recebers add column observacao varchar(100) default '';
            // alter table conta_recebers add column tipo_pagamento varchar(20) default '';
            // alter table conta_recebers add column remessa_nfe_id integer default null;

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
        Schema::dropIfExists('conta_recebers');
    }
}
