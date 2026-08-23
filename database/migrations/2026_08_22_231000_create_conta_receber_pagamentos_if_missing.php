<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('conta_receber_pagamentos')) {
            Schema::create('conta_receber_pagamentos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('conta_receber_id')->index();
                $table->unsignedBigInteger('empresa_id')->index();
                $table->decimal('valor', 15, 2);
                $table->string('forma_pagamento', 10)->index();
                $table->dateTime('data_pagamento');
                $table->string('origem', 30)->nullable()->index();
                $table->string('provedor', 60)->nullable();
                $table->string('external_id', 191)->nullable()->index();
                $table->uuid('lote_uuid')->nullable()->index();
                $table->string('status', 30)->default('confirmado')->index();
                $table->text('observacao')->nullable();
                $table->timestamps();

                $table->index(
                    ['empresa_id', 'conta_receber_id', 'status'],
                    'crp_empresa_conta_status_idx'
                );
            });

            return;
        }

        // Tabela pré-existente: não retornamos silenciosamente. Validamos a
        // estrutura mínima e completamos apenas as colunas ausentes.
        if (!Schema::hasColumn('conta_receber_pagamentos', 'id')) {
            throw new \RuntimeException(
                'A tabela conta_receber_pagamentos já existe, mas não possui a coluna id. Corrija o schema legado antes de migrar.'
            );
        }

        $missing = [
            'conta_receber_id' => !Schema::hasColumn('conta_receber_pagamentos', 'conta_receber_id'),
            'empresa_id' => !Schema::hasColumn('conta_receber_pagamentos', 'empresa_id'),
            'valor' => !Schema::hasColumn('conta_receber_pagamentos', 'valor'),
            'forma_pagamento' => !Schema::hasColumn('conta_receber_pagamentos', 'forma_pagamento'),
            'data_pagamento' => !Schema::hasColumn('conta_receber_pagamentos', 'data_pagamento'),
            'origem' => !Schema::hasColumn('conta_receber_pagamentos', 'origem'),
            'provedor' => !Schema::hasColumn('conta_receber_pagamentos', 'provedor'),
            'external_id' => !Schema::hasColumn('conta_receber_pagamentos', 'external_id'),
            'lote_uuid' => !Schema::hasColumn('conta_receber_pagamentos', 'lote_uuid'),
            'status' => !Schema::hasColumn('conta_receber_pagamentos', 'status'),
            'observacao' => !Schema::hasColumn('conta_receber_pagamentos', 'observacao'),
            'created_at' => !Schema::hasColumn('conta_receber_pagamentos', 'created_at'),
            'updated_at' => !Schema::hasColumn('conta_receber_pagamentos', 'updated_at'),
        ];

        if (!in_array(true, $missing, true)) {
            return;
        }

        Schema::table('conta_receber_pagamentos', function (Blueprint $table) use ($missing) {
            // Nullable em estrutura legada para preservar linhas existentes. O
            // fluxo novo sempre grava os campos necessários nos novos pagamentos.
            if ($missing['conta_receber_id']) {
                $table->unsignedBigInteger('conta_receber_id')->nullable()->index();
            }
            if ($missing['empresa_id']) {
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
            }
            if ($missing['valor']) {
                $table->decimal('valor', 15, 2)->nullable();
            }
            if ($missing['forma_pagamento']) {
                $table->string('forma_pagamento', 10)->nullable()->index();
            }
            if ($missing['data_pagamento']) {
                $table->dateTime('data_pagamento')->nullable();
            }
            if ($missing['origem']) {
                $table->string('origem', 30)->nullable()->index();
            }
            if ($missing['provedor']) {
                $table->string('provedor', 60)->nullable();
            }
            if ($missing['external_id']) {
                $table->string('external_id', 191)->nullable()->index();
            }
            if ($missing['lote_uuid']) {
                $table->uuid('lote_uuid')->nullable()->index();
            }
            if ($missing['status']) {
                $table->string('status', 30)->nullable()->index();
            }
            if ($missing['observacao']) {
                $table->text('observacao')->nullable();
            }
            if ($missing['created_at']) {
                $table->timestamp('created_at')->nullable();
            }
            if ($missing['updated_at']) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intencionalmente não destrutivo: esta migration também atende bancos
        // legados onde a tabela pode ter sido criada fora do histórico atual de
        // migrations. Um rollback não deve apagar pagamentos financeiros reais.
    }
};
