<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevolucaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devolucaos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('fornecedor_id')->unsigned();
            $table->foreign('fornecedor_id')->references('id')->on('fornecedors');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->integer('natureza_id')->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->integer('transportadora_id')->nullable()->unsigned();
            $table->foreign('transportadora_id')->references('id')->on('transportadoras');

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->timestamp('data_registro')->useCurrent();
            $table->decimal('valor_integral', 10,2);
            $table->decimal('valor_devolvido', 10,2);

            $table->string('motivo', 100);
            $table->string('observacao', 150);
            
            $table->enum('estado_emissao', ['novo', 'aprovado', 'rejeitado', 'cancelado']);
            // alter table devolucaos modify column estado_emissao enum('novo', 'aprovado', 'rejeitado', 'cancelado') default 'novo';
            
            $table->boolean('devolucao_parcial');

            $table->string('chave_nf_entrada',48);
            $table->integer('nNf');
            $table->decimal('vFrete', 10, 2);
            $table->decimal('vDesc', 10, 2);

            $table->string('chave_gerada', 44);
            $table->integer('numero_gerado');
            $table->integer('tipo');
            $table->integer('sequencia_cce')->default(0);

            $table->string('transportadora_nome', 100)->default('');
            $table->string('transportadora_cidade', 50)->default('');
            $table->string('transportadora_uf', 2)->default('');
            $table->string('transportadora_cpf_cnpj', 18)->default('');
            $table->string('transportadora_ie', 15)->default('');
            $table->string('transportadora_endereco', 120)->default('');

            $table->decimal('frete_quantidade', 6, 2)->default(0);
            $table->string('frete_especie', 20)->default('');
            $table->string('frete_marca', 20)->default('');
            $table->string('frete_numero', 20)->default('');
            $table->integer('frete_tipo')->default(0);

            $table->string('veiculo_placa', 10)->default('');
            $table->string('veiculo_uf', 2)->default('');

            $table->decimal('frete_peso_bruto', 10, 3)->default(0);
            $table->decimal('frete_peso_liquido', 10, 3)->default(0);

            $table->decimal('despesa_acessorias', 10, 2)->default(0);

            // alter table devolucaos add column transportadora_nome varchar(100) default "";
            // alter table devolucaos add column transportadora_cidade varchar(50) default "";
            // alter table devolucaos add column transportadora_uf varchar(2) default "";
            // alter table devolucaos add column transportadora_cpf_cnpj varchar(18) default "";
            // alter table devolucaos add column transportadora_ie varchar(15) default "";
            // alter table devolucaos add column transportadora_endereco varchar(120) default "";

            // alter table devolucaos add column frete_quantidade decimal(6,2) default 0;
            // alter table devolucaos add column frete_especie varchar(20) default "";
            // alter table devolucaos add column frete_marca varchar(20) default "";
            // alter table devolucaos add column frete_numero varchar(20) default "";
            // alter table devolucaos add column frete_tipo integer default 0;

            // alter table devolucaos add column veiculo_placa varchar(10) default "";
            // alter table devolucaos add column veiculo_uf varchar(2) default "";

            // alter table devolucaos add column frete_peso_bruto decimal(10,3) default 0;
            // alter table devolucaos add column frete_peso_liquido decimal(10,3) default 0;
            // alter table devolucaos add column despesa_acessorias decimal(10,2) default 0;

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
        Schema::dropIfExists('devolucaos');
    }
}
