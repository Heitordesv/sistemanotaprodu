<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venda_caixas', function (Blueprint $table) {
            $table->string('desconto_tipo', 12)
                ->default('fixo')
                ->after('desconto');
            $table->decimal('desconto_percentual', 7, 4)
                ->nullable()
                ->after('desconto_tipo');
            $table->string('acrescimo_tipo', 12)
                ->default('fixo')
                ->after('acrescimo');
            $table->decimal('acrescimo_percentual', 7, 4)
                ->nullable()
                ->after('acrescimo_tipo');
            $table->decimal('taxa_entrega', 10, 2)
                ->default(0)
                ->after('acrescimo_percentual');
        });
    }

    public function down(): void
    {
        Schema::table('venda_caixas', function (Blueprint $table) {
            $table->dropColumn([
                'desconto_tipo',
                'desconto_percentual',
                'acrescimo_tipo',
                'acrescimo_percentual',
                'taxa_entrega',
            ]);
        });
    }
};
