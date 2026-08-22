<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lead_observacoes', function (Blueprint $table) {
            // Primeiro, remova a chave estrangeira existente
            // Certifique-se de que o nome da chave estrangeira está correto se for diferente de 'lead_observacoes_lead_id_foreign'
            $table->dropForeign(['lead_id']);

            // Adicione-a de volta com a regra ON DELETE CASCADE
            $table->foreign('lead_id')
                  ->references('id')
                  ->on('leads')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_observacoes', function (Blueprint $table) {
            // Para reverter, remova a chave estrangeira com cascade
            $table->dropForeign(['lead_id']);

            // E a adicione novamente sem cascade (ou como era originalmente)
            $table->foreign('lead_id')
                  ->references('id')
                  ->on('leads');
        });
    }
};