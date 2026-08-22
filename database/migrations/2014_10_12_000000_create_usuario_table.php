<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsuarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->string('login');
            $table->boolean('adm');
            $table->string('senha');
            $table->string('email', 200);
            $table->string('img', 100)->default('');

            $table->boolean('ativo')->default(true);
            $table->boolean('somente_fiscal')->default(false);
            $table->boolean('caixa_livre')->default(0);
            $table->boolean('permite_desconto')->default(true);
            $table->boolean('menu_representante')->default(false);

            $table->text('permissao');
            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');
            
            $table->integer('tema')->default(1);
            $table->integer('tema_menu')->default(1);
            $table->string('tipo_menu', 20)->default('lateral');
            $table->string('rota_acesso', 150)->default('');
            $table->text('locais')->nullable();
            $table->integer('local_padrao')->nullable();

            $table->boolean('aviso_sonoro')->default(true);

            // alter table usuarios add column tema_menu integer default 1;
            // alter table usuarios add column caixa_livre boolean default 0;
            // alter table usuarios add column rota_acesso varchar(150) default '';
            // alter table usuarios add column somente_fiscal boolean default 0;
            // alter table usuarios add column menu_representante boolean default 1;

            // alter table usuarios add column permite_desconto boolean default 1;
            // alter table usuarios add column tipo_menu varchar(20) default 'lateral';

            // alter table usuarios add column aviso_sonoro boolean default 1;

            
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
        Schema::dropIfExists('usuarios');
    }
}
