<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWsAdicionaisItensGratisTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ws_adicionais_itens_gratis', function (Blueprint $table) {
            $table->increments('id_adicional_gratis');
            $table->string('nome_adicional_gratis');
            $table->unsignedInteger('categorias_adicional_gratis');
            $table->string('user_id');
            $table->boolean('status_adicional_gratis')->default(1); 
            $table->unsignedInteger('id_adicionais_cat');
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ws_adicionais_itens_gratis');
    }
}
