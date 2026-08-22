<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardGerencialController extends Controller
{
    public function resumo(Request $request)
    {
        $empresaId = $this->empresaIdDaSessao();

        if (!$empresaId) {
            return response()->json([
                'message' => 'Sessão expirada. Faça login novamente.',
            ], 401);
        }

        try {
            $inicio = $request->filled('data_inicial')
                ? Carbon::parse($request->data_inicial)->startOfDay()
                : Carbon::now()->startOfMonth();

            $fim = $request->filled('data_final')
                ? Carbon::parse($request->data_final)->endOfDay()
                : Carbon::now()->endOfMonth();
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Período inválido. Revise as datas informadas.',
            ], 422);
        }

        if ($fim->lt($inicio)) {
            return response()->json([
                'message' => 'A data final deve ser igual ou posterior à data inicial.',
            ], 422);
        }

        $localId = $request->input('local_id');
        $diasPeriodo = $inicio->diffInDays($fim) + 1;
        $fimAnterior = (clone $inicio)->subDay()->endOfDay();
        $inicioAnterior = (clone $fimAnterior)->subDays($diasPeriodo - 1)->startOfDay();

        $atual = $this->indicadoresDeVendas($empresaId, $localId, $inicio, $fim);
        $anterior = $this->indicadoresDeVendas($empresaId, $localId, $inicioAnterior, $fimAnterior);
        $financeiro = $this->indicadoresFinanceiros($empresaId, $localId, $inicio, $fim);
        $produtosTop = $this->produtosMaisRelevantes($empresaId, $localId, $inicio, $fim, $atual['faturamento']);

        $variacaoFaturamento = $this->variacaoPercentual($atual['faturamento'], $anterior['faturamento']);
        $variacaoVendas = $this->variacaoPercentual($atual['quantidade_vendas'], $anterior['quantidade_vendas']);
        $variacaoTicket = $this->variacaoPercentual($atual['ticket_medio'], $anterior['ticket_medio']);
        $resultadoOperacional = $atual['lucro_bruto'] - $financeiro['despesas_pagas'];
        $inadimplencia = $financeiro['contas_receber_abertas'] > 0
            ? ($financeiro['contas_receber_vencidas'] / $financeiro['contas_receber_abertas']) * 100
            : 0;
        $coberturaFinanceira = $financeiro['contas_pagar_abertas'] > 0
            ? $financeiro['contas_receber_abertas'] / $financeiro['contas_pagar_abertas']
            : null;
        $concentracaoTopProduto = $produtosTop->first()['participacao'] ?? 0;

        $saude = $this->calcularSaudeDoNegocio(
            $variacaoFaturamento,
            $atual['margem_bruta'],
            $inadimplencia,
            $resultadoOperacional,
            $coberturaFinanceira,
            $concentracaoTopProduto
        );

        $recomendacoes = $this->gerarRecomendacoes(
            $atual,
            $variacaoFaturamento,
            $financeiro,
            $inadimplencia,
            $resultadoOperacional,
            $coberturaFinanceira,
            $concentracaoTopProduto
        );

        return response()->json([
            'periodo' => [
                'inicio' => $inicio->format('Y-m-d'),
                'fim' => $fim->format('Y-m-d'),
                'inicio_anterior' => $inicioAnterior->format('Y-m-d'),
                'fim_anterior' => $fimAnterior->format('Y-m-d'),
            ],
            'indicadores' => [
                'faturamento' => [
                    'valor' => round($atual['faturamento'], 2),
                    'anterior' => round($anterior['faturamento'], 2),
                    'variacao' => $variacaoFaturamento,
                ],
                'vendas' => [
                    'quantidade' => $atual['quantidade_vendas'],
                    'anterior' => $anterior['quantidade_vendas'],
                    'variacao' => $variacaoVendas,
                ],
                'ticket_medio' => [
                    'valor' => round($atual['ticket_medio'], 2),
                    'anterior' => round($anterior['ticket_medio'], 2),
                    'variacao' => $variacaoTicket,
                ],
                'clientes_atendidos' => $atual['clientes_atendidos'],
                'custo_mercadoria' => round($atual['custo_mercadoria'], 2),
                'lucro_bruto' => round($atual['lucro_bruto'], 2),
                'margem_bruta' => round($atual['margem_bruta'], 2),
                'despesas_pagas' => round($financeiro['despesas_pagas'], 2),
                'resultado_operacional_estimado' => round($resultadoOperacional, 2),
                'contas_receber_abertas' => round($financeiro['contas_receber_abertas'], 2),
                'contas_receber_vencidas' => round($financeiro['contas_receber_vencidas'], 2),
                'inadimplencia' => round($inadimplencia, 2),
                'contas_pagar_abertas' => round($financeiro['contas_pagar_abertas'], 2),
                'cobertura_financeira' => $coberturaFinanceira === null
                    ? null
                    : round($coberturaFinanceira, 2),
                'concentracao_top_produto' => round($concentracaoTopProduto, 2),
            ],
            'saude_negocio' => $saude,
            'produtos_top' => $produtosTop->values(),
            'recomendacoes' => $recomendacoes,
        ]);
    }

    private function empresaIdDaSessao()
    {
        $usuario = session('user_logged');

        if (is_array($usuario)) {
            return (int) ($usuario['empresa'] ?? $usuario['empresa_id'] ?? 0);
        }

        if (is_object($usuario)) {
            return (int) ($usuario->empresa_id ?? $usuario->empresa ?? 0);
        }

        return 0;
    }

    private function indicadoresDeVendas($empresaId, $localId, Carbon $inicio, Carbon $fim)
    {
        $vendasNfe = Venda::query()
            ->where('empresa_id', $empresaId)
            ->where('estado_emissao', '!=', 'cancelado')
            ->whereBetween('data_registro', [$inicio, $fim]);
        $this->aplicarFiltroLocal($vendasNfe, $localId, 'filial_id');

        $vendasPdv = VendaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('estado_emissao', '!=', 'cancelado')
            ->where('rascunho', 0)
            ->where('consignado', 0)
            ->whereBetween('data_registro', [$inicio, $fim]);
        $this->aplicarFiltroLocal($vendasPdv, $localId, 'filial_id');

        $faturamentoNfe = (float) (clone $vendasNfe)->sum('valor_total');
        $faturamentoPdv = (float) (clone $vendasPdv)->sum('valor_total');
        $quantidadeNfe = (int) (clone $vendasNfe)->count();
        $quantidadePdv = (int) (clone $vendasPdv)->count();
        $quantidadeVendas = $quantidadeNfe + $quantidadePdv;
        $faturamento = $faturamentoNfe + $faturamentoPdv;

        $clientesNfe = (clone $vendasNfe)
            ->whereNotNull('cliente_id')
            ->distinct()
            ->pluck('cliente_id');
        $clientesPdv = (clone $vendasPdv)
            ->whereNotNull('cliente_id')
            ->distinct()
            ->pluck('cliente_id');
        $clientesAtendidos = $clientesNfe->merge($clientesPdv)->unique()->count();

        $custoMercadoria = $this->custoDasVendas($empresaId, $localId, $inicio, $fim);
        $lucroBruto = $faturamento - $custoMercadoria;
        $margemBruta = $faturamento > 0 ? ($lucroBruto / $faturamento) * 100 : 0;

        return [
            'faturamento' => $faturamento,
            'quantidade_vendas' => $quantidadeVendas,
            'ticket_medio' => $quantidadeVendas > 0 ? $faturamento / $quantidadeVendas : 0,
            'clientes_atendidos' => $clientesAtendidos,
            'custo_mercadoria' => $custoMercadoria,
            'lucro_bruto' => $lucroBruto,
            'margem_bruta' => $margemBruta,
        ];
    }

    private function custoDasVendas($empresaId, $localId, Carbon $inicio, Carbon $fim)
    {
        $nfe = DB::table('item_vendas as item')
            ->join('vendas as venda', 'venda.id', '=', 'item.venda_id')
            ->where('venda.empresa_id', $empresaId)
            ->where('venda.estado_emissao', '!=', 'cancelado')
            ->whereBetween('venda.data_registro', [$inicio, $fim]);
        $this->aplicarFiltroLocal($nfe, $localId, 'venda.filial_id');

        $pdv = DB::table('item_venda_caixas as item')
            ->join('venda_caixas as venda', 'venda.id', '=', 'item.venda_caixa_id')
            ->where('venda.empresa_id', $empresaId)
            ->where('venda.estado_emissao', '!=', 'cancelado')
            ->where('venda.rascunho', 0)
            ->where('venda.consignado', 0)
            ->whereBetween('venda.data_registro', [$inicio, $fim]);
        $this->aplicarFiltroLocal($pdv, $localId, 'venda.filial_id');

        $custoNfe = (float) $nfe->sum(DB::raw('item.valor_custo * item.quantidade'));
        $custoPdv = (float) $pdv->sum(DB::raw('item.valor_custo * item.quantidade'));

        return $custoNfe + $custoPdv;
    }

    private function indicadoresFinanceiros($empresaId, $localId, Carbon $inicio, Carbon $fim)
    {
        $receber = ContaReceber::query()
            ->where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio->toDateString(), $fim->toDateString()]);
        $this->aplicarFiltroLocal($receber, $localId, 'filial_id');

        $pagar = ContaPagar::query()
            ->where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio->toDateString(), $fim->toDateString()]);
        $this->aplicarFiltroLocal($pagar, $localId, 'filial_id');

        $saldoReceber = DB::raw('GREATEST(valor_integral - COALESCE(valor_recebido, 0), 0)');
        $saldoPagar = DB::raw('GREATEST(valor_integral - COALESCE(valor_pago, 0), 0)');
        $limiteVencido = Carbon::today()->lt($fim) ? Carbon::today() : (clone $fim)->startOfDay();

        return [
            'contas_receber_abertas' => (float) (clone $receber)
                ->where('status', 0)
                ->sum($saldoReceber),
            'contas_receber_vencidas' => (float) (clone $receber)
                ->where('status', 0)
                ->whereDate('data_vencimento', '<', $limiteVencido->toDateString())
                ->sum($saldoReceber),
            'contas_pagar_abertas' => (float) (clone $pagar)
                ->where('status', 0)
                ->sum($saldoPagar),
            'despesas_pagas' => (float) (clone $pagar)->sum('valor_pago'),
        ];
    }

    private function produtosMaisRelevantes($empresaId, $localId, Carbon $inicio, Carbon $fim, $faturamentoTotal)
    {
        $nfe = DB::table('item_vendas as item')
            ->join('vendas as venda', 'venda.id', '=', 'item.venda_id')
            ->join('produtos as produto', 'produto.id', '=', 'item.produto_id')
            ->where('venda.empresa_id', $empresaId)
            ->where('venda.estado_emissao', '!=', 'cancelado')
            ->whereBetween('venda.data_registro', [$inicio, $fim])
            ->selectRaw('produto.id as produto_id, produto.nome, SUM(item.quantidade) as quantidade, SUM(item.valor * item.quantidade) as faturamento')
            ->groupBy('produto.id', 'produto.nome');
        $this->aplicarFiltroLocal($nfe, $localId, 'venda.filial_id');

        $pdv = DB::table('item_venda_caixas as item')
            ->join('venda_caixas as venda', 'venda.id', '=', 'item.venda_caixa_id')
            ->join('produtos as produto', 'produto.id', '=', 'item.produto_id')
            ->where('venda.empresa_id', $empresaId)
            ->where('venda.estado_emissao', '!=', 'cancelado')
            ->where('venda.rascunho', 0)
            ->where('venda.consignado', 0)
            ->whereBetween('venda.data_registro', [$inicio, $fim])
            ->selectRaw('produto.id as produto_id, produto.nome, SUM(item.quantidade) as quantidade, SUM(item.valor * item.quantidade) as faturamento')
            ->groupBy('produto.id', 'produto.nome');
        $this->aplicarFiltroLocal($pdv, $localId, 'venda.filial_id');

        return $nfe->get()
            ->merge($pdv->get())
            ->groupBy('produto_id')
            ->map(function ($linhas) use ($faturamentoTotal) {
                $faturamento = (float) $linhas->sum('faturamento');

                return [
                    'produto_id' => (int) $linhas->first()->produto_id,
                    'nome' => $linhas->first()->nome,
                    'quantidade' => round((float) $linhas->sum('quantidade'), 3),
                    'faturamento' => round($faturamento, 2),
                    'participacao' => $faturamentoTotal > 0
                        ? round(($faturamento / $faturamentoTotal) * 100, 2)
                        : 0,
                ];
            })
            ->sortByDesc('faturamento')
            ->take(5)
            ->values();
    }

    private function aplicarFiltroLocal($query, $localId, $coluna)
    {
        if ($localId === null || $localId === '' || $localId === 'todos') {
            return $query;
        }

        if ((string) $localId === '-1') {
            return $query->whereNull($coluna);
        }

        return $query->where($coluna, (int) $localId);
    }

    private function variacaoPercentual($atual, $anterior)
    {
        $atual = (float) $atual;
        $anterior = (float) $anterior;

        if (abs($anterior) < 0.01) {
            return $atual > 0 ? null : 0;
        }

        return round((($atual - $anterior) / abs($anterior)) * 100, 2);
    }

    private function calcularSaudeDoNegocio($crescimento, $margem, $inadimplencia, $resultado, $cobertura, $concentracao)
    {
        $score = 100;

        if ($crescimento !== null) {
            $score -= $crescimento <= -10 ? 20 : ($crescimento < 0 ? 10 : 0);
        }

        $score -= $margem < 10 ? 25 : ($margem < 20 ? 15 : 0);
        $score -= $inadimplencia > 30 ? 25 : ($inadimplencia > 15 ? 10 : 0);
        $score -= $resultado < 0 ? 15 : 0;

        if ($cobertura !== null) {
            $score -= $cobertura < 0.8 ? 15 : ($cobertura < 1.2 ? 5 : 0);
        }

        $score -= $concentracao > 50 ? 10 : 0;
        $score = max(0, min(100, $score));

        if ($score >= 80) {
            $classificacao = 'Saudável';
            $nivel = 'success';
        } elseif ($score >= 60) {
            $classificacao = 'Atenção';
            $nivel = 'warning';
        } else {
            $classificacao = 'Risco';
            $nivel = 'danger';
        }

        return [
            'score' => $score,
            'classificacao' => $classificacao,
            'nivel' => $nivel,
        ];
    }

    private function gerarRecomendacoes($atual, $crescimento, $financeiro, $inadimplencia, $resultado, $cobertura, $concentracao)
    {
        $recomendacoes = [];

        if ($atual['quantidade_vendas'] === 0) {
            $recomendacoes[] = $this->recomendacao(
                'warning',
                'Sem vendas no período',
                'Não existem vendas válidas para analisar no intervalo selecionado.',
                'Revise o período e, se estiver correto, priorize ações comerciais imediatas.'
            );
        } elseif ($crescimento !== null && $crescimento < 0) {
            $recomendacoes[] = $this->recomendacao(
                $crescimento <= -10 ? 'danger' : 'warning',
                'Faturamento em queda',
                'O faturamento caiu ' . number_format(abs($crescimento), 1, ',', '.') . '% em relação ao período anterior equivalente.',
                'Analise produtos, vendedores, clientes e canais que perderam receita.'
            );
        } elseif ($crescimento !== null && $crescimento >= 10) {
            $recomendacoes[] = $this->recomendacao(
                'success',
                'Crescimento consistente',
                'O faturamento cresceu ' . number_format($crescimento, 1, ',', '.') . '% sobre o período anterior equivalente.',
                'Identifique os produtos e canais responsáveis para repetir o resultado.'
            );
        }

        if ($atual['margem_bruta'] < 15 && $atual['faturamento'] > 0) {
            $recomendacoes[] = $this->recomendacao(
                'danger',
                'Margem bruta baixa',
                'A margem estimada está em ' . number_format($atual['margem_bruta'], 1, ',', '.') . '%.',
                'Revise preços, descontos, custos cadastrados e mix de produtos.'
            );
        } elseif ($atual['margem_bruta'] < 25 && $atual['faturamento'] > 0) {
            $recomendacoes[] = $this->recomendacao(
                'warning',
                'Margem exige atenção',
                'A margem estimada está abaixo de 25%.',
                'Priorize produtos mais rentáveis e controle descontos concedidos.'
            );
        }

        if ($inadimplencia > 15) {
            $recomendacoes[] = $this->recomendacao(
                $inadimplencia > 30 ? 'danger' : 'warning',
                'Inadimplência elevada',
                number_format($inadimplencia, 1, ',', '.') . '% das contas abertas do período já estão vencidas.',
                'Organize uma régua de cobrança e revise limites e prazos de crédito.'
            );
        }

        if ($resultado < 0 && $atual['faturamento'] > 0) {
            $recomendacoes[] = $this->recomendacao(
                'danger',
                'Resultado operacional negativo',
                'O lucro bruto estimado não cobriu as despesas pagas do período.',
                'Reduza despesas não essenciais e revise margem e volume de vendas.'
            );
        }

        if ($cobertura !== null && $cobertura < 1) {
            $recomendacoes[] = $this->recomendacao(
                'warning',
                'Pressão de caixa futura',
                'As contas a receber abertas não cobrem integralmente as contas a pagar abertas do período.',
                'Replaneje vencimentos, acelere recebimentos e preserve capital de giro.'
            );
        }

        if ($concentracao > 45) {
            $recomendacoes[] = $this->recomendacao(
                'warning',
                'Receita concentrada em um produto',
                'O produto líder representa ' . number_format($concentracao, 1, ',', '.') . '% do faturamento analisado.',
                'Diversifique o mix e acompanhe estoque e margem desse produto.'
            );
        }

        if (empty($recomendacoes)) {
            $recomendacoes[] = $this->recomendacao(
                'success',
                'Indicadores equilibrados',
                'Os principais indicadores do período não apresentam alertas relevantes.',
                'Mantenha o acompanhamento e compare os resultados por filial, produto e período.'
            );
        }

        return array_slice($recomendacoes, 0, 6);
    }

    private function recomendacao($nivel, $titulo, $descricao, $acao)
    {
        return compact('nivel', 'titulo', 'descricao', 'acao');
    }
}
