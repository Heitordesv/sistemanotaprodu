<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_venda_caixa_pre_vendas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('pre_venda_id')->unsigned();
            $table->foreign('pre_venda_id')->references('id')->
            on('venda_caixa_pre_vendas')->onDelete('cascade');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos');

            $table->decimal('quantidade', 10,3);
            $table->decimal('valor', 16, 7);

            $table->string('observacao', 80);

            $table->integer('cfop')->default(0);

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
        Schema::dropIfExists('item_venda_caixa_pre_vendas');
    }
};
