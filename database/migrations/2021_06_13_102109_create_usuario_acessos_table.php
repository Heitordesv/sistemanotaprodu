<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsuarioAcessosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuario_acessos', function (Blueprint $table) {
            $table->increments('id');

            $table->boolean('status')->default(false);

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')
            ->on('usuarios')->onDelete('cascade');

            $table->string('hash', 20);
            $table->string('ip_address', 20);

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
        Schema::dropIfExists('usuario_acessos');
    }
}
