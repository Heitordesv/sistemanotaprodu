<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addOpeningColumn('venda_caixas', 'venda_caixas_abertura_idx');
        $this->addOpeningColumn('vendas', 'vendas_abertura_idx');
        $this->addOpeningColumn('sangria_caixas', 'sangria_caixas_abertura_idx');
        $this->addOpeningColumn('suprimento_caixas', 'suprimento_caixas_abertura_idx');
        $this->addReceivableAuditColumns();
    }

    public function down(): void
    {
        $this->dropReceivableAuditColumns();
        $this->dropOpeningColumn('suprimento_caixas', 'suprimento_caixas_abertura_idx');
        $this->dropOpeningColumn('sangria_caixas', 'sangria_caixas_abertura_idx');
        $this->dropOpeningColumn('vendas', 'vendas_abertura_idx');
        $this->dropOpeningColumn('venda_caixas', 'venda_caixas_abertura_idx');
    }

    private function addOpeningColumn(string $tableName, string $indexName): void
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

    private function dropOpeningColumn(string $tableName, string $indexName): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'abertura_caixa_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
            $table->dropColumn('abertura_caixa_id');
        });
    }

    private function addReceivableAuditColumns(): void
    {
        if (!Schema::hasTable('conta_recebers')) {
            return;
        }

        $addOpening = !Schema::hasColumn('conta_recebers', 'abertura_caixa_id');
        $addUser = !Schema::hasColumn('conta_recebers', 'received_by_user_id');
        $addDate = !Schema::hasColumn('conta_recebers', 'received_at');

        if (!$addOpening && !$addUser && !$addDate) {
            return;
        }

        Schema::table('conta_recebers', function (Blueprint $table) use ($addOpening, $addUser, $addDate) {
            if ($addOpening) {
                $table->unsignedInteger('abertura_caixa_id')
                    ->nullable()
                    ->index('conta_recebers_abertura_idx');
            }

            if ($addUser) {
                $table->unsignedInteger('received_by_user_id')
                    ->nullable()
                    ->index('conta_recebers_recebedor_idx');
            }

            if ($addDate) {
                $table->timestamp('received_at')->nullable();
            }
        });
    }

    private function dropReceivableAuditColumns(): void
    {
        if (!Schema::hasTable('conta_recebers')) {
            return;
        }

        if (Schema::hasColumn('conta_recebers', 'received_by_user_id')) {
            Schema::table('conta_recebers', function (Blueprint $table) {
                $table->dropIndex('conta_recebers_recebedor_idx');
                $table->dropColumn('received_by_user_id');
            });
        }

        if (Schema::hasColumn('conta_recebers', 'abertura_caixa_id')) {
            Schema::table('conta_recebers', function (Blueprint $table) {
                $table->dropIndex('conta_recebers_abertura_idx');
                $table->dropColumn('abertura_caixa_id');
            });
        }

        if (Schema::hasColumn('conta_recebers', 'received_at')) {
            Schema::table('conta_recebers', function (Blueprint $table) {
                $table->dropColumn('received_at');
            });
        }
    }
};
