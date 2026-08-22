<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutoIbptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produto_ibpts', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');

            $table->string('codigo');
            $table->string('uf', 2);
            $table->string('descricao', 100);
            $table->decimal('nacional', 5, 2);
            $table->decimal('estadual', 5, 2);
            $table->decimal('importado', 5, 2);
            $table->decimal('municipal', 5, 2);
            $table->string('vigencia_inicio', 10);
            $table->string('vigencia_fim', 10);
            $table->string('chave', 10);
            $table->string('versao', 10);
            $table->string('fonte', 40);
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
        Schema::dropIfExists('produto_ibpts');
    }
}
