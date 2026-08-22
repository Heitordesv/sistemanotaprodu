<?php

namespace App\Services;

use App\Models\ContaReceber;
use App\Models\ContaReceberPagamento;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ContaReceberPagamentoService
{
    public function registrarMultiplos(ContaReceber $conta, array $pagamentos, string $dataPagamento, ?string $loteUuid = null): ContaReceber
    {
        return DB::transaction(function () use ($conta, $pagamentos, $dataPagamento, $loteUuid) {
            $conta = ContaReceber::whereKey($conta->id)->lockForUpdate()->firstOrFail();

            if ((int) $conta->status === 1 || (float) $conta->valor_recebido >= (float) $conta->valor_integral) {
                throw new RuntimeException('Esta conta já está quitada.');
            }

            if ($loteUuid && ContaReceberPagamento::where('lote_uuid', $loteUuid)->exists()) {
                return $conta->fresh();
            }

            $normalizados = [];
            $totalNovo = 0.0;

            foreach ($pagamentos as $pagamento) {
                $forma = trim((string) ($pagamento['forma_pagamento'] ?? ''));
                $valor = $this->converterValor($pagamento['valor'] ?? 0);
                $observacao = trim((string) ($pagamento['observacao'] ?? ''));

                if ($forma === '' || $valor <= 0) {
                    continue;
                }

                $normalizados[] = [
                    'forma_pagamento' => $forma,
                    'valor' => $valor,
                    'observacao' => $observacao !== '' ? $observacao : null,
                ];
                $totalNovo += $valor;
            }

            if (!$normalizados || $totalNovo <= 0) {
                throw new RuntimeException('Informe pelo menos uma forma de pagamento com valor maior que zero.');
            }

            $recebidoAtual = round((float) $conta->valor_recebido, 2);
            $valorIntegral = round((float) $conta->valor_integral, 2);
            $saldoRestante = round(max(0, $valorIntegral - $recebidoAtual), 2);
            $totalNovo = round($totalNovo, 2);

            if ($totalNovo > $saldoRestante + 0.009) {
                throw new RuntimeException(
                    'O total informado (R$ ' . number_format($totalNovo, 2, ',', '.') .
                    ') é maior que o saldo restante (R$ ' . number_format($saldoRestante, 2, ',', '.') . ').'
                );
            }

            foreach ($normalizados as $pagamento) {
                ContaReceberPagamento::create([
                    'conta_receber_id' => $conta->id,
                    'empresa_id' => $conta->empresa_id,
                    'valor' => $pagamento['valor'],
                    'forma_pagamento' => $pagamento['forma_pagamento'],
                    'data_pagamento' => $dataPagamento,
                    'origem' => 'manual',
                    'provedor' => null,
                    'external_id' => null,
                    'lote_uuid' => $loteUuid,
                    'status' => 'confirmado',
                    'observacao' => $pagamento['observacao'],
                ]);
            }

            $novoRecebido = round($recebidoAtual + $totalNovo, 2);
            $quitado = $novoRecebido >= ($valorIntegral - 0.009);

            $conta->valor_recebido = min($novoRecebido, $valorIntegral);
            $conta->status = $quitado ? 1 : 0;
            $conta->data_recebimento = $dataPagamento;
            $conta->tipo_pagamento = count($normalizados) > 1 ? '99' : $normalizados[0]['forma_pagamento'];
            $conta->save();

            return $conta->fresh();
        });
    }

    private function converterValor($valor): float
    {
        if (is_numeric($valor)) {
            return round((float) $valor, 2);
        }

        $valor = trim((string) $valor);
        $valor = preg_replace('/[^0-9,.-]/', '', $valor);

        if (str_contains($valor, ',') && str_contains($valor, '.')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (str_contains($valor, ',')) {
            $valor = str_replace(',', '.', $valor);
        }

        return round((float) $valor, 2);
    }
}