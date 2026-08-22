<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemCompraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_compras', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('compra_id')->unsigned();
            $table->foreign('compra_id')->references('id')->on('compras');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos');

            $table->decimal('quantidade', 10,2);
            $table->decimal('valor_unitario', 16,7);
            $table->string('unidade_compra', 10);

            $table->date('validade')->nullable();

            $table->string('cfop_entrada', 4)->default('');
            $table->string('codigo_siad', 10)->default('');

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
        Schema::dropIfExists('item_compras');
    }
}
