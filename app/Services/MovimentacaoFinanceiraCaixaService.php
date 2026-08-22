<?php

namespace App\Services;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Carbon\Carbon;

class MovimentacaoFinanceiraCaixaService
{
    public function consultar(
        int $empresaId,
        $inicio,
        $fim,
        ?int $filialId = null
    ): array {
        $dataInicial = Carbon::parse($inicio)->toDateString();
        $dataFinal = Carbon::parse($fim)->toDateString();

        $contasPagar = ContaPagar::query()
            ->with(['fornecedor', 'compra.fornecedor', 'categoria'])
            ->where('empresa_id', $empresaId)
            ->where('status', true)
            ->where('valor_pago', '>', 0)
            ->whereNotNull('data_pagamento')
            ->whereBetween('data_pagamento', [$dataInicial, $dataFinal])
            ->when(
                $filialId !== null,
                function ($query) use ($filialId) {
                    return $query->where('filial_id', $filialId);
                },
                function ($query) {
                    return $query->whereNull('filial_id');
                }
            )
            ->orderBy('data_pagamento')
            ->orderBy('id')
            ->get();

        $contasReceber = ContaReceber::query()
            ->with([
                'cliente',
                'venda.cliente',
                'vendaCaixa.cliente',
                'empresa_id_emp_rel',
                'categoria',
            ])
            ->where('empresa_id', $empresaId)
            ->where('status', true)
            ->where('valor_recebido', '>', 0)
            ->whereNotNull('data_recebimento')
            ->whereBetween('data_recebimento', [$dataInicial, $dataFinal])
            ->when(
                $filialId !== null,
                function ($query) use ($filialId) {
                    return $query->where('filial_id', $filialId);
                },
                function ($query) {
                    return $query->whereNull('filial_id');
                }
            )
            ->orderBy('data_recebimento')
            ->orderBy('id')
            ->get();

        $totalPagar = round((float) $contasPagar->sum('valor_pago'), 2);
        $totalReceber = round((float) $contasReceber->sum('valor_recebido'), 2);

        return [
            'inicio' => $dataInicial,
            'fim' => $dataFinal,
            'contas_pagar' => $contasPagar,
            'contas_receber' => $contasReceber,
            'total_pagar' => $totalPagar,
            'total_receber' => $totalReceber,
            'saldo' => round($totalReceber - $totalPagar, 2),
        ];
    }
}