<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venda_caixas', function (Blueprint $table) {
            $table->unsignedBigInteger('lista_preco_id')
                ->nullable()
                ->after('abertura_caixa_id');
            $table->index(
                ['empresa_id', 'lista_preco_id'],
                'venda_caixas_empresa_lista_preco_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('venda_caixas', function (Blueprint $table) {
            $table->dropIndex('venda_caixas_empresa_lista_preco_idx');
            $table->dropColumn('lista_preco_id');
        });
    }
};
