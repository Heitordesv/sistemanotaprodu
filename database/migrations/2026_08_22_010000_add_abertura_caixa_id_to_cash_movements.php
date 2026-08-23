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
        $this->createReceivableAuditTable();
    }

    public function down(): void
    {
        // Intencionalmente não destrutivo.
        // Esta migration introduz vínculos e histórico de auditoria financeira.
        // Remover tabela/colunas em rollback apagaria evidências de recebimentos
        // e poderia corromper fechamentos de caixa já realizados.
        //
        // Caso seja necessária uma reversão estrutural, ela deve ser feita por
        // migration corretiva explícita, com backup, validação e plano de dados.
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

    private function createReceivableAuditTable(): void
    {
        if (Schema::hasTable('conta_receber_recebimentos')) {
            return;
        }

        Schema::create('conta_receber_recebimentos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('conta_receber_id')->index('crr_conta_idx');
            $table->unsignedInteger('empresa_id')->index('crr_empresa_idx');
            $table->unsignedInteger('abertura_caixa_id')->nullable()->index('crr_abertura_idx');
            $table->unsignedInteger('usuario_id')->nullable()->index('crr_usuario_idx');
            $table->decimal('valor', 16, 7);
            $table->string('tipo_pagamento', 50)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }
};
