<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContadorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contadors', function (Blueprint $table) {
            $table->increments('id');

            $table->string('razao_social', 100);
            $table->string('nome_fantasia', 80);
            $table->string('cnpj', 19);
            $table->string('ie', 20);
            $table->string('logradouro', 80);

            $table->string('numero', 10);
            $table->string('bairro', 50);
            $table->string('fone', 20);
            $table->string('cep', 10);
            $table->string('email', 80);

            $table->decimal('percentual_comissao', 5, 2);

            $table->integer('cidade_id');
            $table->boolean('cadastrado_por_cliente')->default(false);
            $table->boolean('contador_parceiro')->default(false);

            $table->string('dados_bancarios')->default(false);
            $table->string('agencia', 15)->default('');
            $table->string('conta', 15)->default('');
            $table->string('banco', 30)->default('');
            $table->string('chave_pix', 50)->default('');
            $table->integer('empresa_id')->nullable();


            // alter table contadors add column cidade_id integer default 0;
            // alter table contadors add column cadastrado_por_cliente boolean default false;

            // alter table contadors add column dados_bancarios boolean default false;
            // alter table contadors add column agencia varchar(15) default '';
            // alter table contadors add column conta varchar(15) default '';
            // alter table contadors add column banco varchar(30) default '';
            // alter table contadors add column chave_pix varchar(50) default '';

            // alter table contadors add column contador_parceiro boolean default false;
            // alter table contadors add column empresa_id integer default null;

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
        Schema::dropIfExists('contadors');
    }
}
