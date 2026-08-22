<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('config_ecommerces', function (Blueprint $table) {
            if (!Schema::hasColumn('config_ecommerces', 'mercadopago_webhook_secret')) {
                $table->string('mercadopago_webhook_secret', 255)->nullable()->after('mercadopago_access_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('config_ecommerces', function (Blueprint $table) {
            if (Schema::hasColumn('config_ecommerces', 'mercadopago_webhook_secret')) {
                $table->dropColumn('mercadopago_webhook_secret');
            }
        });
    }
};