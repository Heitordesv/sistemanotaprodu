<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidoPagSegurosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_pag_seguros', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('pedido_delivery_id')->unsigned();
            $table->foreign('pedido_delivery_id')->references('id')
            ->on('pedido_deliveries')->onDelete('cascade');

            $table->string('numero_cartao', 20);
            $table->string('cpf', 15);
            $table->string('nome_impresso', 25);
            $table->string('codigo_transacao', 45);
            $table->string('referencia', 35);
            $table->string('bandeira', 10);
            $table->integer('parcelas');
            $table->integer('status');
            
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
        Schema::dropIfExists('pedido_pag_seguros');
    }
}
