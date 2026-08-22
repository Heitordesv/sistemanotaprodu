<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('conta_receber_recebimentos');

        $dropAbertura = Schema::hasColumn('conta_recebers', 'abertura_caixa_id');
        $dropUsuario = Schema::hasColumn('conta_recebers', 'received_by_user_id');
        $dropReceivedAt = Schema::hasColumn('conta_recebers', 'received_at');

        if ($dropAbertura || $dropUsuario || $dropReceivedAt) {
            Schema::table('conta_recebers', function (Blueprint $table) use ($dropAbertura, $dropUsuario, $dropReceivedAt) {
                if ($dropAbertura) {
                    $table->dropColumn('abertura_caixa_id');
                }
                if ($dropUsuario) {
                    $table->dropColumn('received_by_user_id');
                }
                if ($dropReceivedAt) {
                    $table->dropColumn('received_at');
                }
            });
        }
    }
};
