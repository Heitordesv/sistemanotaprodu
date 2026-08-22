<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addColumn('venda_caixas', 'venda_caixas_abertura_idx');
        $this->addColumn('vendas', 'vendas_abertura_idx');
        $this->addColumn('sangria_caixas', 'sangria_caixas_abertura_idx');
        $this->addColumn('suprimento_caixas', 'suprimento_caixas_abertura_idx');
    }

    public function down(): void
    {
        $this->dropColumn('suprimento_caixas', 'suprimento_caixas_abertura_idx');
        $this->dropColumn('sangria_caixas', 'sangria_caixas_abertura_idx');
        $this->dropColumn('vendas', 'vendas_abertura_idx');
        $this->dropColumn('venda_caixas', 'venda_caixas_abertura_idx');
    }

    private function addColumn(string $tableName, string $indexName): void
    {
        if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'abertura_caixa_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            // Sem FK rígida para manter compatibilidade com bancos legados
            // que podem possuir engine/tipos diferentes em produção.
            $table->unsignedInteger('abertura_caixa_id')
                ->nullable()
                ->index($indexName);
        });
    }

    private function dropColumn(string $tableName, string $indexName): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'abertura_caixa_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
            $table->dropColumn('abertura_caixa_id');
        });
    }
};
