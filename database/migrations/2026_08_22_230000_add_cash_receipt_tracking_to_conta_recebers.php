<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('conta_recebers')) {
            throw new \RuntimeException('A tabela conta_recebers precisa existir antes da migration de auditoria financeira.');
        }

        $addAbertura = !Schema::hasColumn('conta_recebers', 'abertura_caixa_id');
        $addUsuario = !Schema::hasColumn('conta_recebers', 'received_by_user_id');
        $addReceivedAt = !Schema::hasColumn('conta_recebers', 'received_at');

        if ($addAbertura || $addUsuario || $addReceivedAt) {
            Schema::table('conta_recebers', function (Blueprint $table) use ($addAbertura, $addUsuario, $addReceivedAt) {
                if ($addAbertura) {
                    $table->unsignedBigInteger('abertura_caixa_id')->nullable()->index();
                }
                if ($addUsuario) {
                    $table->unsignedBigInteger('received_by_user_id')->nullable()->index();
                }
                if ($addReceivedAt) {
                    $table->timestamp('received_at')->nullable()->index();
                }
            });
        }

        if (!Schema::hasTable('conta_receber_recebimentos')) {
            Schema::create('conta_receber_recebimentos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('conta_receber_id')->index();
                $table->unsignedBigInteger('empresa_id')->index();
                $table->unsignedBigInteger('abertura_caixa_id')->nullable()->index();
                $table->unsignedBigInteger('usuario_id')->nullable()->index();
                $table->decimal('valor', 15, 7);
                $table->string('tipo_pagamento', 10)->nullable()->index();
                $table->timestamp('received_at')->nullable()->index();
                $table->timestamps();
                $table->index(['empresa_id', 'abertura_caixa_id', 'received_at'], 'crr_empresa_caixa_received_idx');
            });

            return;
        }

        // Banco legado: nunca assumimos que uma tabela existente está completa.
        // A coluna id é estrutural; se não existir, abortamos em vez de tentar
        // criar uma PK sobre uma tabela desconhecida e potencialmente corromper dados.
        if (!Schema::hasColumn('conta_receber_recebimentos', 'id')) {
            throw new \RuntimeException(
                'A tabela conta_receber_recebimentos já existe, mas não possui a coluna id. Corrija o schema legado antes de migrar.'
            );
        }

        $missing = [
            'conta_receber_id' => !Schema::hasColumn('conta_receber_recebimentos', 'conta_receber_id'),
            'empresa_id' => !Schema::hasColumn('conta_receber_recebimentos', 'empresa_id'),
            'abertura_caixa_id' => !Schema::hasColumn('conta_receber_recebimentos', 'abertura_caixa_id'),
            'usuario_id' => !Schema::hasColumn('conta_receber_recebimentos', 'usuario_id'),
            'valor' => !Schema::hasColumn('conta_receber_recebimentos', 'valor'),
            'tipo_pagamento' => !Schema::hasColumn('conta_receber_recebimentos', 'tipo_pagamento'),
            'received_at' => !Schema::hasColumn('conta_receber_recebimentos', 'received_at'),
            'created_at' => !Schema::hasColumn('conta_receber_recebimentos', 'created_at'),
            'updated_at' => !Schema::hasColumn('conta_receber_recebimentos', 'updated_at'),
        ];

        if (in_array(true, $missing, true)) {
            Schema::table('conta_receber_recebimentos', function (Blueprint $table) use ($missing) {
                // Em tabela legada com dados, novas colunas são nullable para que a
                // migration seja não destrutiva. Novos registros do serviço sempre
                // preenchem os campos obrigatórios.
                if ($missing['conta_receber_id']) {
                    $table->unsignedBigInteger('conta_receber_id')->nullable()->index();
                }
                if ($missing['empresa_id']) {
                    $table->unsignedBigInteger('empresa_id')->nullable()->index();
                }
                if ($missing['abertura_caixa_id']) {
                    $table->unsignedBigInteger('abertura_caixa_id')->nullable()->index();
                }
                if ($missing['usuario_id']) {
                    $table->unsignedBigInteger('usuario_id')->nullable()->index();
                }
                if ($missing['valor']) {
                    $table->decimal('valor', 15, 7)->nullable();
                }
                if ($missing['tipo_pagamento']) {
                    $table->string('tipo_pagamento', 10)->nullable()->index();
                }
                if ($missing['received_at']) {
                    $table->timestamp('received_at')->nullable()->index();
                }
                if ($missing['created_at']) {
                    $table->timestamp('created_at')->nullable();
                }
                if ($missing['updated_at']) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Intencionalmente não destrutivo. Esta migration atende bancos legados
        // onde tabela/colunas podem ter sido criadas manualmente antes do histórico
        // atual de migrations. Rollback nunca deve apagar auditoria financeira real.
    }
};
