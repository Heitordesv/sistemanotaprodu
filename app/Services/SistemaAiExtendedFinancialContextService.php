<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SistemaAiExtendedFinancialContextService extends SistemaAiFinancialContextService
{
    public function build(int $empresaId): string
    {
        $contextoBase = parent::build($empresaId);
        $agora = Carbon::now();
        $inicioHoje = $agora->copy()->startOfDay();
        $fimHoje = $agora->copy()->endOfDay();
        $inicio7Dias = $inicioHoje->copy()->subDays(6);
        $inicioMes = $agora->copy()->startOfMonth()->startOfDay();
        $fimMes = $agora->copy()->endOfMonth()->endOfDay();

        $dados = [
            'periodo_referencia' => $agora->format('Y-m-d H:i:s'),
            'filtros_disponiveis_para_consulta' => [
                'periodos_rapidos' => ['hoje', 'ontem', 'ultimos_7_dias', 'mes_atual'],
                'dimensoes' => [
                    'data',
                    'intervalo_de_datas',
                    'usuario_operador',
                    'forma_de_pagamento',
                    'status_do_caixa',
                    'filial',
                    'caixa_id',
                ],
                'observacao' => 'A Pesquisa IA deve combinar estes filtros somente com dados pertencentes ao empresa_id da sessão.',
            ],
            'hoje' => $this->resumoPeriodo($empresaId, $inicioHoje, $fimHoje),
            'ontem' => $this->resumoPeriodo(
                $empresaId,
                $inicioHoje->copy()->subDay(),
                $fimHoje->copy()->subDay()
            ),
            'ultimos_7_dias' => [
                'inicio' => $inicio7Dias->toDateString(),
                'fim' => $fimHoje->toDateString(),
                'resumo' => $this->resumoPeriodo($empresaId, $inicio7Dias, $fimHoje),
                'por_dia' => $this->serieDiaria($empresaId, $inicio7Dias, $fimHoje),
                'por_operador' => $this->rankingOperadores($empresaId, $inicio7Dias, $fimHoje),
                'formas_pagamento' => $this->formasPagamentoPeriodo($empresaId, $inicio7Dias, $fimHoje),
                'caixas' => $this->caixasPeriodo($empresaId, $inicio7Dias, $fimHoje, 80),
            ],
            'mes_atual' => [
                'inicio' => $inicioMes->toDateString(),
                'fim' => $fimMes->toDateString(),
                'resumo' => $this->resumoPeriodo($empresaId, $inicioMes, $fimMes),
                'por_dia' => $this->serieDiaria($empresaId, $inicioMes, $fimHoje),
                'por_operador' => $this->rankingOperadores($empresaId, $inicioMes, $fimHoje),
                'formas_pagamento' => $this->formasPagamentoPeriodo($empresaId, $inicioMes, $fimHoje),
                'melhor_dia' => $this->melhorDia($empresaId, $inicioMes, $fimHoje),
            ],
            'regras_de_interpretacao' => [
                'vendas_pdv' => 'Faturamento registrado no frente de caixa; não significa necessariamente dinheiro recebido no mesmo instante.',
                'suprimento' => 'Entrada física de numerário no caixa; não deve ser tratada automaticamente como receita.',
                'sangria' => 'Retirada física de numerário do caixa; não deve ser tratada automaticamente como despesa.',
                'ticket_medio' => 'Total de vendas dividido pela quantidade de vendas válidas no período.',
                'canceladas_rascunhos_consignadas' => 'Quando as colunas existem, registros cancelados, rascunhos e consignações são excluídos dos totais gerenciais.',
            ],
        ];

        return $contextoBase
            . "\n\nHISTÓRICO GERENCIAL DE CAIXAS PARA A PESQUISA IA\n"
            . json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function resumoPeriodo(int $empresaId, Carbon $inicio, Carbon $fim): array
    {
        $totalPdv = $this->sumVendasPdv($empresaId, $inicio, $fim);
        $quantidade = $this->countVendasPdv($empresaId, $inicio, $fim);
        $suprimentos = $this->sumMovimento('suprimento_caixas', $empresaId, $inicio, $fim);
        $sangrias = $this->sumMovimento('sangria_caixas', $empresaId, $inicio, $fim);
        $caixas = $this->countCaixas($empresaId, $inicio, $fim);

        return [
            'inicio' => $inicio->format('Y-m-d H:i:s'),
            'fim' => $fim->format('Y-m-d H:i:s'),
            'vendas_pdv_total' => round($totalPdv, 2),
            'quantidade_vendas_pdv' => $quantidade,
            'ticket_medio_pdv' => $quantidade > 0 ? round($totalPdv / $quantidade, 2) : 0.0,
            'suprimentos' => round($suprimentos, 2),
            'sangrias' => round($sangrias, 2),
            'saldo_movimentacao_fisica_caixa' => round($suprimentos - $sangrias, 2),
            'quantidade_caixas' => $caixas['total'],
            'caixas_abertos' => $caixas['abertos'],
            'caixas_fechados' => $caixas['fechados'],
            'formas_pagamento' => $this->formasPagamentoPeriodo($empresaId, $inicio, $fim),
        ];
    }

    private function serieDiaria(int $empresaId, Carbon $inicio, Carbon $fim): array
    {
        $resultado = [];
        $dia = $inicio->copy()->startOfDay();
        $ultimoDia = $fim->copy()->endOfDay();

        while ($dia->lte($ultimoDia)) {
            $inicioDia = $dia->copy()->startOfDay();
            $fimDia = $dia->copy()->endOfDay();
            $total = $this->sumVendasPdv($empresaId, $inicioDia, $fimDia);
            $quantidade = $this->countVendasPdv($empresaId, $inicioDia, $fimDia);
            $caixas = $this->countCaixas($empresaId, $inicioDia, $fimDia);

            $resultado[] = [
                'data' => $dia->toDateString(),
                'vendas_pdv_total' => round($total, 2),
                'quantidade_vendas' => $quantidade,
                'ticket_medio' => $quantidade > 0 ? round($total / $quantidade, 2) : 0.0,
                'suprimentos' => round($this->sumMovimento('suprimento_caixas', $empresaId, $inicioDia, $fimDia), 2),
                'sangrias' => round($this->sumMovimento('sangria_caixas', $empresaId, $inicioDia, $fimDia), 2),
                'caixas_abertos_no_periodo' => $caixas['total'],
            ];

            $dia->addDay();
        }

        return $resultado;
    }

    private function rankingOperadores(int $empresaId, Carbon $inicio, Carbon $fim): array
    {
        if (!Schema::hasTable('venda_caixas')
            || !Schema::hasColumn('venda_caixas', 'empresa_id')
            || !Schema::hasColumn('venda_caixas', 'usuario_id')
            || !Schema::hasColumn('venda_caixas', 'valor_total')
        ) {
            return [];
        }

        $query = DB::table('venda_caixas as v')
            ->where('v.empresa_id', $empresaId)
            ->whereBetween('v.created_at', [$inicio, $fim]);

        $this->aplicarExclusoesPdvAlias($query, 'v');

        if (Schema::hasTable('usuarios') && Schema::hasColumn('usuarios', 'nome')) {
            $query->leftJoin('usuarios as u', 'u.id', '=', 'v.usuario_id')
                ->select(
                    'v.usuario_id',
                    'u.nome as usuario',
                    DB::raw('COUNT(v.id) as quantidade_vendas'),
                    DB::raw('COALESCE(SUM(v.valor_total), 0) as total_vendas')
                )
                ->groupBy('v.usuario_id', 'u.nome');
        } else {
            $query->select(
                    'v.usuario_id',
                    DB::raw('COUNT(v.id) as quantidade_vendas'),
                    DB::raw('COALESCE(SUM(v.valor_total), 0) as total_vendas')
                )
                ->groupBy('v.usuario_id');
        }

        return $query
            ->orderByDesc('total_vendas')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                $total = (float) $row->total_vendas;
                $quantidade = (int) $row->quantidade_vendas;

                return [
                    'usuario_id' => (int) $row->usuario_id,
                    'usuario' => $row->usuario ?? ('Usuário #' . $row->usuario_id),
                    'quantidade_vendas' => $quantidade,
                    'total_vendas' => round($total, 2),
                    'ticket_medio' => $quantidade > 0 ? round($total / $quantidade, 2) : 0.0,
                ];
            })
            ->values()
            ->all();
    }

    private function caixasPeriodo(int $empresaId, Carbon $inicio, Carbon $fim, int $limite = 80): array
    {
        if (!Schema::hasTable('abertura_caixas') || !Schema::hasColumn('abertura_caixas', 'empresa_id')) {
            return [];
        }

        $query = DB::table('abertura_caixas as a')
            ->where('a.empresa_id', $empresaId)
            ->where('a.created_at', '<=', $fim)
            ->where(function ($q) use ($inicio) {
                $q->where('a.status', 0)
                    ->orWhere('a.updated_at', '>=', $inicio);
            });

        if (Schema::hasTable('usuarios') && Schema::hasColumn('usuarios', 'nome')) {
            $query->leftJoin('usuarios as u', 'u.id', '=', 'a.usuario_id')
                ->addSelect('u.nome as usuario');
        }

        $select = [
            'a.id',
            'a.usuario_id',
            'a.status',
            'a.valor',
            'a.created_at',
            'a.updated_at',
        ];

        if (Schema::hasColumn('abertura_caixas', 'filial_id')) {
            $select[] = 'a.filial_id';
        }
        if (Schema::hasColumn('abertura_caixas', 'valor_dinheiro_caixa')) {
            $select[] = 'a.valor_dinheiro_caixa';
        }

        $rows = $query
            ->addSelect($select)
            ->orderByDesc('a.created_at')
            ->limit($limite)
            ->get();

        return $rows->map(function ($row) use ($empresaId, $inicio, $fim) {
            $abertoEm = Carbon::parse($row->created_at);
            $fechadoEm = ((int) $row->status === 0)
                ? Carbon::now()
                : Carbon::parse($row->updated_at);
            $inicioCaixa = $abertoEm->greaterThan($inicio) ? $abertoEm : $inicio->copy();
            $fimCaixa = $fechadoEm->lessThan($fim) ? $fechadoEm : $fim->copy();
            $usuarioId = (int) $row->usuario_id;

            return [
                'caixa_id' => (int) $row->id,
                'usuario_id' => $usuarioId,
                'usuario' => $row->usuario ?? ('Usuário #' . $usuarioId),
                'filial_id' => $row->filial_id ?? null,
                'status' => ((int) $row->status === 0) ? 'aberto' : 'fechado',
                'aberto_em' => $abertoEm->format('Y-m-d H:i:s'),
                'fechado_em' => ((int) $row->status === 0) ? null : $fechadoEm->format('Y-m-d H:i:s'),
                'valor_abertura' => round((float) ($row->valor ?? 0), 2),
                'vendas_pdv_total' => round($this->sumVendasPdv($empresaId, $inicioCaixa, $fimCaixa, $usuarioId), 2),
                'quantidade_vendas' => $this->countVendasPdv($empresaId, $inicioCaixa, $fimCaixa, $usuarioId),
                'suprimentos' => round($this->sumMovimento('suprimento_caixas', $empresaId, $inicioCaixa, $fimCaixa, $usuarioId), 2),
                'sangrias' => round($this->sumMovimento('sangria_caixas', $empresaId, $inicioCaixa, $fimCaixa, $usuarioId), 2),
                'formas_pagamento' => $this->formasPagamentoPeriodo($empresaId, $inicioCaixa, $fimCaixa, $usuarioId),
                'dinheiro_informado_fechamento' => isset($row->valor_dinheiro_caixa)
                    ? round((float) $row->valor_dinheiro_caixa, 2)
                    : null,
            ];
        })->values()->all();
    }

    private function melhorDia(int $empresaId, Carbon $inicio, Carbon $fim): ?array
    {
        $dias = $this->serieDiaria($empresaId, $inicio, $fim);
        if (empty($dias)) {
            return null;
        }

        usort($dias, fn ($a, $b) => $b['vendas_pdv_total'] <=> $a['vendas_pdv_total']);

        return $dias[0] ?? null;
    }

    private function sumVendasPdv(
        int $empresaId,
        Carbon $inicio,
        Carbon $fim,
        ?int $usuarioId = null
    ): float {
        if (!Schema::hasTable('venda_caixas')
            || !Schema::hasColumn('venda_caixas', 'empresa_id')
            || !Schema::hasColumn('venda_caixas', 'valor_total')
        ) {
            return 0.0;
        }

        $query = DB::table('venda_caixas')
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$inicio, $fim]);

        if ($usuarioId !== null && Schema::hasColumn('venda_caixas', 'usuario_id')) {
            $query->where('usuario_id', $usuarioId);
        }

        $this->aplicarExclusoesPdv($query);

        return round((float) $query->sum('valor_total'), 2);
    }

    private function countVendasPdv(
        int $empresaId,
        Carbon $inicio,
        Carbon $fim,
        ?int $usuarioId = null
    ): int {
        if (!Schema::hasTable('venda_caixas') || !Schema::hasColumn('venda_caixas', 'empresa_id')) {
            return 0;
        }

        $query = DB::table('venda_caixas')
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$inicio, $fim]);

        if ($usuarioId !== null && Schema::hasColumn('venda_caixas', 'usuario_id')) {
            $query->where('usuario_id', $usuarioId);
        }

        $this->aplicarExclusoesPdv($query);

        return $query->count();
    }

    private function sumMovimento(
        string $table,
        int $empresaId,
        Carbon $inicio,
        Carbon $fim,
        ?int $usuarioId = null
    ): float {
        if (!Schema::hasTable($table)
            || !Schema::hasColumn($table, 'empresa_id')
            || !Schema::hasColumn($table, 'valor')
        ) {
            return 0.0;
        }

        $query = DB::table($table)
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$inicio, $fim]);

        if ($usuarioId !== null && Schema::hasColumn($table, 'usuario_id')) {
            $query->where('usuario_id', $usuarioId);
        }

        return round((float) $query->sum('valor'), 2);
    }

    private function countCaixas(int $empresaId, Carbon $inicio, Carbon $fim): array
    {
        if (!Schema::hasTable('abertura_caixas') || !Schema::hasColumn('abertura_caixas', 'empresa_id')) {
            return ['total' => 0, 'abertos' => 0, 'fechados' => 0];
        }

        $query = DB::table('abertura_caixas')
            ->where('empresa_id', $empresaId)
            ->where('created_at', '<=', $fim)
            ->where(function ($q) use ($inicio) {
                $q->where('status', 0)
                    ->orWhere('updated_at', '>=', $inicio);
            });

        return [
            'total' => (clone $query)->count(),
            'abertos' => (clone $query)->where('status', 0)->count(),
            'fechados' => (clone $query)->where('status', '<>', 0)->count(),
        ];
    }

    private function formasPagamentoPeriodo(
        int $empresaId,
        Carbon $inicio,
        Carbon $fim,
        ?int $usuarioId = null
    ): array {
        if (!Schema::hasTable('venda_caixas') || !Schema::hasColumn('venda_caixas', 'empresa_id')) {
            return [];
        }

        $totais = [];
        $faturaDisponivel = Schema::hasTable('fatura_frente_caixas')
            && Schema::hasColumn('fatura_frente_caixas', 'venda_caixa_id')
            && Schema::hasColumn('fatura_frente_caixas', 'forma_pagamento')
            && Schema::hasColumn('fatura_frente_caixas', 'valor');

        if ($faturaDisponivel) {
            $query = DB::table('fatura_frente_caixas as f')
                ->join('venda_caixas as v', 'v.id', '=', 'f.venda_caixa_id')
                ->where('v.empresa_id', $empresaId)
                ->whereBetween('v.created_at', [$inicio, $fim]);

            if ($usuarioId !== null && Schema::hasColumn('venda_caixas', 'usuario_id')) {
                $query->where('v.usuario_id', $usuarioId);
            }

            $this->aplicarExclusoesPdvAlias($query, 'v');

            $rows = $query
                ->select('f.forma_pagamento', DB::raw('COUNT(*) as quantidade'), DB::raw('COALESCE(SUM(f.valor), 0) as total'))
                ->groupBy('f.forma_pagamento')
                ->get();

            foreach ($rows as $row) {
                $this->adicionarForma($totais, $row->forma_pagamento, (float) $row->total, (int) $row->quantidade);
            }
        }

        $tipoColumn = Schema::hasColumn('venda_caixas', 'tipo_pagamento')
            ? 'tipo_pagamento'
            : (Schema::hasColumn('venda_caixas', 'forma_pagamento') ? 'forma_pagamento' : null);

        if ($tipoColumn !== null && Schema::hasColumn('venda_caixas', 'valor_total')) {
            $fallback = DB::table('venda_caixas as v')
                ->where('v.empresa_id', $empresaId)
                ->whereBetween('v.created_at', [$inicio, $fim]);

            if ($usuarioId !== null && Schema::hasColumn('venda_caixas', 'usuario_id')) {
                $fallback->where('v.usuario_id', $usuarioId);
            }

            $this->aplicarExclusoesPdvAlias($fallback, 'v');

            if ($faturaDisponivel) {
                $fallback->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('fatura_frente_caixas as f')
                        ->whereColumn('f.venda_caixa_id', 'v.id');
                });
            }

            $rows = $fallback
                ->select('v.' . $tipoColumn, DB::raw('COUNT(*) as quantidade'), DB::raw('COALESCE(SUM(v.valor_total), 0) as total'))
                ->groupBy('v.' . $tipoColumn)
                ->get();

            foreach ($rows as $row) {
                $this->adicionarForma($totais, $row->{$tipoColumn} ?? null, (float) $row->total, (int) $row->quantidade);
            }
        }

        $resultado = array_values($totais);
        usort($resultado, fn ($a, $b) => $b['total'] <=> $a['total']);

        return $resultado;
    }

    private function adicionarForma(array &$totais, $codigo, float $total, int $quantidade): void
    {
        $chave = trim((string) ($codigo ?? ''));
        $chave = $chave === '' ? 'nao_informado' : $chave;

        if (!isset($totais[$chave])) {
            $totais[$chave] = [
                'codigo' => $codigo,
                'forma' => $this->paymentLabel($codigo),
                'total' => 0.0,
                'quantidade_lancamentos' => 0,
            ];
        }

        $totais[$chave]['total'] = round($totais[$chave]['total'] + $total, 2);
        $totais[$chave]['quantidade_lancamentos'] += $quantidade;
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

    private function aplicarExclusoesPdv(Builder $query): void
    {
        if (Schema::hasColumn('venda_caixas', 'estado_emissao')) {
            $query->where(function ($q) {
                $q->whereNull('estado_emissao')
                    ->orWhereRaw('LOWER(estado_emissao) <> ?', ['cancelado']);
            });
        }
        if (Schema::hasColumn('venda_caixas', 'rascunho')) {
            $query->where('rascunho', 0);
        }
        if (Schema::hasColumn('venda_caixas', 'consignado')) {
            $query->where('consignado', 0);
        }
    }

    private function aplicarExclusoesPdvAlias(Builder $query, string $alias): void
    {
        if (Schema::hasColumn('venda_caixas', 'estado_emissao')) {
            $query->where(function ($q) use ($alias) {
                $q->whereNull($alias . '.estado_emissao')
                    ->orWhereRaw('LOWER(' . $alias . '.estado_emissao) <> ?', ['cancelado']);
            });
        }
        if (Schema::hasColumn('venda_caixas', 'rascunho')) {
            $query->where($alias . '.rascunho', 0);
        }
        if (Schema::hasColumn('venda_caixas', 'consignado')) {
            $query->where($alias . '.consignado', 0);
        }
    }
}