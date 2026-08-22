<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SistemaAiFinancialContextService
{
    public function build(int $empresaId): string
    {
        $agora = Carbon::now();
        $hoje = $agora->copy()->startOfDay();
        $fimHoje = $agora->copy()->endOfDay();
        $inicioMes = $agora->copy()->startOfMonth();
        $fimMes = $agora->copy()->endOfMonth();

        $dados = [
            'periodo_referencia' => $agora->format('Y-m-d H:i:s'),
            'cadastros' => [
                'clientes' => $this->countTenant('clientes', $empresaId),
                'produtos' => $this->countTenant('produtos', $empresaId),
                'fornecedores' => $this->countTenant('fornecedors', $empresaId),
            ],
            'vendas' => [
                'nfe_mes' => $this->sumPeriod('vendas', 'valor_total', $empresaId, $inicioMes, $fimMes),
                'pdv_mes' => $this->sumPeriod('venda_caixas', 'valor_total', $empresaId, $inicioMes, $fimMes),
                'nfe_hoje' => $this->sumPeriod('vendas', 'valor_total', $empresaId, $hoje, $fimHoje),
                'pdv_hoje' => $this->sumPeriod('venda_caixas', 'valor_total', $empresaId, $hoje, $fimHoje),
                'quantidade_nfe_mes' => $this->countPeriod('vendas', $empresaId, $inicioMes, $fimMes),
                'quantidade_pdv_mes' => $this->countPeriod('venda_caixas', $empresaId, $inicioMes, $fimMes),
                'quantidade_nfe_hoje' => $this->countPeriod('vendas', $empresaId, $hoje, $fimHoje),
                'quantidade_pdv_hoje' => $this->countPeriod('venda_caixas', $empresaId, $hoje, $fimHoje),
                'formas_pagamento_hoje' => $this->formasPagamentoVendasPeriodo($empresaId, $hoje, $fimHoje),
            ],
            'receber' => $this->receber($empresaId, $hoje, $fimHoje),
            'pagar' => $this->pagar($empresaId, $hoje, $fimHoje),
            'compras' => [
                'total_mes' => $this->sumPeriod('compras', 'total', $empresaId, $inicioMes, $fimMes),
                'quantidade_mes' => $this->countPeriod('compras', $empresaId, $inicioMes, $fimMes),
            ],
            'estoque' => $this->estoque($empresaId),
            'ordens_servico' => $this->ordensServico($empresaId, $inicioMes, $fimMes),
        ];

        $dados['vendas']['faturamento_bruto_mes'] = round(
            (float) ($dados['vendas']['nfe_mes'] ?? 0) + (float) ($dados['vendas']['pdv_mes'] ?? 0),
            2
        );
        $dados['vendas']['faturamento_bruto_hoje'] = round(
            (float) ($dados['vendas']['nfe_hoje'] ?? 0) + (float) ($dados['vendas']['pdv_hoje'] ?? 0),
            2
        );

        $dados['relatorio_dia'] = $this->relatorioDia(
            $empresaId,
            $hoje,
            $fimHoje,
            $dados['vendas'],
            $dados['receber'],
            $dados['pagar']
        );

        return "PAINEL FINANCEIRO E GERENCIAL DA EMPRESA\n" .
            json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function receber(int $empresaId, Carbon $inicioDia, Carbon $fimDia): array
    {
        if (!Schema::hasTable('conta_recebers') || !Schema::hasColumn('conta_recebers', 'empresa_id')) {
            return [];
        }

        $base = DB::table('conta_recebers')->where('empresa_id', $empresaId);
        $pendente = (clone $base)->where('status', false);
        $vencido = (clone $pendente)->whereDate('data_vencimento', '<', $inicioDia->toDateString());
        $aVencer = (clone $pendente)->whereDate('data_vencimento', '>=', $inicioDia->toDateString());
        $venceHoje = (clone $pendente)->whereDate('data_vencimento', $inicioDia->toDateString());

        $recebidoMes = (clone $base)
            ->whereNotNull('data_recebimento')
            ->whereBetween('data_recebimento', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);

        $recebidoHoje = (clone $base)
            ->whereNotNull('data_recebimento')
            ->whereBetween('data_recebimento', [$inicioDia, $fimDia]);

        return [
            'total_pendente' => $this->sumExpression($pendente, 'valor_integral', 'valor_recebido'),
            'total_vencido' => $this->sumExpression($vencido, 'valor_integral', 'valor_recebido'),
            'total_a_vencer' => $this->sumExpression($aVencer, 'valor_integral', 'valor_recebido'),
            'vence_hoje_pendente' => $this->sumExpression($venceHoje, 'valor_integral', 'valor_recebido'),
            'quantidade_pendente' => (clone $pendente)->count(),
            'quantidade_vencida' => (clone $vencido)->count(),
            'quantidade_vence_hoje' => (clone $venceHoje)->count(),
            'recebido_no_mes' => $this->sumSafe($recebidoMes, 'valor_recebido'),
            'recebido_hoje' => $this->sumSafe($recebidoHoje, 'valor_recebido'),
            'quantidade_recebimentos_hoje' => (clone $recebidoHoje)->count(),
            'formas_pagamento_recebimentos_hoje' => $this->formasPagamentoContas(
                'conta_recebers',
                'valor_recebido',
                'data_recebimento',
                $empresaId,
                $inicioDia,
                $fimDia
            ),
        ];
    }

    private function pagar(int $empresaId, Carbon $inicioDia, Carbon $fimDia): array
    {
        if (!Schema::hasTable('conta_pagars') || !Schema::hasColumn('conta_pagars', 'empresa_id')) {
            return [];
        }

        $base = DB::table('conta_pagars')->where('empresa_id', $empresaId);
        $pendente = (clone $base)->where('status', false);
        $vencido = (clone $pendente)->whereDate('data_vencimento', '<', $inicioDia->toDateString());
        $aVencer = (clone $pendente)->whereDate('data_vencimento', '>=', $inicioDia->toDateString());
        $venceHoje = (clone $pendente)->whereDate('data_vencimento', $inicioDia->toDateString());

        $pagoMes = (clone $base)
            ->whereNotNull('data_pagamento')
            ->whereBetween('data_pagamento', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);

        $pagoHoje = (clone $base)
            ->whereNotNull('data_pagamento')
            ->whereBetween('data_pagamento', [$inicioDia, $fimDia]);

        return [
            'total_pendente' => $this->sumExpression($pendente, 'valor_integral', 'valor_pago'),
            'total_vencido' => $this->sumExpression($vencido, 'valor_integral', 'valor_pago'),
            'total_a_vencer' => $this->sumExpression($aVencer, 'valor_integral', 'valor_pago'),
            'vence_hoje_pendente' => $this->sumExpression($venceHoje, 'valor_integral', 'valor_pago'),
            'quantidade_pendente' => (clone $pendente)->count(),
            'quantidade_vencida' => (clone $vencido)->count(),
            'quantidade_vence_hoje' => (clone $venceHoje)->count(),
            'pago_no_mes' => $this->sumSafe($pagoMes, 'valor_pago'),
            'pago_hoje' => $this->sumSafe($pagoHoje, 'valor_pago'),
            'quantidade_pagamentos_hoje' => (clone $pagoHoje)->count(),
            'formas_pagamento_pagamentos_hoje' => $this->formasPagamentoContas(
                'conta_pagars',
                'valor_pago',
                'data_pagamento',
                $empresaId,
                $inicioDia,
                $fimDia
            ),
        ];
    }

    private function relatorioDia(
        int $empresaId,
        Carbon $inicioDia,
        Carbon $fimDia,
        array $vendas,
        array $receber,
        array $pagar
    ): array {
        $suprimentos = $this->sumPeriodByUser('suprimento_caixas', 'valor', $empresaId, $inicioDia, $fimDia);
        $sangrias = $this->sumPeriodByUser('sangria_caixas', 'valor', $empresaId, $inicioDia, $fimDia);
        $recebidoHoje = (float) ($receber['recebido_hoje'] ?? 0);
        $pagoHoje = (float) ($pagar['pago_hoje'] ?? 0);
        $faturamentoHoje = (float) ($vendas['faturamento_bruto_hoje'] ?? 0);

        return [
            'data' => $inicioDia->toDateString(),
            'resumo' => [
                'faturamento_bruto_hoje' => round($faturamentoHoje, 2),
                'recebimentos_de_contas_hoje' => round($recebidoHoje, 2),
                'pagamentos_de_contas_hoje' => round($pagoHoje, 2),
                'suprimentos_de_caixa_hoje' => round($suprimentos, 2),
                'sangrias_de_caixa_hoje' => round($sangrias, 2),
                'saldo_realizado_contas_receber_menos_contas_pagar' => round($recebidoHoje - $pagoHoje, 2),
            ],
            'caixas' => $this->caixasDoDia($empresaId, $inicioDia, $fimDia),
            'formas_pagamento_vendas' => $vendas['formas_pagamento_hoje'] ?? [],
            'formas_pagamento_recebimentos' => $receber['formas_pagamento_recebimentos_hoje'] ?? [],
            'formas_pagamento_pagamentos' => $pagar['formas_pagamento_pagamentos_hoje'] ?? [],
            'contas_receber' => [
                'recebido_hoje' => round($recebidoHoje, 2),
                'vence_hoje_pendente' => (float) ($receber['vence_hoje_pendente'] ?? 0),
                'vencido_total' => (float) ($receber['total_vencido'] ?? 0),
                'pendente_total' => (float) ($receber['total_pendente'] ?? 0),
            ],
            'contas_pagar' => [
                'pago_hoje' => round($pagoHoje, 2),
                'vence_hoje_pendente' => (float) ($pagar['vence_hoje_pendente'] ?? 0),
                'vencido_total' => (float) ($pagar['total_vencido'] ?? 0),
                'pendente_total' => (float) ($pagar['total_pendente'] ?? 0),
            ],
            'observacoes_contabeis' => [
                'faturamento_nao_e_lucro',
                'suprimento_de_caixa_e_movimentacao_interna_e_nao_receita',
                'sangria_de_caixa_e_retirada_de_numerario_e_nao_despesa_por_si_so',
                'recebimento_de_conta_pode_ser_de_venda_realizada_em_data_anterior',
                'nao_somar_faturamento_e_recebimentos_de_contas_sem_verificar_origem_para_evitar_dupla_contagem',
            ],
        ];
    }

    private function caixasDoDia(int $empresaId, Carbon $inicioDia, Carbon $fimDia): array
    {
        if (!Schema::hasTable('abertura_caixas') || !Schema::hasColumn('abertura_caixas', 'empresa_id')) {
            return [];
        }

        $query = DB::table('abertura_caixas')
            ->where('empresa_id', $empresaId)
            ->where('created_at', '<=', $fimDia)
            ->where(function ($q) use ($inicioDia) {
                $q->where('status', 0)
                    ->orWhere('updated_at', '>=', $inicioDia);
            })
            ->orderBy('created_at');

        $aberturas = $query->get();
        if ($aberturas->isEmpty()) {
            return [];
        }

        $usuarios = [];
        if (Schema::hasTable('usuarios') && Schema::hasColumn('usuarios', 'nome')) {
            $usuarios = DB::table('usuarios')
                ->whereIn('id', $aberturas->pluck('usuario_id')->filter()->unique()->values())
                ->pluck('nome', 'id')
                ->all();
        }

        $resultado = [];
        $agora = Carbon::now();

        foreach ($aberturas as $abertura) {
            $abertoEm = Carbon::parse($abertura->created_at);
            $fechadoEm = ((int) $abertura->status === 0)
                ? $agora->copy()
                : Carbon::parse($abertura->updated_at);

            $inicio = $abertoEm->greaterThan($inicioDia) ? $abertoEm : $inicioDia->copy();
            $fim = $fechadoEm->lessThan($fimDia) ? $fechadoEm : $fimDia->copy();

            if ($fim->lt($inicio)) {
                continue;
            }

            $usuarioId = (int) $abertura->usuario_id;
            $pdvTotal = $this->sumPeriodByUser('venda_caixas', 'valor_total', $empresaId, $inicio, $fim, $usuarioId);
            $nfeTotal = $this->sumPeriodByUser('vendas', 'valor_total', $empresaId, $inicio, $fim, $usuarioId);
            $suprimentos = $this->sumPeriodByUser('suprimento_caixas', 'valor', $empresaId, $inicio, $fim, $usuarioId);
            $sangrias = $this->sumPeriodByUser('sangria_caixas', 'valor', $empresaId, $inicio, $fim, $usuarioId);
            $formas = $this->formasPagamentoVendasPeriodo($empresaId, $inicio, $fim, $usuarioId);
            $dinheiroVendas = $this->totalFormaPagamento($formas, '01');
            $valorAbertura = round((float) ($abertura->valor ?? 0), 2);

            $resultado[] = [
                'caixa_id' => (int) $abertura->id,
                'usuario_id' => $usuarioId,
                'usuario' => $usuarios[$usuarioId] ?? ('Usuário #' . $usuarioId),
                'filial_id' => isset($abertura->filial_id) ? $abertura->filial_id : null,
                'status' => ((int) $abertura->status === 0) ? 'aberto' : 'fechado',
                'aberto_em' => $abertoEm->format('Y-m-d H:i:s'),
                'fechado_em' => ((int) $abertura->status === 0) ? null : $fechadoEm->format('Y-m-d H:i:s'),
                'valor_abertura' => $valorAbertura,
                'vendas_pdv' => round($pdvTotal, 2),
                'vendas_nfe' => round($nfeTotal, 2),
                'total_movimentado_em_vendas' => round($pdvTotal + $nfeTotal, 2),
                'formas_pagamento' => $formas,
                'suprimentos' => round($suprimentos, 2),
                'sangrias' => round($sangrias, 2),
                'dinheiro_estimado_no_caixa' => round($valorAbertura + $dinheiroVendas + $suprimentos - $sangrias, 2),
                'dinheiro_informado_no_fechamento' => isset($abertura->valor_dinheiro_caixa)
                    ? round((float) $abertura->valor_dinheiro_caixa, 2)
                    : null,
            ];
        }

        return $resultado;
    }

    private function formasPagamentoVendasPeriodo(
        int $empresaId,
        Carbon $inicio,
        Carbon $fim,
        ?int $usuarioId = null
    ): array {
        $totais = [];

        if (Schema::hasTable('vendas') && Schema::hasColumn('vendas', 'empresa_id') && Schema::hasColumn('vendas', 'valor_total')) {
            $query = DB::table('vendas')
                ->where('empresa_id', $empresaId)
                ->whereBetween('created_at', [$inicio, $fim]);

            if ($usuarioId !== null && Schema::hasColumn('vendas', 'usuario_id')) {
                $query->where('usuario_id', $usuarioId);
            }

            $this->excludeCancelled($query, 'vendas');

            $tipoColumn = Schema::hasColumn('vendas', 'tipo_pagamento') ? 'tipo_pagamento' : 'forma_pagamento';
            $rows = $query
                ->select($tipoColumn, DB::raw('COUNT(*) as quantidade'), DB::raw('COALESCE(SUM(valor_total), 0) as total'))
                ->groupBy($tipoColumn)
                ->get();

            foreach ($rows as $row) {
                $this->addFormaPagamento(
                    $totais,
                    $row->{$tipoColumn} ?? null,
                    (float) $row->total,
                    (int) $row->quantidade,
                    'NFe'
                );
            }
        }

        if (Schema::hasTable('venda_caixas') && Schema::hasColumn('venda_caixas', 'empresa_id') && Schema::hasColumn('venda_caixas', 'valor_total')) {
            $faturaDisponivel = Schema::hasTable('fatura_frente_caixas')
                && Schema::hasColumn('fatura_frente_caixas', 'venda_caixa_id')
                && Schema::hasColumn('fatura_frente_caixas', 'forma_pagamento')
                && Schema::hasColumn('fatura_frente_caixas', 'valor');

            if ($faturaDisponivel) {
                $faturas = DB::table('fatura_frente_caixas as f')
                    ->join('venda_caixas as v', 'v.id', '=', 'f.venda_caixa_id')
                    ->where('v.empresa_id', $empresaId)
                    ->whereBetween('v.created_at', [$inicio, $fim]);

                if ($usuarioId !== null && Schema::hasColumn('venda_caixas', 'usuario_id')) {
                    $faturas->where('v.usuario_id', $usuarioId);
                }
                $this->excludeCancelledAliased($faturas, 'venda_caixas', 'v');
                $this->excludeDraftPdvAliased($faturas, 'v');

                $rows = $faturas
                    ->select('f.forma_pagamento', DB::raw('COUNT(*) as quantidade'), DB::raw('COALESCE(SUM(f.valor), 0) as total'))
                    ->groupBy('f.forma_pagamento')
                    ->get();

                foreach ($rows as $row) {
                    $this->addFormaPagamento(
                        $totais,
                        $row->forma_pagamento,
                        (float) $row->total,
                        (int) $row->quantidade,
                        'PDV'
                    );
                }
            }

            $fallback = DB::table('venda_caixas as v')
                ->where('v.empresa_id', $empresaId)
                ->whereBetween('v.created_at', [$inicio, $fim]);

            if ($usuarioId !== null && Schema::hasColumn('venda_caixas', 'usuario_id')) {
                $fallback->where('v.usuario_id', $usuarioId);
            }

            $this->excludeCancelledAliased($fallback, 'venda_caixas', 'v');
            $this->excludeDraftPdvAliased($fallback, 'v');

            if ($faturaDisponivel) {
                $fallback->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('fatura_frente_caixas as f')
                        ->whereColumn('f.venda_caixa_id', 'v.id');
                });
            }

            $tipoColumn = Schema::hasColumn('venda_caixas', 'tipo_pagamento') ? 'tipo_pagamento' : 'forma_pagamento';
            $rows = $fallback
                ->select('v.' . $tipoColumn, DB::raw('COUNT(*) as quantidade'), DB::raw('COALESCE(SUM(v.valor_total), 0) as total'))
                ->groupBy('v.' . $tipoColumn)
                ->get();

            foreach ($rows as $row) {
                $this->addFormaPagamento(
                    $totais,
                    $row->{$tipoColumn} ?? null,
                    (float) $row->total,
                    (int) $row->quantidade,
                    'PDV'
                );
            }
        }

        $resultado = array_values($totais);
        usort($resultado, fn ($a, $b) => $b['total'] <=> $a['total']);

        return $resultado;
    }

    private function formasPagamentoContas(
        string $table,
        string $valueColumn,
        string $dateColumn,
        int $empresaId,
        Carbon $inicio,
        Carbon $fim
    ): array {
        if (!Schema::hasTable($table)
            || !Schema::hasColumn($table, 'empresa_id')
            || !Schema::hasColumn($table, $valueColumn)
            || !Schema::hasColumn($table, $dateColumn)
        ) {
            return [];
        }

        $tipoColumn = Schema::hasColumn($table, 'tipo_pagamento') ? 'tipo_pagamento' : null;
        if ($tipoColumn === null) {
            return [[
                'codigo' => null,
                'forma' => 'Não informado',
                'total' => round((float) DB::table($table)
                    ->where('empresa_id', $empresaId)
                    ->whereBetween($dateColumn, [$inicio, $fim])
                    ->sum($valueColumn), 2),
                'quantidade' => DB::table($table)
                    ->where('empresa_id', $empresaId)
                    ->whereBetween($dateColumn, [$inicio, $fim])
                    ->count(),
            ]];
        }

        return DB::table($table)
            ->where('empresa_id', $empresaId)
            ->whereBetween($dateColumn, [$inicio, $fim])
            ->select($tipoColumn, DB::raw('COUNT(*) as quantidade'), DB::raw('COALESCE(SUM(' . $valueColumn . '), 0) as total'))
            ->groupBy($tipoColumn)
            ->get()
            ->map(function ($row) use ($tipoColumn) {
                $codigo = $row->{$tipoColumn} ?? null;

                return [
                    'codigo' => $codigo,
                    'forma' => $this->paymentLabel($codigo),
                    'total' => round((float) $row->total, 2),
                    'quantidade' => (int) $row->quantidade,
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    private function addFormaPagamento(array &$totais, $codigo, float $total, int $quantidade, string $origem): void
    {
        $chave = trim((string) ($codigo ?? ''));
        $chave = $chave === '' ? 'nao_informado' : $chave;

        if (!isset($totais[$chave])) {
            $totais[$chave] = [
                'codigo' => $codigo,
                'forma' => $this->paymentLabel($codigo),
                'total' => 0.0,
                'quantidade_lancamentos' => 0,
                'origens' => [],
            ];
        }

        $totais[$chave]['total'] = round($totais[$chave]['total'] + $total, 2);
        $totais[$chave]['quantidade_lancamentos'] += $quantidade;
        if (!in_array($origem, $totais[$chave]['origens'], true)) {
            $totais[$chave]['origens'][] = $origem;
        }
    }

    private function totalFormaPagamento(array $formas, string $codigo): float
    {
        foreach ($formas as $forma) {
            $codigoForma = (string) ($forma['codigo'] ?? '');
            $nomeForma = mb_strtolower((string) ($forma['forma'] ?? ''));
            if ($codigoForma === $codigo || ($codigo === '01' && $nomeForma === 'dinheiro')) {
                return (float) ($forma['total'] ?? 0);
            }
        }

        return 0.0;
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

        if ($codigo === '') {
            return 'Não informado';
        }

        return $map[$codigo] ?? $codigo;
    }

    private function estoque(int $empresaId): array
    {
        if (!Schema::hasTable('estoques') || !Schema::hasColumn('estoques', 'empresa_id')) {
            return [];
        }

        $base = DB::table('estoques')->where('empresa_id', $empresaId);

        return [
            'quantidade_total' => Schema::hasColumn('estoques', 'quantidade')
                ? round((float) (clone $base)->sum('quantidade'), 3)
                : null,
            'registros_estoque' => (clone $base)->count(),
        ];
    }

    private function ordensServico(int $empresaId, Carbon $inicio, Carbon $fim): array
    {
        if (!Schema::hasTable('ordem_servicos') || !Schema::hasColumn('ordem_servicos', 'empresa_id')) {
            return [];
        }

        $base = DB::table('ordem_servicos')->where('empresa_id', $empresaId);
        $mes = Schema::hasColumn('ordem_servicos', 'created_at')
            ? (clone $base)->whereBetween('created_at', [$inicio, $fim])
            : clone $base;

        return [
            'quantidade_mes' => (clone $mes)->count(),
            'valor_mes' => Schema::hasColumn('ordem_servicos', 'valor')
                ? round((float) (clone $mes)->sum('valor'), 2)
                : null,
            'valor_pago_mes' => Schema::hasColumn('ordem_servicos', 'valor_pago')
                ? round((float) (clone $mes)->sum('valor_pago'), 2)
                : null,
        ];
    }

    private function countTenant(string $table, int $empresaId): ?int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'empresa_id')) {
            return null;
        }

        return DB::table($table)->where('empresa_id', $empresaId)->count();
    }

    private function sumPeriod(string $table, string $column, int $empresaId, Carbon $inicio, Carbon $fim): ?float
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'empresa_id') || !Schema::hasColumn($table, $column)) {
            return null;
        }

        $query = DB::table($table)->where('empresa_id', $empresaId);
        if (Schema::hasColumn($table, 'created_at')) {
            $query->whereBetween('created_at', [$inicio, $fim]);
        }
        $this->excludeCancelled($query, $table);
        $this->excludeDraftPdv($query, $table);

        return round((float) $query->sum($column), 2);
    }

    private function sumPeriodByUser(
        string $table,
        string $column,
        int $empresaId,
        Carbon $inicio,
        Carbon $fim,
        ?int $usuarioId = null
    ): float {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'empresa_id') || !Schema::hasColumn($table, $column)) {
            return 0.0;
        }

        $query = DB::table($table)->where('empresa_id', $empresaId);
        if (Schema::hasColumn($table, 'created_at')) {
            $query->whereBetween('created_at', [$inicio, $fim]);
        }
        if ($usuarioId !== null && Schema::hasColumn($table, 'usuario_id')) {
            $query->where('usuario_id', $usuarioId);
        }

        $this->excludeCancelled($query, $table);
        $this->excludeDraftPdv($query, $table);

        return round((float) $query->sum($column), 2);
    }

    private function countPeriod(string $table, int $empresaId, Carbon $inicio, Carbon $fim): ?int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'empresa_id')) {
            return null;
        }

        $query = DB::table($table)->where('empresa_id', $empresaId);
        if (Schema::hasColumn($table, 'created_at')) {
            $query->whereBetween('created_at', [$inicio, $fim]);
        }
        $this->excludeCancelled($query, $table);
        $this->excludeDraftPdv($query, $table);

        return $query->count();
    }

    private function excludeCancelled(Builder $query, string $table): void
    {
        if (Schema::hasColumn($table, 'estado_emissao')) {
            $query->where(function ($q) {
                $q->whereNull('estado_emissao')
                    ->orWhereRaw('LOWER(estado_emissao) <> ?', ['cancelado']);
            });
        }
    }

    private function excludeCancelledAliased(Builder $query, string $table, string $alias): void
    {
        if (Schema::hasColumn($table, 'estado_emissao')) {
            $query->where(function ($q) use ($alias) {
                $q->whereNull($alias . '.estado_emissao')
                    ->orWhereRaw('LOWER(' . $alias . '.estado_emissao) <> ?', ['cancelado']);
            });
        }
    }

    private function excludeDraftPdv(Builder $query, string $table): void
    {
        if ($table === 'venda_caixas') {
            if (Schema::hasColumn($table, 'rascunho')) {
                $query->where('rascunho', 0);
            }
            if (Schema::hasColumn($table, 'consignado')) {
                $query->where('consignado', 0);
            }
        }
    }

    private function excludeDraftPdvAliased(Builder $query, string $alias): void
    {
        if (Schema::hasColumn('venda_caixas', 'rascunho')) {
            $query->where($alias . '.rascunho', 0);
        }
        if (Schema::hasColumn('venda_caixas', 'consignado')) {
            $query->where($alias . '.consignado', 0);
        }
    }

    private function sumSafe(Builder $query, string $column): float
    {
        return round((float) (clone $query)->sum($column), 2);
    }

    private function sumExpression(Builder $query, string $totalColumn, string $paidColumn): float
    {
        return round((float) (clone $query)->selectRaw(
            'COALESCE(SUM(COALESCE(' . $totalColumn . ', 0) - COALESCE(' . $paidColumn . ', 0)), 0) AS total'
        )->value('total'), 2);
    }
}