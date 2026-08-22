<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('planos', function (Blueprint $table) {
            
            $table->increments('id');
            $table->string('nome', 40);
            $table->decimal('valor', 6,2);
            $table->integer('maximo_clientes');
            $table->integer('maximo_produtos');
            $table->integer('maximo_fornecedores');
            $table->integer('maximo_nfes');
            $table->integer('maximo_nfces');
            $table->integer('maximo_cte');
            $table->integer('maximo_mdfe');
            $table->integer('maximo_evento');
            $table->integer('maximo_usuario');
            $table->integer('armazenamento');
            $table->integer('maximo_usuario_simultaneo');
            $table->boolean('delivery');
            $table->integer('perfil_id');
            $table->integer('intervalo_dias');

            $table->text('descricao');
            $table->string('img', 100);
            
            $table->boolean('visivel')->default(1);
            $table->boolean('api_sieg')->default(0);

            // alter table planos add column armazenamento integer default 0;
            // alter table planos add column api_sieg boolean default 0;
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
        Schema::dropIfExists('planos');
    }
}
