<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SistemaAiBusinessFlowContextService
{
    public function build(int $empresaId): string
    {
        $inicio = Carbon::now()->startOfDay();
        $fim = Carbon::now()->endOfDay();

        $despesas = $this->despesasPagas($empresaId, $inicio, $fim);
        $receitas = $this->receitasRecebidas($empresaId, $inicio, $fim);

        $totalDespesas = array_sum(array_column($despesas['por_categoria'], 'total'));
        $totalReceitas = array_sum(array_column($receitas['por_categoria'], 'total'));

        $dados = [
            'data' => $inicio->toDateString(),
            'fluxo_realizado_do_dia' => [
                'entradas_por_contas_recebidas' => round($totalReceitas, 2),
                'saidas_por_contas_pagas' => round($totalDespesas, 2),
                'saldo_realizado_recebimentos_menos_pagamentos' => round($totalReceitas - $totalDespesas, 2),
            ],
            'despesas_pagas_hoje' => $despesas,
            'receitas_recebidas_hoje' => $receitas,
            'regras_de_interpretacao' => [
                'conta_paga_e_saida_financeira_realizada',
                'conta_pendente_nao_deve_ser_tratada_como_dinheiro_que_ja_saiu',
                'conta_recebida_e_entrada_financeira_realizada',
                'venda_do_dia_e_faturamento_nao_devem_ser_somados_automaticamente_a_contas_recebidas_para_evitar_dupla_contagem',
                'sangria_e_retirada_de_caixa_nao_significa_despesa_sem_vinculo_com_uma_conta_paga',
                'suprimento_e_entrada_fisica_no_caixa_nao_significa_receita_nova',
                'categorias_devem_ser_usadas_para_explicar_para_onde_o_dinheiro_foi_e_de_onde_veio',
            ],
        ];

        return "FLUXO FINANCEIRO E LEITURA DE NEGÓCIO DO DIA\n" .
            json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function despesasPagas(int $empresaId, Carbon $inicio, Carbon $fim): array
    {
        if (!$this->validFinancialTable('conta_pagars', 'data_pagamento', 'valor_pago')) {
            return $this->emptyGroup();
        }

        $base = DB::table('conta_pagars as cp')
            ->where('cp.empresa_id', $empresaId)
            ->whereNotNull('cp.data_pagamento')
            ->whereBetween('cp.data_pagamento', [$inicio, $fim])
            ->where('cp.valor_pago', '>', 0);

        $total = round((float) (clone $base)->sum('cp.valor_pago'), 2);
        $porCategoria = $this->groupByCategory($base, 'cp', 'valor_pago', $total, $empresaId, 'pagar');
        $detalhes = $this->expenseDetails($base, $empresaId);

        return [
            'total' => $total,
            'quantidade' => (clone $base)->count(),
            'por_categoria' => $porCategoria,
            'por_forma_pagamento' => $this->groupByPayment($base, 'cp', 'valor_pago'),
            'lancamentos' => $detalhes,
            'maior_categoria' => $porCategoria[0] ?? null,
            'maior_saida' => $detalhes[0] ?? null,
        ];
    }

    private function receitasRecebidas(int $empresaId, Carbon $inicio, Carbon $fim): array
    {
        if (!$this->validFinancialTable('conta_recebers', 'data_recebimento', 'valor_recebido')) {
            return $this->emptyGroup();
        }

        $base = DB::table('conta_recebers as cr')
            ->where('cr.empresa_id', $empresaId)
            ->whereNotNull('cr.data_recebimento')
            ->whereBetween('cr.data_recebimento', [$inicio, $fim])
            ->where('cr.valor_recebido', '>', 0);

        $total = round((float) (clone $base)->sum('cr.valor_recebido'), 2);
        $porCategoria = $this->groupByCategory($base, 'cr', 'valor_recebido', $total, $empresaId, 'receber');
        $detalhes = $this->incomeDetails($base, $empresaId);

        return [
            'total' => $total,
            'quantidade' => (clone $base)->count(),
            'por_categoria' => $porCategoria,
            'por_forma_pagamento' => $this->groupByPayment($base, 'cr', 'valor_recebido'),
            'lancamentos' => $detalhes,
            'maior_categoria' => $porCategoria[0] ?? null,
            'maior_entrada' => $detalhes[0] ?? null,
        ];
    }

    private function groupByCategory(
        Builder $base,
        string $alias,
        string $valueColumn,
        float $total,
        int $empresaId,
        string $tipo
    ): array {
        $query = clone $base;

        if (Schema::hasTable('categoria_contas')
            && Schema::hasColumn('categoria_contas', 'nome')
            && Schema::hasColumn($alias === 'cp' ? 'conta_pagars' : 'conta_recebers', 'categoria_id')
        ) {
            $query->leftJoin('categoria_contas as cat', function ($join) use ($alias, $empresaId, $tipo) {
                $join->on('cat.id', '=', $alias . '.categoria_id')
                    ->where('cat.empresa_id', '=', $empresaId)
                    ->where('cat.tipo', '=', $tipo);
            });

            $rows = $query
                ->selectRaw("COALESCE(cat.nome, 'Sem categoria') as categoria")
                ->selectRaw('COUNT(*) as quantidade')
                ->selectRaw('COALESCE(SUM(' . $alias . '.' . $valueColumn . '), 0) as total')
                ->groupBy('cat.id', 'cat.nome')
                ->orderByDesc('total')
                ->get();
        } else {
            $rows = collect([(object) [
                'categoria' => 'Sem categoria',
                'quantidade' => (clone $base)->count(),
                'total' => (clone $base)->sum($alias . '.' . $valueColumn),
            ]]);
        }

        return $rows->map(function ($row) use ($total) {
            $valor = round((float) $row->total, 2);

            return [
                'categoria' => $row->categoria ?: 'Sem categoria',
                'total' => $valor,
                'quantidade' => (int) $row->quantidade,
                'percentual_do_total' => $total > 0 ? round(($valor / $total) * 100, 2) : 0,
            ];
        })->values()->all();
    }

    private function groupByPayment(Builder $base, string $alias, string $valueColumn): array
    {
        $table = $alias === 'cp' ? 'conta_pagars' : 'conta_recebers';
        if (!Schema::hasColumn($table, 'tipo_pagamento')) {
            return [];
        }

        return (clone $base)
            ->select($alias . '.tipo_pagamento')
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(' . $alias . '.' . $valueColumn . '), 0) as total')
            ->groupBy($alias . '.tipo_pagamento')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'codigo' => $row->tipo_pagamento,
                'forma' => $this->paymentLabel($row->tipo_pagamento),
                'total' => round((float) $row->total, 2),
                'quantidade' => (int) $row->quantidade,
            ])
            ->values()
            ->all();
    }

    private function expenseDetails(Builder $base, int $empresaId): array
    {
        $query = clone $base;

        if (Schema::hasTable('categoria_contas') && Schema::hasColumn('conta_pagars', 'categoria_id')) {
            $query->leftJoin('categoria_contas as cat', function ($join) use ($empresaId) {
                $join->on('cat.id', '=', 'cp.categoria_id')
                    ->where('cat.empresa_id', '=', $empresaId)
                    ->where('cat.tipo', '=', 'pagar');
            });
        }

        if (Schema::hasTable('fornecedors') && Schema::hasColumn('conta_pagars', 'fornecedor_id')) {
            $query->leftJoin('fornecedors as f', 'f.id', '=', 'cp.fornecedor_id');
        }

        $select = [
            'cp.id',
            'cp.valor_pago',
            'cp.data_pagamento',
        ];

        foreach (['referencia', 'observacao', 'tipo_pagamento', 'categoria_id', 'fornecedor_id'] as $column) {
            if (Schema::hasColumn('conta_pagars', $column)) {
                $select[] = 'cp.' . $column;
            }
        }

        if (Schema::hasTable('categoria_contas')) {
            $select[] = DB::raw("COALESCE(cat.nome, 'Sem categoria') as categoria_nome");
        }
        if (Schema::hasTable('fornecedors')) {
            $nomeFornecedor = Schema::hasColumn('fornecedors', 'razao_social')
                ? "COALESCE(f.razao_social, f.nome_fantasia, 'Não informado')"
                : (Schema::hasColumn('fornecedors', 'nome_fantasia') ? "COALESCE(f.nome_fantasia, 'Não informado')" : "'Não informado'");
            $select[] = DB::raw($nomeFornecedor . ' as fornecedor_nome');
        }

        return $query
            ->select($select)
            ->orderByDesc('cp.valor_pago')
            ->limit(40)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'referencia' => $row->referencia ?? 'Sem referência',
                    'descricao' => $row->observacao ?? null,
                    'fornecedor' => $row->fornecedor_nome ?? 'Não informado',
                    'categoria' => $row->categoria_nome ?? 'Sem categoria',
                    'valor_pago' => round((float) $row->valor_pago, 2),
                    'forma_pagamento' => $this->paymentLabel($row->tipo_pagamento ?? null),
                    'data_pagamento' => $row->data_pagamento,
                ];
            })
            ->values()
            ->all();
    }

    private function incomeDetails(Builder $base, int $empresaId): array
    {
        $query = clone $base;

        if (Schema::hasTable('categoria_contas') && Schema::hasColumn('conta_recebers', 'categoria_id')) {
            $query->leftJoin('categoria_contas as cat', function ($join) use ($empresaId) {
                $join->on('cat.id', '=', 'cr.categoria_id')
                    ->where('cat.empresa_id', '=', $empresaId)
                    ->where('cat.tipo', '=', 'receber');
            });
        }

        if (Schema::hasTable('clientes') && Schema::hasColumn('conta_recebers', 'cliente_id')) {
            $query->leftJoin('clientes as c', 'c.id', '=', 'cr.cliente_id');
        }

        $select = ['cr.id', 'cr.valor_recebido', 'cr.data_recebimento'];
        foreach (['referencia', 'observacao', 'tipo_pagamento', 'categoria_id', 'cliente_id'] as $column) {
            if (Schema::hasColumn('conta_recebers', $column)) {
                $select[] = 'cr.' . $column;
            }
        }

        if (Schema::hasTable('categoria_contas')) {
            $select[] = DB::raw("COALESCE(cat.nome, 'Sem categoria') as categoria_nome");
        }
        if (Schema::hasTable('clientes')) {
            $nomeCliente = Schema::hasColumn('clientes', 'razao_social')
                ? "COALESCE(c.razao_social, c.nome_fantasia, 'Não informado')"
                : (Schema::hasColumn('clientes', 'nome_fantasia') ? "COALESCE(c.nome_fantasia, 'Não informado')" : "'Não informado'");
            $select[] = DB::raw($nomeCliente . ' as cliente_nome');
        }

        return $query
            ->select($select)
            ->orderByDesc('cr.valor_recebido')
            ->limit(40)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'referencia' => $row->referencia ?? 'Sem referência',
                    'descricao' => $row->observacao ?? null,
                    'cliente' => $row->cliente_nome ?? 'Não informado',
                    'categoria' => $row->categoria_nome ?? 'Sem categoria',
                    'valor_recebido' => round((float) $row->valor_recebido, 2),
                    'forma_pagamento' => $this->paymentLabel($row->tipo_pagamento ?? null),
                    'data_recebimento' => $row->data_recebimento,
                ];
            })
            ->values()
            ->all();
    }

    private function validFinancialTable(string $table, string $dateColumn, string $valueColumn): bool
    {
        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'empresa_id')
            && Schema::hasColumn($table, $dateColumn)
            && Schema::hasColumn($table, $valueColumn);
    }

    private function emptyGroup(): array
    {
        return [
            'total' => 0,
            'quantidade' => 0,
            'por_categoria' => [],
            'por_forma_pagamento' => [],
            'lancamentos' => [],
            'maior_categoria' => null,
        ];
    }

    private function paymentLabel($codigo): string
    {
        $codigo = trim((string) ($codigo ?? ''));
        $map = [
            '01' => 'Dinheiro',
            '02' => 'Cheque',
            '03' => 'Cartão de Crédito',
            '04' => 'Cartão de Débito',
            '05' => 'Crédito Loja',
            '06' => 'Crediário',
            '10' => 'Vale Alimentação',
            '11' => 'Vale Refeição',
            '12' => 'Vale Presente',
            '13' => 'Vale Combustível',
            '14' => 'Duplicata Mercantil',
            '15' => 'Boleto Bancário',
            '16' => 'Depósito Bancário',
            '17' => 'PIX',
            '19' => 'PIX QR Code',
            '90' => 'Sem pagamento',
            '99' => 'Outros',
        ];

        return $codigo === '' ? 'Não informado' : ($map[$codigo] ?? $codigo);
    }
}