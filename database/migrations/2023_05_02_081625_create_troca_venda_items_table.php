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
        Schema::create('troca_venda_items', function (Blueprint $table) {
            $table->id();

            $table->integer('troca_id')->unsigned();
            $table->foreign('troca_id')->references('id')->on('troca_vendas')
            ->onDelete('cascade');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos');

            $table->decimal('valor', 16,7);
            $table->decimal('quantidade', 10,3);
            
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
        Schema::dropIfExists('troca_venda_items');
    }
};
