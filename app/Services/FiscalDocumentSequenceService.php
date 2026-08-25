<?php

namespace App\Services;

use App\Models\ConfigNota;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class FiscalDocumentSequenceService
{
    public const MODELO_NFE = 55;
    public const MODELO_NFCE = 65;

    public function reserveNFe(int $empresaId, int $vendaId, int $serie, int $ambiente): array
    {
        return $this->reserve($empresaId, self::MODELO_NFE, $serie, $ambiente, 'venda', $vendaId);
    }

    public function reserveNFCe(int $empresaId, int $vendaCaixaId, int $serie, int $ambiente): array
    {
        return $this->reserve($empresaId, self::MODELO_NFCE, $serie, $ambiente, 'venda_caixa', $vendaCaixaId);
    }

    public function reserve(
        int $empresaId,
        int $modelo,
        int $serie,
        int $ambiente,
        string $sourceType,
        int $sourceId
    ): array {
        $this->validate($empresaId, $modelo, $serie, $ambiente, $sourceType, $sourceId);

        return DB::transaction(function () use (
            $empresaId,
            $modelo,
            $serie,
            $ambiente,
            $sourceType,
            $sourceId
        ) {
            $existing = DB::table('fiscal_document_reservations')
                ->where('empresa_id', $empresaId)
                ->where('modelo', $modelo)
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $this->reservationToArray($existing);
            }

            $this->ensureSequenceRow($empresaId, $modelo, $serie, $ambiente);

            $sequence = DB::table('fiscal_document_sequences')
                ->where('empresa_id', $empresaId)
                ->where('modelo', $modelo)
                ->where('serie', $serie)
                ->where('ambiente', $ambiente)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                throw new RuntimeException('Não foi possível inicializar a sequência fiscal.');
            }

            $numero = (int) $sequence->ultimo_numero + 1;
            if ($numero > 999999999) {
                throw new RuntimeException('A sequência fiscal atingiu o limite suportado.');
            }

            DB::table('fiscal_document_sequences')
                ->where('id', $sequence->id)
                ->update([
                    'ultimo_numero' => $numero,
                    'updated_at' => now(),
                ]);

            try {
                $reservationId = DB::table('fiscal_document_reservations')->insertGetId([
                    'empresa_id' => $empresaId,
                    'modelo' => $modelo,
                    'serie' => $serie,
                    'ambiente' => $ambiente,
                    'numero' => $numero,
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'status' => 'reserved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (QueryException $e) {
                // Uma segunda requisição do mesmo documento pode ter vencido a corrida
                // entre o primeiro lookup e o INSERT. A unique key do source garante
                // idempotência; em qualquer outro conflito propagamos o erro.
                $winner = DB::table('fiscal_document_reservations')
                    ->where('empresa_id', $empresaId)
                    ->where('modelo', $modelo)
                    ->where('source_type', $sourceType)
                    ->where('source_id', $sourceId)
                    ->first();

                if (!$winner) {
                    throw $e;
                }

                return $this->reservationToArray($winner);
            }

            $this->syncLegacyCounter($empresaId, $modelo, $numero);

            $reservation = DB::table('fiscal_document_reservations')
                ->where('id', $reservationId)
                ->first();

            return $this->reservationToArray($reservation);
        }, 5);
    }

    public function markAuthorized(int $reservationId, ?string $chave = null, ?string $protocolo = null): void
    {
        DB::transaction(function () use ($reservationId, $chave, $protocolo) {
            $reservation = DB::table('fiscal_document_reservations')
                ->where('id', $reservationId)
                ->lockForUpdate()
                ->first();

            if (!$reservation) {
                throw new RuntimeException('Reserva fiscal não encontrada.');
            }

            DB::table('fiscal_document_reservations')
                ->where('id', $reservationId)
                ->update([
                    'status' => 'authorized',
                    'chave' => $chave ?: $reservation->chave,
                    'protocolo' => $protocolo ?: $reservation->protocolo,
                    'authorized_at' => $reservation->authorized_at ?: now(),
                    'updated_at' => now(),
                ]);
        });
    }

    public function markRejected(int $reservationId, ?string $chave = null): void
    {
        $this->markStatus($reservationId, 'rejected', $chave);
    }

    public function markUncertain(int $reservationId, ?string $chave = null): void
    {
        $this->markStatus($reservationId, 'uncertain', $chave);
    }

    private function markStatus(int $reservationId, string $status, ?string $chave): void
    {
        DB::transaction(function () use ($reservationId, $status, $chave) {
            $reservation = DB::table('fiscal_document_reservations')
                ->where('id', $reservationId)
                ->lockForUpdate()
                ->first();

            if (!$reservation || $reservation->status === 'authorized') {
                return;
            }

            DB::table('fiscal_document_reservations')
                ->where('id', $reservationId)
                ->update([
                    'status' => $status,
                    'chave' => $chave ?: $reservation->chave,
                    'updated_at' => now(),
                ]);
        });
    }

    private function ensureSequenceRow(int $empresaId, int $modelo, int $serie, int $ambiente): void
    {
        $exists = DB::table('fiscal_document_sequences')
            ->where('empresa_id', $empresaId)
            ->where('modelo', $modelo)
            ->where('serie', $serie)
            ->where('ambiente', $ambiente)
            ->exists();

        if ($exists) {
            return;
        }

        // Se este modelo ainda não possui nenhuma sequência nova, estamos no
        // bootstrap do legado e preservamos o contador configurado atualmente.
        // Se já existe outra série, a nova série nasce independente em 1.
        $hasModelSequence = DB::table('fiscal_document_sequences')
            ->where('empresa_id', $empresaId)
            ->where('modelo', $modelo)
            ->where('ambiente', $ambiente)
            ->exists();

        $legacyLast = 0;
        if (!$hasModelSequence) {
            $config = ConfigNota::query()
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->first();

            if ($config) {
                $legacyLast = $modelo === self::MODELO_NFE
                    ? (int) $config->ultimo_numero_nfe
                    : (int) $config->ultimo_numero_nfce;
            }
        }

        DB::table('fiscal_document_sequences')->insertOrIgnore([
            'empresa_id' => $empresaId,
            'modelo' => $modelo,
            'serie' => $serie,
            'ambiente' => $ambiente,
            'ultimo_numero' => max(0, $legacyLast),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function syncLegacyCounter(int $empresaId, int $modelo, int $numero): void
    {
        $config = ConfigNota::query()
            ->where('empresa_id', $empresaId)
            ->lockForUpdate()
            ->first();

        if (!$config) {
            return;
        }

        $field = $modelo === self::MODELO_NFE ? 'ultimo_numero_nfe' : 'ultimo_numero_nfce';
        if ((int) $config->{$field} < $numero) {
            $config->{$field} = $numero;
            $config->save();
        }
    }

    private function validate(
        int $empresaId,
        int $modelo,
        int $serie,
        int $ambiente,
        string $sourceType,
        int $sourceId
    ): void {
        if ($empresaId <= 0 || $sourceId <= 0) {
            throw new InvalidArgumentException('Empresa e documento fiscal devem ser válidos.');
        }

        if (!in_array($modelo, [self::MODELO_NFE, self::MODELO_NFCE], true)) {
            throw new InvalidArgumentException('Modelo fiscal não suportado pela sequência.');
        }

        if ($serie < 0 || $serie > 999) {
            throw new InvalidArgumentException('Série fiscal fora do intervalo suportado.');
        }

        if (!in_array($ambiente, [1, 2], true)) {
            throw new InvalidArgumentException('Ambiente fiscal inválido.');
        }

        if (!in_array($sourceType, ['venda', 'venda_caixa'], true)) {
            throw new InvalidArgumentException('Origem fiscal inválida.');
        }
    }

    private function reservationToArray(object $reservation): array
    {
        return [
            'id' => (int) $reservation->id,
            'empresa_id' => (int) $reservation->empresa_id,
            'modelo' => (int) $reservation->modelo,
            'serie' => (int) $reservation->serie,
            'ambiente' => (int) $reservation->ambiente,
            'numero' => (int) $reservation->numero,
            'source_type' => (string) $reservation->source_type,
            'source_id' => (int) $reservation->source_id,
            'status' => (string) $reservation->status,
            'chave' => $reservation->chave,
            'protocolo' => $reservation->protocolo,
        ];
    }
}
