<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClienteEcommercesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_ecommerces', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('nome', 30);
            $table->string('sobre_nome', 30);
            $table->string('cpf', 18);
            $table->string('ie', 15)->default('');
            $table->string('email', 60);
            $table->string('telefone', 15);
            $table->string('senha', 100);
            $table->string('token', 20)->default('');
            $table->boolean('status')->default(true);

            // alter table cliente_ecommerces add column token varchar(20) default '';
            
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
        Schema::dropIfExists('cliente_ecommerces');
    }
}
