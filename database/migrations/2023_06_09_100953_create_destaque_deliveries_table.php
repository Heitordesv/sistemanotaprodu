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
        Schema::create('destaque_deliveries', function (Blueprint $table) {
            $table->id();

            $table->integer('empresa_id')->unsigned()->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('produto_id')->unsigned()->nullable();
            $table->foreign('produto_id')->references('id')->on('produto_deliveries')
            ->onDelete('cascade');

            $table->string('img', 30);
            $table->boolean('status');

            $table->integer('ordem');
            
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
        Schema::dropIfExists('destaque_deliveries');
    }
};
