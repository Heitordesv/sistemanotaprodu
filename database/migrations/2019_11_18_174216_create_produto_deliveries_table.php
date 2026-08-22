<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProdutoDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produto_deliveries', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');

            $table->integer('categoria_id')->unsigned();
            $table->foreign('categoria_id')->references('id')->on('categoria_produto_deliveries')->onDelete('cascade');

            $table->string('descricao_curta', 50);
            $table->string('descricao', 255);
            $table->string('ingredientes', 255);
            $table->string('referencia', 12);
            $table->decimal('valor', 10,2);
            $table->decimal('valor_anterior', 10,2);
            $table->boolean('status');
            $table->boolean('destaque');
            $table->integer('limite_diario');

            $table->boolean('tem_adicionais')->default(0);
            $table->enum('tipo', ['simples', 'variavel']);

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
        Schema::dropIfExists('produto_deliveries');
    }
}
