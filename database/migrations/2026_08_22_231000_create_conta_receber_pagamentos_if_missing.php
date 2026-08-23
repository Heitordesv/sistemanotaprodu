<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('conta_receber_pagamentos')) {
            return;
        }

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
    }

    public function down(): void
    {
        // Intencionalmente não destrutivo: esta migration também atende bancos
        // legados onde a tabela pode ter sido criada fora do histórico atual de
        // migrations. Um rollback não deve apagar pagamentos financeiros reais.
    }
};
