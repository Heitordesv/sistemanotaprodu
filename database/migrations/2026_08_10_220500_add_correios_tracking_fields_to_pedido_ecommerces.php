<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_ecommerces', function (Blueprint $table) {
            $table->string('correios_prepostagem_id', 120)->nullable()->after('codigo_rastreio');
            $table->string('correios_rotulo_recibo', 120)->nullable()->after('correios_prepostagem_id');
            $table->string('correios_status', 40)->nullable()->after('correios_rotulo_recibo');
            $table->timestamp('correios_ultima_consulta_at')->nullable()->after('correios_status');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_ecommerces', function (Blueprint $table) {
            $table->dropColumn([
                'correios_prepostagem_id',
                'correios_rotulo_recibo',
                'correios_status',
                'correios_ultima_consulta_at',
            ]);
        });
    }
};