<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaasReceitaService
{
    public function resumo(Carbon $inicio, Carbon $fim): array
    {
        $empresaAdministradoraId = $this->empresaAdministradoraId();
        $inicioData = $inicio->copy()->startOfDay();
        $fimData = $fim->copy()->endOfDay();
        $hoje = now()->startOfDay();

        $receitas = $this->resumoReceitas(
            $empresaAdministradoraId,
            $inicioData,
            $fimData,
            $hoje
        );

        $despesas = $this->resumoDespesas(
            $empresaAdministradoraId,
            $inicioData,
            $fimData,
            $hoje
        );

        return [
            'receitas' => $receitas,
            'despesas' => $despesas,
            'resultado' => [
                'realizado' => round($receitas['recebido'] - $despesas['pago'], 2),
                'projetado' => round($receitas['a_vencer'] - $despesas['a_vencer'], 2),
                'vencido_liquido' => round($receitas['vencido'] - $despesas['vencido'], 2),
            ],
        ];
    }

    private function resumoReceitas(
        int $empresaAdministradoraId,
        Carbon $inicio,
        Carbon $fim,
        Carbon $hoje
    ): array {
        if (
            !Schema::hasTable('conta_recebers')
            || !Schema::hasColumn('conta_recebers', 'empresa_id_emp')
        ) {
            return $this->emptyFinancialResult($inicio, $fim, 'receita');
        }

        $base = DB::table('conta_recebers')
            ->where('empresa_id', $empresaAdministradoraId)
            ->whereNotNull('empresa_id_emp')
            ->where('empresa_id_emp', '>', 0);

        $saldoExpression = $this->saldoReceberExpression();
        $recebidoExpression = $this->recebidoExpression();
        $dataRecebimentoExpression = $this->dataRecebimentoExpression();

        $recebido = (float) (clone $base)
            ->where(function (Builder $query) {
                $query->where('status', 1)
                    ->orWhere('valor_recebido', '>', 0);
            })
            ->whereBetween(DB::raw($dataRecebimentoExpression), [$inicio, $fim])
            ->sum(DB::raw($recebidoExpression));

        $abertoQuery = (clone $base)
            ->where('status', 0)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->whereRaw("{$saldoExpression} > 0");

        $emAberto = (float) (clone $abertoQuery)->sum(DB::raw($saldoExpression));

        $vencido = (float) (clone $abertoQuery)
            ->whereDate('data_vencimento', '<', $hoje->toDateString())
            ->sum(DB::raw($saldoExpression));

        $aVencer = max(0, $emAberto - $vencido);
        $titulosPeriodo = (clone $base)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->count();
        $titulosRecebidos = (clone $base)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->where('status', 1)
            ->count();
        $titulosVencidos = (clone $abertoQuery)
            ->whereDate('data_vencimento', '<', $hoje->toDateString())
            ->count();

        return [
            'recebido' => round($recebido, 2),
            'em_aberto' => round($emAberto, 2),
            'a_vencer' => round($aVencer, 2),
            'vencido' => round($vencido, 2),
            'titulos_periodo' => $titulosPeriodo,
            'titulos_recebidos' => $titulosRecebidos,
            'titulos_vencidos' => $titulosVencidos,
            'taxa_recebimento' => round(
                $titulosPeriodo > 0 ? ($titulosRecebidos / $titulosPeriodo) * 100 : 0,
                2
            ),
            'serie' => $this->serieReceitas(
                $base,
                $inicio,
                $fim,
                $hoje,
                $recebidoExpression,
                $saldoExpression,
                $dataRecebimentoExpression
            ),
        ];
    }

    private function resumoDespesas(
        int $empresaAdministradoraId,
        Carbon $inicio,
        Carbon $fim,
        Carbon $hoje
    ): array {
        if (!Schema::hasTable('conta_pagars')) {
            return $this->emptyFinancialResult($inicio, $fim, 'despesa');
        }

        $base = DB::table('conta_pagars')
            ->where('empresa_id', $empresaAdministradoraId);

        $saldoExpression = $this->saldoPagarExpression();
        $pagoExpression = $this->pagoExpression();
        $dataPagamentoExpression = $this->dataPagamentoExpression();

        $pago = (float) (clone $base)
            ->where(function (Builder $query) {
                $query->where('status', 1)
                    ->orWhere('valor_pago', '>', 0);
            })
            ->whereBetween(DB::raw($dataPagamentoExpression), [$inicio, $fim])
            ->sum(DB::raw($pagoExpression));

        $abertoQuery = (clone $base)
            ->where('status', 0)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->whereRaw("{$saldoExpression} > 0");

        $emAberto = (float) (clone $abertoQuery)->sum(DB::raw($saldoExpression));

        $vencido = (float) (clone $abertoQuery)
            ->whereDate('data_vencimento', '<', $hoje->toDateString())
            ->sum(DB::raw($saldoExpression));

        $aVencer = max(0, $emAberto - $vencido);
        $titulosPeriodo = (clone $base)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->count();
        $titulosPagos = (clone $base)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->where('status', 1)
            ->count();
        $titulosVencidos = (clone $abertoQuery)
            ->whereDate('data_vencimento', '<', $hoje->toDateString())
            ->count();

        return [
            'pago' => round($pago, 2),
            'em_aberto' => round($emAberto, 2),
            'a_vencer' => round($aVencer, 2),
            'vencido' => round($vencido, 2),
            'titulos_periodo' => $titulosPeriodo,
            'titulos_pagos' => $titulosPagos,
            'titulos_vencidos' => $titulosVencidos,
            'taxa_pagamento' => round(
                $titulosPeriodo > 0 ? ($titulosPagos / $titulosPeriodo) * 100 : 0,
                2
            ),
            'serie' => $this->serieDespesas(
                $base,
                $inicio,
                $fim,
                $hoje,
                $pagoExpression,
                $saldoExpression,
                $dataPagamentoExpression
            ),
        ];
    }

    private function empresaAdministradoraId(): int
    {
        $user = session('user_logged');
        $isSuper = (bool) ($user['super'] ?? false);
        $login = (string) ($user['login'] ?? '');

        if (!$user || (!$isSuper && (!$login || !isSuper($login)))) {
            abort(403, 'Acesso permitido somente ao administrador master do SaaS.');
        }

        $empresaId = (int) ($user['empresa'] ?? 0);

        if ($empresaId < 1) {
            abort(403, 'Empresa administradora não identificada na sessão.');
        }

        return $empresaId;
    }

    private function recebidoExpression(): string
    {
        return "CASE
            WHEN COALESCE(valor_recebido, 0) > 0 THEN COALESCE(valor_recebido, 0)
            WHEN status = 1 THEN COALESCE(valor_integral, 0)
                + COALESCE(juros, 0)
                + COALESCE(multa, 0)
            ELSE 0
        END";
    }

    private function saldoReceberExpression(): string
    {
        return "CASE
            WHEN status = 1 THEN 0
            ELSE GREATEST(
                COALESCE(valor_integral, 0)
                + COALESCE(juros, 0)
                + COALESCE(multa, 0)
                - COALESCE(valor_recebido, 0),
                0
            )
        END";
    }

    private function pagoExpression(): string
    {
        return "CASE
            WHEN COALESCE(valor_pago, 0) > 0 THEN COALESCE(valor_pago, 0)
            WHEN status = 1 THEN COALESCE(valor_integral, 0)
            ELSE 0
        END";
    }

    private function saldoPagarExpression(): string
    {
        return "CASE
            WHEN status = 1 THEN 0
            ELSE GREATEST(
                COALESCE(valor_integral, 0) - COALESCE(valor_pago, 0),
                0
            )
        END";
    }

    private function dataRecebimentoExpression(): string
    {
        return "COALESCE(NULLIF(data_recebimento, '0000-00-00'), data_vencimento)";
    }

    private function dataPagamentoExpression(): string
    {
        return "COALESCE(NULLIF(data_pagamento, '0000-00-00'), data_vencimento)";
    }

    private function serieReceitas(
        Builder $base,
        Carbon $inicio,
        Carbon $fim,
        Carbon $hoje,
        string $recebidoExpression,
        string $saldoExpression,
        string $dataRecebimentoExpression
    ): array {
        $recebidos = (clone $base)
            ->where(function (Builder $query) {
                $query->where('status', 1)
                    ->orWhere('valor_recebido', '>', 0);
            })
            ->whereBetween(DB::raw($dataRecebimentoExpression), [$inicio, $fim])
            ->selectRaw("DATE_FORMAT({$dataRecebimentoExpression}, '%Y-%m') as periodo")
            ->selectRaw("SUM({$recebidoExpression}) as total")
            ->groupBy('periodo')
            ->pluck('total', 'periodo');

        $aVencer = $this->openMonthlySeries(
            $base,
            $inicio,
            $fim,
            $hoje,
            $saldoExpression,
            false
        );
        $vencidos = $this->openMonthlySeries(
            $base,
            $inicio,
            $fim,
            $hoje,
            $saldoExpression,
            true
        );

        return $this->buildSeries($inicio, $fim, [
            'recebida' => $recebidos,
            'pendente' => $aVencer,
            'vencida' => $vencidos,
        ]);
    }

    private function serieDespesas(
        Builder $base,
        Carbon $inicio,
        Carbon $fim,
        Carbon $hoje,
        string $pagoExpression,
        string $saldoExpression,
        string $dataPagamentoExpression
    ): array {
        $pagos = (clone $base)
            ->where(function (Builder $query) {
                $query->where('status', 1)
                    ->orWhere('valor_pago', '>', 0);
            })
            ->whereBetween(DB::raw($dataPagamentoExpression), [$inicio, $fim])
            ->selectRaw("DATE_FORMAT({$dataPagamentoExpression}, '%Y-%m') as periodo")
            ->selectRaw("SUM({$pagoExpression}) as total")
            ->groupBy('periodo')
            ->pluck('total', 'periodo');

        $aVencer = $this->openMonthlySeries(
            $base,
            $inicio,
            $fim,
            $hoje,
            $saldoExpression,
            false
        );
        $vencidos = $this->openMonthlySeries(
            $base,
            $inicio,
            $fim,
            $hoje,
            $saldoExpression,
            true
        );

        return $this->buildSeries($inicio, $fim, [
            'paga' => $pagos,
            'pendente' => $aVencer,
            'vencida' => $vencidos,
        ]);
    }

    private function openMonthlySeries(
        Builder $base,
        Carbon $inicio,
        Carbon $fim,
        Carbon $hoje,
        string $saldoExpression,
        bool $vencido
    ) {
        $query = (clone $base)
            ->where('status', 0)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->whereRaw("{$saldoExpression} > 0");

        if ($vencido) {
            $query->whereDate('data_vencimento', '<', $hoje->toDateString());
        } else {
            $query->whereDate('data_vencimento', '>=', $hoje->toDateString());
        }

        return $query
            ->selectRaw("DATE_FORMAT(data_vencimento, '%Y-%m') as periodo")
            ->selectRaw("SUM({$saldoExpression}) as total")
            ->groupBy('periodo')
            ->pluck('total', 'periodo');
    }

    private function buildSeries(Carbon $inicio, Carbon $fim, array $maps): array
    {
        $labels = [];
        $series = [];

        foreach (array_keys($maps) as $key) {
            $series[$key] = [];
        }

        $meses = [1 => 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $cursor = $inicio->copy()->startOfMonth();
        $limite = $fim->copy()->startOfMonth();

        while ($cursor->lte($limite)) {
            $chave = $cursor->format('Y-m');
            $labels[] = $meses[(int) $cursor->format('n')] . '/' . $cursor->format('y');

            foreach ($maps as $nome => $map) {
                $series[$nome][] = round((float) $map->get($chave, 0), 2);
            }

            $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }

    private function emptyFinancialResult(
        Carbon $inicio,
        Carbon $fim,
        string $type
    ): array {
        $seriesNames = $type === 'despesa'
            ? ['paga', 'pendente', 'vencida']
            : ['recebida', 'pendente', 'vencida'];
        $serie = $this->buildSeries($inicio, $fim, array_fill_keys($seriesNames, collect()));

        if ($type === 'despesa') {
            return [
                'pago' => 0,
                'em_aberto' => 0,
                'a_vencer' => 0,
                'vencido' => 0,
                'titulos_periodo' => 0,
                'titulos_pagos' => 0,
                'titulos_vencidos' => 0,
                'taxa_pagamento' => 0,
                'serie' => $serie,
            ];
        }

        return [
            'recebido' => 0,
            'em_aberto' => 0,
            'a_vencer' => 0,
            'vencido' => 0,
            'titulos_periodo' => 0,
            'titulos_recebidos' => 0,
            'titulos_vencidos' => 0,
            'taxa_recebimento' => 0,
            'serie' => $serie,
        ];
    }
}