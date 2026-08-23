<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addAuditColumns('conta_recebers', 'conta_recebers_pdv_devolucao_idx', 'conta_recebers_cancelado_por_idx');
        $this->addAuditColumns('comissao_vendas', 'comissao_vendas_pdv_devolucao_idx', 'comissao_vendas_cancelado_por_idx');
    }

    public function down(): void
    {
        // Não destrutivo: estes campos passam a compor a trilha financeira de
        // devoluções. Removê-los em rollback apagaria o vínculo entre o registro
        // original e a operação que o neutralizou.
    }

    private function addAuditColumns(string $tableName, string $returnIndex, string $userIndex): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $addReturn = !Schema::hasColumn($tableName, 'pdv_devolucao_id');
        $addCancelledAt = !Schema::hasColumn($tableName, 'cancelado_em');
        $addCancelledBy = !Schema::hasColumn($tableName, 'cancelado_por_usuario_id');

        if (!$addReturn && !$addCancelledAt && !$addCancelledBy) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use (
            $addReturn,
            $addCancelledAt,
            $addCancelledBy,
            $returnIndex,
            $userIndex
        ) {
            if ($addReturn) {
                $table->unsignedInteger('pdv_devolucao_id')->nullable()->index($returnIndex);
            }

            if ($addCancelledAt) {
                $table->timestamp('cancelado_em')->nullable();
            }

            if ($addCancelledBy) {
                $table->unsignedInteger('cancelado_por_usuario_id')->nullable()->index($userIndex);
            }
        });
    }
};
