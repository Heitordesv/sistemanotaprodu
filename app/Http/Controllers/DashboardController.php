<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('user_logged')) {
            return redirect('/login')->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $user = session('user_logged');
        $empresaId = is_object($user) ? ($user->empresa_id ?? null) : ($user['empresa'] ?? null);
        $isAdmin = is_object($user) ? ($user->adm ?? 0) : ($user['adm'] ?? 0);

        if ((int) $isAdmin !== 1) {
            return redirect('/graficos');
        }

        $inicio = $request->filled('data_inicio')
            ? Carbon::parse($request->data_inicio)->startOfDay()
            : now()->startOfMonth();

        $fim = $request->filled('data_fim')
            ? Carbon::parse($request->data_fim)->endOfDay()
            : now()->endOfMonth();

        $hoje = now()->startOfDay();
        $proximos7Dias = now()->addDays(7)->endOfDay();

        $totalLead = Lead::count();
        $totalAtivas = Empresa::where('status', 1)->count();
        $totalBloqueadas = Empresa::where('status', 0)->count();

        $planosAtivosCollection = collect();
        $empresasPagas = 0;
        $empresasTesteGratis = 0;
        $novosPagantesPeriodo = 0;
        $clientesRisco = 0;
        $clientesVencidos = 0;
        $mrr = 0.0;
        $arr = 0.0;
        $ticketMedio = 0.0;
        $planosResumo = collect();
        $crescimento12Meses = collect();

        if (Schema::hasTable('plano_empresas') && Schema::hasTable('planos')) {
            $planosAtivosCollection = DB::table('plano_empresas')
                ->join('planos', 'planos.id', '=', 'plano_empresas.plano_id')
                ->join('empresas', 'empresas.id', '=', 'plano_empresas.empresa_id')
                ->where('empresas.status', 1)
                ->where('plano_empresas.plano_id', '!=', 17)
                ->where('plano_empresas.expiracao', '>=', $hoje)
                ->select(
                    'plano_empresas.id',
                    'plano_empresas.empresa_id',
                    'plano_empresas.plano_id',
                    'plano_empresas.expiracao',
                    'plano_empresas.valor as valor_personalizado',
                    'planos.nome as plano_nome',
                    'planos.valor as plano_valor',
                    'planos.intervalo_dias'
                )
                ->orderByDesc('plano_empresas.id')
                ->get()
                ->unique('empresa_id')
                ->values();

            $empresasPagas = $planosAtivosCollection->count();

            $empresasTesteGratis = DB::table('plano_empresas')
                ->join('empresas', 'empresas.id', '=', 'plano_empresas.empresa_id')
                ->where('empresas.status', 1)
                ->where('plano_empresas.plano_id', 17)
                ->where('plano_empresas.expiracao', '>=', $hoje)
                ->distinct('plano_empresas.empresa_id')
                ->count('plano_empresas.empresa_id');

            $novosPagantesPeriodo = DB::table('plano_empresas')
                ->join('empresas', 'empresas.id', '=', 'plano_empresas.empresa_id')
                ->where('empresas.status', 1)
                ->where('plano_empresas.plano_id', '!=', 17)
                ->whereBetween('plano_empresas.created_at', [$inicio, $fim])
                ->distinct('plano_empresas.empresa_id')
                ->count('plano_empresas.empresa_id');

            $clientesRisco = DB::table('plano_empresas')
                ->join('empresas', 'empresas.id', '=', 'plano_empresas.empresa_id')
                ->where('empresas.status', 1)
                ->where('plano_empresas.plano_id', '!=', 17)
                ->whereBetween('plano_empresas.expiracao', [$hoje, $proximos7Dias])
                ->distinct('plano_empresas.empresa_id')
                ->count('plano_empresas.empresa_id');

            $clientesVencidos = DB::table('plano_empresas')
                ->join('empresas', 'empresas.id', '=', 'plano_empresas.empresa_id')
                ->where('empresas.status', 1)
                ->where('plano_empresas.plano_id', '!=', 17)
                ->where('plano_empresas.expiracao', '<', $hoje)
                ->distinct('plano_empresas.empresa_id')
                ->count('plano_empresas.empresa_id');

            $planosAtivosCollection = $planosAtivosCollection->map(function ($plano) {
                $valor = (float) ($plano->valor_personalizado ?: $plano->plano_valor ?: 0);
                $dias = max((int) ($plano->intervalo_dias ?: 30), 1);

                if ($dias >= 300) {
                    $mrrPlano = $valor / 12;
                } else {
                    $mrrPlano = $valor / max($dias / 30, 1);
                }

                $plano->mrr_calculado = round($mrrPlano, 2);
                $plano->valor_efetivo = $valor;

                return $plano;
            });

            $mrr = (float) $planosAtivosCollection->sum('mrr_calculado');
            $arr = $mrr * 12;
            $ticketMedio = $empresasPagas > 0 ? $mrr / $empresasPagas : 0;

            $planosResumo = $planosAtivosCollection
                ->groupBy('plano_nome')
                ->map(function ($items, $nome) {
                    return (object) [
                        'nome' => $nome ?: 'Plano sem nome',
                        'clientes' => $items->count(),
                        'mrr' => (float) $items->sum('mrr_calculado'),
                    ];
                })
                ->sortByDesc('clientes')
                ->values();

            $crescimento12Meses = DB::table('plano_empresas')
                ->where('plano_id', '!=', 17)
                ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(DISTINCT empresa_id) as total")
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();
        }

        $assinaturasDisponiveis = Schema::hasTable('subscriptions');
        $assinaturasAutorizadas = 0;
        $assinaturasPausadas = 0;
        $assinaturasCanceladas = 0;
        $assinaturasPendentes = 0;
        $proximasCobrancas = 0;
        $assinaturasRecentes = collect();

        if ($assinaturasDisponiveis) {
            $assinaturasAutorizadas = DB::table('subscriptions')->where('status', 'authorized')->count();
            $assinaturasPausadas = DB::table('subscriptions')->where('status', 'paused')->count();
            $assinaturasCanceladas = DB::table('subscriptions')->where('status', 'cancelled')->count();
            $assinaturasPendentes = DB::table('subscriptions')
                ->whereNotIn('status', ['authorized', 'paused', 'cancelled'])
                ->count();

            $proximasCobrancas = DB::table('subscriptions')
                ->where('status', 'authorized')
                ->whereNotNull('next_payment_date')
                ->whereBetween('next_payment_date', [$hoje, $proximos7Dias])
                ->count();

            $assinaturasRecentes = DB::table('subscriptions')
                ->select('id', 'user_id', 'mp_subscription_id', 'mp_plan_id', 'status', 'next_payment_date', 'created_at')
                ->orderByDesc('id')
                ->limit(8)
                ->get();
        }

        $pagamentosAprovados = 0.0;
        $pagamentosPendentes = 0.0;
        $pagamentosPorForma = collect();

        if (Schema::hasTable('payments')) {
            $pagamentosPeriodo = DB::table('payments')
                ->whereBetween('created_at', [$inicio, $fim]);

            $pagamentosAprovados = (float) (clone $pagamentosPeriodo)
                ->whereIn('status', [1, '1', 'approved', 'authorized'])
                ->sum('valor');

            $pagamentosPendentes = (float) (clone $pagamentosPeriodo)
                ->whereIn('status', [0, '0', 2, '2', 'pending', 'in_process'])
                ->sum('valor');

            $pagamentosPorForma = (clone $pagamentosPeriodo)
                ->whereIn('status', [1, '1', 'approved', 'authorized'])
                ->selectRaw("COALESCE(NULLIF(forma_pagamento, ''), 'não informado') as forma, COUNT(*) as quantidade, SUM(valor) as total")
                ->groupBy('forma_pagamento')
                ->orderByDesc('total')
                ->get();
        }

        $totalBaseConversao = $novosPagantesPeriodo + $empresasTesteGratis;
        $taxaConversao = $totalBaseConversao > 0
            ? ($novosPagantesPeriodo / $totalBaseConversao) * 100
            : 0;

        $churnPrevisto = $empresasPagas > 0 ? ($clientesRisco / $empresasPagas) * 100 : 0;
        $receitaPrevista12Meses = $arr;

        $investimentoMarketing = 0.0;
        if (Schema::hasTable('conta_pagars')) {
            $investimentoMarketing = (float) DB::table('conta_pagars')
                ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
                ->where('categoria_id', 191)
                ->whereBetween('created_at', [$inicio, $fim])
                ->sum('valor_integral');
        }

        $cac = $novosPagantesPeriodo > 0 ? $investimentoMarketing / $novosPagantesPeriodo : 0;
        $ltv = $ticketMedio * 12;
        $ltvCac = $cac > 0 ? $ltv / $cac : 0;

        $totalReceberMes = 0.0;
        $recebidoMes = 0.0;
        $pendenteReceberMes = 0.0;
        $totalPagarMes = 0.0;
        $pagasMes = 0.0;
        $pendentePagarMes = 0.0;
        $vencidasPagarMes = 0.0;

        if (Schema::hasTable('conta_recebers')) {
            $contasReceber = DB::table('conta_recebers')
                ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
                ->whereBetween('data_vencimento', [$inicio, $fim]);

            $totalReceberMes = (float) (clone $contasReceber)->sum('valor_integral');
            $recebidoMes = (float) (clone $contasReceber)->where('status', 1)->sum('valor_integral');
            $pendenteReceberMes = (float) (clone $contasReceber)->where('status', 0)->sum('valor_integral');
        }

        if (Schema::hasTable('conta_pagars')) {
            $contasPagar = DB::table('conta_pagars')
                ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
                ->whereBetween('data_vencimento', [$inicio, $fim]);

            $totalPagarMes = (float) (clone $contasPagar)->sum('valor_integral');
            $pagasMes = (float) (clone $contasPagar)->where('status', 1)->sum('valor_integral');
            $pendentePagarMes = (float) (clone $contasPagar)
                ->where('status', 0)
                ->where('data_vencimento', '>=', $hoje)
                ->sum('valor_integral');
            $vencidasPagarMes = (float) (clone $contasPagar)
                ->where('status', 0)
                ->where('data_vencimento', '<', $hoje)
                ->sum('valor_integral');
        }

        $saldoMes = $recebidoMes - $pagasMes;

        $empresasAlerta = collect();
        if (Schema::hasTable('plano_empresas')) {
            $empresasAlerta = DB::table('plano_empresas')
                ->join('empresas', 'empresas.id', '=', 'plano_empresas.empresa_id')
                ->leftJoin('planos', 'planos.id', '=', 'plano_empresas.plano_id')
                ->where('empresas.status', 1)
                ->where('plano_empresas.plano_id', '!=', 17)
                ->whereBetween('plano_empresas.expiracao', [$hoje, $proximos7Dias])
                ->select(
                    'empresas.id as empresa_id',
                    'empresas.razao_social',
                    'empresas.nome_fantasia',
                    'planos.nome as plano_nome',
                    'plano_empresas.expiracao'
                )
                ->orderBy('plano_empresas.expiracao')
                ->limit(10)
                ->get();
        }

        return view('dashboard.index', compact(
            'inicio', 'fim', 'totalLead', 'totalAtivas', 'totalBloqueadas',
            'empresasPagas', 'empresasTesteGratis', 'novosPagantesPeriodo',
            'clientesRisco', 'clientesVencidos', 'mrr', 'arr', 'ticketMedio',
            'planosResumo', 'crescimento12Meses', 'assinaturasDisponiveis',
            'assinaturasAutorizadas', 'assinaturasPausadas', 'assinaturasCanceladas',
            'assinaturasPendentes', 'proximasCobrancas', 'assinaturasRecentes',
            'pagamentosAprovados', 'pagamentosPendentes', 'pagamentosPorForma',
            'taxaConversao', 'churnPrevisto', 'receitaPrevista12Meses',
            'investimentoMarketing', 'cac', 'ltv', 'ltvCac', 'totalReceberMes',
            'recebidoMes', 'pendenteReceberMes', 'totalPagarMes', 'pagasMes',
            'pendentePagarMes', 'vencidasPagarMes', 'saldoMes', 'empresasAlerta'
        ))->with('title', 'Dashboard SaaS');
    }
}