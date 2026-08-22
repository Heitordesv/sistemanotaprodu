<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWsAdicionaisItensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ws_adicionais_itens', function (Blueprint $table) {
            $table->increments('id_adicionais');
            $table->string('user_id');
            $table->unsignedInteger('categorias_adicional'); 
            $table->string('nome_adicional');
            $table->decimal('valor_adicional', 8, 2); 
            $table->string('medida_adicional')->nullable(); 
            $table->boolean('status_adicional')->default(1);
            $table->unsignedInteger('id_adicionais_cat');
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ws_adicionais_itens');
    }
}
