<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_document_sequences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedSmallInteger('modelo');
            $table->unsignedSmallInteger('serie');
            $table->unsignedTinyInteger('ambiente');
            $table->unsignedBigInteger('ultimo_numero')->default(0);
            $table->timestamps();

            $table->unique(
                ['empresa_id', 'modelo', 'serie', 'ambiente'],
                'fiscal_seq_empresa_modelo_serie_amb_unique'
            );
            $table->index(['empresa_id', 'modelo'], 'fiscal_seq_empresa_modelo_idx');
        });

        Schema::create('fiscal_document_reservations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedSmallInteger('modelo');
            $table->unsignedSmallInteger('serie');
            $table->unsignedTinyInteger('ambiente');
            $table->unsignedBigInteger('numero');
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id');
            $table->string('status', 24)->default('reserved');
            $table->string('chave', 64)->nullable();
            $table->string('protocolo', 80)->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['empresa_id', 'modelo', 'serie', 'ambiente', 'numero'],
                'fiscal_res_numero_unique'
            );
            $table->unique(
                ['empresa_id', 'modelo', 'source_type', 'source_id'],
                'fiscal_res_source_unique'
            );
            $table->index(['empresa_id', 'status'], 'fiscal_res_empresa_status_idx');
        });

        if (Schema::hasTable('venda_caixas') && !Schema::hasColumn('venda_caixas', 'nSerie')) {
            Schema::table('venda_caixas', function (Blueprint $table) {
                $table->unsignedSmallInteger('nSerie')->nullable()->after('numero_nfce');
                $table->index(['empresa_id', 'nSerie', 'numero_nfce'], 'venda_caixas_fiscal_seq_idx');
            });
        }

        $this->seedLegacySequences();
    }

    public function down(): void
    {
        if (Schema::hasTable('venda_caixas') && Schema::hasColumn('venda_caixas', 'nSerie')) {
            Schema::table('venda_caixas', function (Blueprint $table) {
                $table->dropIndex('venda_caixas_fiscal_seq_idx');
                $table->dropColumn('nSerie');
            });
        }

        Schema::dropIfExists('fiscal_document_reservations');
        Schema::dropIfExists('fiscal_document_sequences');
    }

    private function seedLegacySequences(): void
    {
        if (!Schema::hasTable('config_notas')) {
            return;
        }

        DB::table('config_notas')
            ->select([
                'id',
                'empresa_id',
                'ambiente',
                'numero_serie_nfe',
                'numero_serie_nfce',
                'ultimo_numero_nfe',
                'ultimo_numero_nfce',
            ])
            ->whereNotNull('empresa_id')
            ->orderBy('id')
            ->chunkById(200, function ($configs) {
                foreach ($configs as $config) {
                    $ambiente = max(1, min(2, (int) ($config->ambiente ?: 2)));

                    $this->seedSequence(
                        (int) $config->empresa_id,
                        55,
                        (int) ($config->numero_serie_nfe ?: 1),
                        $ambiente,
                        (int) ($config->ultimo_numero_nfe ?: 0)
                    );

                    $this->seedSequence(
                        (int) $config->empresa_id,
                        65,
                        (int) ($config->numero_serie_nfce ?: 1),
                        $ambiente,
                        (int) ($config->ultimo_numero_nfce ?: 0)
                    );
                }
            }, 'id');
    }

    private function seedSequence(int $empresaId, int $modelo, int $serie, int $ambiente, int $ultimo): void
    {
        DB::table('fiscal_document_sequences')->insertOrIgnore([
            'empresa_id' => $empresaId,
            'modelo' => $modelo,
            'serie' => max(0, $serie),
            'ambiente' => $ambiente,
            'ultimo_numero' => max(0, $ultimo),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
