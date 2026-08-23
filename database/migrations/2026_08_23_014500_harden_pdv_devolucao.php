<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureAuthorizationTable();
        $this->addStockScopeToSale();
        $this->addBranchToStockAudit();
        $this->createReturnLedger();
    }

    public function down(): void
    {
        // Intencionalmente não destrutivo.
        // Devoluções, cancelamentos fiscais e trilhas financeiras são evidências
        // operacionais/fiscais. Uma reversão estrutural deve ser feita por migration
        // corretiva explícita, com backup e validação dos dados.
    }

    private function ensureAuthorizationTable(): void
    {
        if (Schema::hasTable('autorizacoes_devolucao_caixa')) {
            return;
        }

        Schema::create('autorizacoes_devolucao_caixa', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id')->index();
            $table->unsignedInteger('venda_caixa_id')->index();
            $table->unsignedInteger('usuario_solicitante_id')->index();
            $table->string('usuario_solicitante_nome', 150);
            $table->unsignedInteger('usuario_autorizador_id')->index();
            $table->string('usuario_autorizador_nome', 150);
            $table->string('tipo', 40);
            $table->string('numero_nfce', 30)->nullable();
            $table->decimal('valor_venda', 16, 7)->default(0);
            $table->string('motivo', 255)->nullable();
            $table->timestamps();
        });
    }

    private function addStockScopeToSale(): void
    {
        if (!Schema::hasTable('venda_caixas') || Schema::hasColumn('venda_caixas', 'estoque_filial_id')) {
            return;
        }

        Schema::table('venda_caixas', function (Blueprint $table) {
            // Guarda o escopo REAL usado na baixa. NULL representa estoque matriz/legado.
            // Não usamos filial_id da venda como prova histórica porque o fluxo antigo
            // baixava estoque sem repassar a filial ao StockMove.
            $table->unsignedInteger('estoque_filial_id')
                ->nullable()
                ->index('venda_caixas_estoque_filial_idx');
        });
    }

    private function addBranchToStockAudit(): void
    {
        if (!Schema::hasTable('alteracao_estoques') || Schema::hasColumn('alteracao_estoques', 'filial_id')) {
            return;
        }

        Schema::table('alteracao_estoques', function (Blueprint $table) {
            $table->unsignedInteger('filial_id')
                ->nullable()
                ->index('alteracao_estoques_filial_idx');
        });
    }

    private function createReturnLedger(): void
    {
        if (Schema::hasTable('pdv_devolucoes')) {
            return;
        }

        Schema::create('pdv_devolucoes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id')->index('pdv_devolucoes_empresa_idx');
            $table->unsignedInteger('venda_caixa_id');
            $table->string('tipo', 30);
            $table->string('status', 40)->index('pdv_devolucoes_status_idx');

            $table->unsignedInteger('usuario_solicitante_id')->index('pdv_devolucoes_solicitante_idx');
            $table->string('usuario_solicitante_nome', 150);
            $table->unsignedInteger('usuario_autorizador_id')->index('pdv_devolucoes_autorizador_idx');
            $table->string('usuario_autorizador_nome', 150);

            $table->string('motivo', 255)->nullable();
            $table->decimal('valor_venda', 16, 7)->default(0);
            $table->unsignedInteger('filial_id')->nullable()->index('pdv_devolucoes_filial_idx');
            $table->unsignedInteger('estoque_filial_id')->nullable()->index('pdv_devolucoes_estoque_filial_idx');
            $table->unsignedInteger('abertura_caixa_original_id')->nullable()->index('pdv_devolucoes_caixa_original_idx');
            $table->unsignedInteger('abertura_caixa_compensacao_id')->nullable()->index('pdv_devolucoes_caixa_comp_idx');
            $table->decimal('valor_reembolso_dinheiro', 16, 7)->default(0);

            $table->string('sefaz_cstat', 20)->nullable();
            $table->string('sefaz_protocolo', 60)->nullable();
            $table->string('sefaz_mensagem', 255)->nullable();
            $table->longText('financeiro_json')->nullable();

            $table->timestamp('sefaz_cancelada_em')->nullable();
            $table->timestamp('estoque_processado_em')->nullable();
            $table->timestamp('financeiro_processado_em')->nullable();
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();

            // Uma venda só pode originar uma devolução. Esta restrição é a última
            // barreira de idempotência mesmo se duas requisições chegarem juntas.
            $table->unique('venda_caixa_id', 'pdv_devolucoes_venda_unique');
        });
    }
};
