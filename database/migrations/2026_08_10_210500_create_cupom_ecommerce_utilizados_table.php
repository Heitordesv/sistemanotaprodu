<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cupom_ecommerce_utilizados')) {
            return;
        }

        Schema::create('cupom_ecommerce_utilizados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('cupom_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('pedido_id');
            $table->timestamps();

            $table->unique(['cupom_id', 'cliente_id'], 'cupom_cliente_unique');
            $table->unique('pedido_id', 'cupom_pedido_unique');
            $table->index(['empresa_id', 'cupom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupom_ecommerce_utilizados');
    }
};