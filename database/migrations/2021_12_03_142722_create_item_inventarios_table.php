<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemInventariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_inventarios', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('inventario_id')->unsigned();
            $table->foreign('inventario_id')->references('id')->on('inventarios');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios')
            ->onDelete('cascade');

            $table->decimal('quantidade', 10,2);
            $table->string('observacao', 100)->deafult('');
            $table->string('estado', 15);
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
        Schema::dropIfExists('item_inventarios');
    }
}
