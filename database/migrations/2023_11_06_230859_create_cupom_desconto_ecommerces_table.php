<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cupom_desconto_ecommerces', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('descricao', 100);
            $table->decimal('valor', 10, 2);
            $table->decimal('valor_minimo_pedido', 10, 2);
            $table->boolean('status')->default(1);
            $table->string('codigo', 6);
            $table->enum('tipo', ['percentual', 'fixo']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cupom_desconto_ecommerces');
    }
};
