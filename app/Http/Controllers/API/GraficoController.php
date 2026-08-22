<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\PedidoEcommerce;
use App\Models\Produto;
use App\Models\ServicoOs;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GraficoController extends Controller
{
    private function getDatas(Request $request): array
    {
        $request->validate([
            'empresa_id' => ['required', 'integer', 'min:1'],
            'data_inicial' => ['nullable', 'date'],
            'data_final' => ['nullable', 'date', 'after_or_equal:data_inicial'],
            'local_id' => ['nullable'],
        ]);

        return [
            'inicio' => $request->filled('data_inicial')
                ? Carbon::parse($request->data_inicial)->startOfDay()
                : Carbon::now()->startOfMonth(),
            'fim' => $request->filled('data_final')
                ? Carbon::parse($request->data_final)->endOfDay()
                : Carbon::now()->endOfMonth(),
        ];
    }

    private function empresaId(Request $request): int
    {
        return (int) $request->input('empresa_id');
    }

    private function localId(Request $request)
    {
        return $request->input('local_id', 'todos');
    }

    private function aplicarFiltroLocal($query, $localId, string $coluna = 'filial_id'): void
    {
        if ($localId === null || $localId === '' || $localId === 'todos') {
            return;
        }

        if ((string) $localId === '-1') {
            $query->whereNull($coluna);
            return;
        }

        $query->where($coluna, (int) $localId);
    }

    private function aplicarVendaValida($query, string $prefixo = '', bool $pdv = false): void
    {
        $estado = $prefixo . 'estado_emissao';

        $query->where(function ($q) use ($estado) {
            $q->whereNull($estado)
                ->orWhereRaw("LOWER(TRIM({$estado})) <> ?", ['cancelado']);
        });

        if (!$pdv) {
            return;
        }

        static $temRascunho = null;
        static $temConsignado = null;

        $temRascunho ??= Schema::hasColumn('venda_caixas', 'rascunho');
        $temConsignado ??= Schema::hasColumn('venda_caixas', 'consignado');

        if ($temRascunho) {
            $query->where(function ($q) use ($prefixo) {
                $q->whereNull($prefixo . 'rascunho')
                    ->orWhere($prefixo . 'rascunho', 0);
            });
        }

        if ($temConsignado) {
            $query->where(function ($q) use ($prefixo) {
                $q->whereNull($prefixo . 'consignado')
                    ->orWhere($prefixo . 'consignado', 0);
            });
        }
    }

    private function vendaQuery(string $model, int $empresaId, $localId, Carbon $inicio, Carbon $fim, bool $pdv = false)
    {
        $query = $model::query()
            ->where('empresa_id', $empresaId)
            ->whereBetween('data_registro', [$inicio, $fim]);

        $this->aplicarFiltroLocal($query, $localId);
        $this->aplicarVendaValida($query, '', $pdv);

        return $query;
    }

    private function ecommerceAprovadoQuery(int $empresaId, Carbon $inicio, Carbon $fim)
    {
        return PedidoEcommerce::query()
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$inicio, $fim])
            ->whereIn(DB::raw('LOWER(status_pagamento)'), ['approved', 'pago', 'paid']);
    }

    private function expressaoPeriodo(string $coluna, bool $diario): string
    {
        return $diario
            ? "DATE({$coluna})"
            : "DATE_FORMAT({$coluna}, '%Y-%m')";
    }

    private function mapaAgregado(Collection $dados): Collection
    {
        return $dados->mapWithKeys(fn ($item) => [
            (string) $item->periodo => (float) $item->total,
        ]);
    }

    private function montarSeriePeriodo(Carbon $inicio, Carbon $fim, bool $diario, Collection ...$mapas): array
    {
        $labels = [];
        $chaves = [];
        $cursor = $diario ? $inicio->copy()->startOfDay() : $inicio->copy()->startOfMonth();
        $limite = $diario ? $fim->copy()->startOfDay() : $fim->copy()->startOfMonth();
        $meses = [1 => 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        while ($cursor->lte($limite)) {
            $chave = $diario ? $cursor->format('Y-m-d') : $cursor->format('Y-m');
            $labels[] = $diario
                ? $cursor->format('d/m')
                : $meses[(int) $cursor->format('n')] . '/' . $cursor->format('y');
            $chaves[] = $chave;
            $diario ? $cursor->addDay() : $cursor->addMonth();
        }

        $series = [];
        foreach ($mapas as $mapa) {
            $series[] = collect($chaves)
                ->map(fn ($chave) => round((float) $mapa->get($chave, 0), 2))
                ->all();
        }

        return [$labels, $series];
    }

    public function vendasAnual(Request $request)
    {
        $datas = $this->getDatas($request);
        $empresaId = $this->empresaId($request);
        $localId = $this->localId($request);
        $diario = $datas['inicio']->diffInDays($datas['fim']) <= 62;
        $expressao = $this->expressaoPeriodo('data_registro', $diario);

        $erpQuery = $this->vendaQuery(Venda::class, $empresaId, $localId, $datas['inicio'], $datas['fim']);
        $erp = $erpQuery
            ->selectRaw("{$expressao} as periodo, SUM(valor_total) as total")
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $pdvQuery = $this->vendaQuery(VendaCaixa::class, $empresaId, $localId, $datas['inicio'], $datas['fim'], true);
        $pdv = $pdvQuery
            ->selectRaw("{$expressao} as periodo, SUM(valor_total) as total")
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $expressaoEcommerce = $this->expressaoPeriodo('created_at', $diario);
        $ecommerce = $this->ecommerceAprovadoQuery($empresaId, $datas['inicio'], $datas['fim'])
            ->where(function ($query) {
                $query->whereNull('venda_id')->orWhere('venda_id', 0);
            })
            ->selectRaw("{$expressaoEcommerce} as periodo, SUM(valor_total) as total")
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        [$labels, $series] = $this->montarSeriePeriodo(
            $datas['inicio'],
            $datas['fim'],
            $diario,
            $this->mapaAgregado($erp),
            $this->mapaAgregado($pdv),
            $this->mapaAgregado($ecommerce)
        );

        $total = collect($series[0])->map(function ($valor, $indice) use ($series) {
            return round($valor + $series[1][$indice] + $series[2][$indice], 2);
        })->all();

        return response()->json([
            'meses' => $labels,
            'vendas_erp' => $series[0],
            'vendas_pdv' => $series[1],
            'vendas_ecommerce' => $series[2],
            'somaVendas' => $total,
        ]);
    }

    public function curvaABC(Request $request)
    {
        $datas = $this->getDatas($request);
        $empresaId = $this->empresaId($request);
        $localId = $this->localId($request);

        $pdv = DB::table('item_venda_caixas as item')
            ->join('venda_caixas as venda', 'venda.id', '=', 'item.venda_caixa_id')
            ->selectRaw('item.produto_id, SUM(item.valor * item.quantidade) as faturamento')
            ->where('venda.empresa_id', $empresaId)
            ->whereBetween('venda.data_registro', [$datas['inicio'], $datas['fim']])
            ->whereNotNull('item.produto_id')
            ->groupBy('item.produto_id');
        $this->aplicarFiltroLocal($pdv, $localId, 'venda.filial_id');
        $this->aplicarVendaValida($pdv, 'venda.', true);

        $erp = DB::table('item_vendas as item')
            ->join('vendas as venda', 'venda.id', '=', 'item.venda_id')
            ->selectRaw('item.produto_id, SUM(item.valor * item.quantidade) as faturamento')
            ->where('venda.empresa_id', $empresaId)
            ->whereBetween('venda.data_registro', [$datas['inicio'], $datas['fim']])
            ->whereNotNull('item.produto_id')
            ->groupBy('item.produto_id');
        $this->aplicarFiltroLocal($erp, $localId, 'venda.filial_id');
        $this->aplicarVendaValida($erp, 'venda.');

        $faturamentoProdutos = DB::query()
            ->fromSub($pdv->unionAll($erp), 'vendas_produtos')
            ->selectRaw('produto_id, SUM(faturamento) as faturamento_total')
            ->groupBy('produto_id')
            ->orderByDesc('faturamento_total')
            ->limit(15)
            ->get();

        $faturamentoGlobal = (float) $faturamentoProdutos->sum('faturamento_total');
        if ($faturamentoGlobal <= 0) {
            return response()->json(['curva_abc_produtos' => []]);
        }

        $produtos = Produto::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $faturamentoProdutos->pluck('produto_id'))
            ->pluck('nome', 'id');

        $acumulado = 0.0;
        $retorno = $faturamentoProdutos->map(function ($item) use (&$acumulado, $faturamentoGlobal, $produtos) {
            $faturamento = (float) $item->faturamento_total;
            $acumulado += $faturamento;

            return [
                'produto_nome' => $produtos[$item->produto_id] ?? 'Produto não identificado',
                'faturamento' => round($faturamento, 2),
                'porcentagem_acumulada' => round(($acumulado / $faturamentoGlobal) * 100, 2),
            ];
        });

        return response()->json(['curva_abc_produtos' => $retorno->values()]);
    }

    private function contaReceberQuery(int $empresaId, $localId, Carbon $inicio, Carbon $fim)
    {
        $query = ContaReceber::query()
            ->where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio->toDateString(), $fim->toDateString()]);
        $this->aplicarFiltroLocal($query, $localId);

        return $query;
    }

    private function contaPagarQuery(int $empresaId, $localId, Carbon $inicio, Carbon $fim)
    {
        $query = ContaPagar::query()
            ->where('empresa_id', $empresaId)
            ->whereBetween('data_vencimento', [$inicio->toDateString(), $fim->toDateString()]);
        $this->aplicarFiltroLocal($query, $localId);

        return $query;
    }

    private function totalRecebido($query): float
    {
        return (float) (clone $query)->sum(DB::raw(
            'CASE WHEN status = 1 AND COALESCE(valor_recebido, 0) = 0 '
            . 'THEN valor_integral ELSE COALESCE(valor_recebido, 0) END'
        ));
    }

    private function totalPago($query): float
    {
        return (float) (clone $query)->sum(DB::raw(
            'CASE WHEN status = 1 AND COALESCE(valor_pago, 0) = 0 '
            . 'THEN valor_integral ELSE COALESCE(valor_pago, 0) END'
        ));
    }

    private function totalAReceber($query): float
    {
        return (float) (clone $query)
            ->where('status', 0)
            ->sum(DB::raw('GREATEST(valor_integral - COALESCE(valor_recebido, 0), 0)'));
    }

    private function totalAPagar($query): float
    {
        return (float) (clone $query)
            ->where('status', 0)
            ->sum(DB::raw('GREATEST(valor_integral - COALESCE(valor_pago, 0), 0)'));
    }

    public function contasReceber(Request $request)
    {
        $datas = $this->getDatas($request);
        $query = $this->contaReceberQuery(
            $this->empresaId($request),
            $this->localId($request),
            $datas['inicio'],
            $datas['fim']
        );

        $recebidas = $this->totalRecebido($query);
        $receber = $this->totalAReceber($query);
        $total = $recebidas + $receber;

        return response()->json([
            'recebidas' => round($recebidas, 2),
            'receber' => round($receber, 2),
            'percentual' => $total > 0 ? round(($recebidas / $total) * 100, 1) : 0,
        ]);
    }

    public function contasPagar(Request $request)
    {
        $datas = $this->getDatas($request);
        $query = $this->contaPagarQuery(
            $this->empresaId($request),
            $this->localId($request),
            $datas['inicio'],
            $datas['fim']
        );

        $pagos = $this->totalPago($query);
        $pagar = $this->totalAPagar($query);
        $total = $pagos + $pagar;

        return response()->json([
            'pagos' => round($pagos, 2),
            'pagar' => round($pagar, 2),
            'percentual' => $total > 0 ? round(($pagos / $total) * 100, 1) : 0,
        ]);
    }

    public function fluxoAnual(Request $request)
    {
        $datas = $this->getDatas($request);
        $empresaId = $this->empresaId($request);
        $localId = $this->localId($request);
        $diario = $datas['inicio']->diffInDays($datas['fim']) <= 62;

        $dataRecebimento = 'COALESCE(data_recebimento, data_vencimento)';
        $periodoRecebimento = $this->expressaoPeriodo($dataRecebimento, $diario);
        $recebimentos = ContaReceber::query()
            ->where('empresa_id', $empresaId)
            ->where(function ($query) {
                $query->where('status', 1)->orWhere('valor_recebido', '>', 0);
            })
            ->whereBetween(DB::raw($dataRecebimento), [$datas['inicio'], $datas['fim']]);
        $this->aplicarFiltroLocal($recebimentos, $localId);
        $recebimentos = $recebimentos
            ->selectRaw("{$periodoRecebimento} as periodo, SUM(CASE WHEN COALESCE(valor_recebido, 0) = 0 THEN valor_integral ELSE valor_recebido END) as total")
            ->groupBy('periodo')
            ->get();

        $dataPagamento = 'COALESCE(data_pagamento, data_vencimento)';
        $periodoPagamento = $this->expressaoPeriodo($dataPagamento, $diario);
        $pagamentos = ContaPagar::query()
            ->where('empresa_id', $empresaId)
            ->where(function ($query) {
                $query->where('status', 1)->orWhere('valor_pago', '>', 0);
            })
            ->whereBetween(DB::raw($dataPagamento), [$datas['inicio'], $datas['fim']]);
        $this->aplicarFiltroLocal($pagamentos, $localId);
        $pagamentos = $pagamentos
            ->selectRaw("{$periodoPagamento} as periodo, SUM(CASE WHEN COALESCE(valor_pago, 0) = 0 THEN valor_integral ELSE valor_pago END) as total")
            ->groupBy('periodo')
            ->get();

        [$labels, $series] = $this->montarSeriePeriodo(
            $datas['inicio'],
            $datas['fim'],
            $diario,
            $this->mapaAgregado($recebimentos),
            $this->mapaAgregado($pagamentos)
        );

        $dados = collect($labels)->map(function ($label, $indice) use ($series) {
            $entrada = (float) $series[0][$indice];
            $saida = (float) $series[1][$indice];

            return [
                'label' => $label,
                'entrada' => round($entrada, 2),
                'saida' => round($saida, 2),
                'saldo' => round($entrada - $saida, 2),
            ];
        });

        return response()->json(['dados' => $dados]);
    }

    public function contasPagarCategorias(Request $request)
    {
        $datas = $this->getDatas($request);
        $query = ContaPagar::query()
            ->selectRaw('categoria_id, SUM(valor_integral) as total')
            ->where('empresa_id', $this->empresaId($request))
            ->whereBetween('data_vencimento', [$datas['inicio']->toDateString(), $datas['fim']->toDateString()]);
        $this->aplicarFiltroLocal($query, $this->localId($request));

        $categorias = $query
            ->groupBy('categoria_id')
            ->with('categoria:id,nome')
            ->orderByDesc('total')
            ->get();

        $principais = $categorias->take(8);
        $outros = (float) $categorias->skip(8)->sum('total');

        $labels = $principais->map(fn ($item) => $item->categoria->nome ?? 'Sem categoria')->values();
        $valores = $principais->map(fn ($item) => round((float) $item->total, 2))->values();

        if ($outros > 0) {
            $labels->push('Outros');
            $valores->push(round($outros, 2));
        }

        return response()->json([
            'labels' => $labels,
            'valores' => $valores,
        ]);
    }

    public function getDataCards(Request $request)
    {
        $datas = $this->getDatas($request);
        $empresaId = $this->empresaId($request);
        $localId = $this->localId($request);

        $vendasErp = $this->vendaQuery(Venda::class, $empresaId, $localId, $datas['inicio'], $datas['fim']);
        $vendasPdv = $this->vendaQuery(VendaCaixa::class, $empresaId, $localId, $datas['inicio'], $datas['fim'], true);
        $ecommerce = $this->ecommerceAprovadoQuery($empresaId, $datas['inicio'], $datas['fim']);
        $ecommerceNaoIntegrado = (clone $ecommerce)->where(function ($query) {
            $query->whereNull('venda_id')->orWhere('venda_id', 0);
        });

        $valorErp = (float) (clone $vendasErp)->sum('valor_total');
        $valorPdv = (float) (clone $vendasPdv)->sum('valor_total');
        $valorEcommerce = (float) (clone $ecommerce)->sum('valor_total');
        $valorEcommerceNaoIntegrado = (float) (clone $ecommerceNaoIntegrado)->sum('valor_total');

        $quantidadeVendas = (int) (clone $vendasErp)->count()
            + (int) (clone $vendasPdv)->count()
            + (int) (clone $ecommerceNaoIntegrado)->count();
        $faturamento = $valorErp + $valorPdv + $valorEcommerceNaoIntegrado;

        $canceladasErp = Venda::query()
            ->where('empresa_id', $empresaId)
            ->whereBetween('data_registro', [$datas['inicio'], $datas['fim']])
            ->whereRaw('LOWER(TRIM(estado_emissao)) = ?', ['cancelado']);
        $this->aplicarFiltroLocal($canceladasErp, $localId);

        $canceladasPdv = VendaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->whereBetween('data_registro', [$datas['inicio'], $datas['fim']])
            ->whereRaw('LOWER(TRIM(estado_emissao)) = ?', ['cancelado']);
        $this->aplicarFiltroLocal($canceladasPdv, $localId);

        $receberQuery = $this->contaReceberQuery($empresaId, $localId, $datas['inicio'], $datas['fim']);
        $pagarQuery = $this->contaPagarQuery($empresaId, $localId, $datas['inicio'], $datas['fim']);
        $contasReceberAbertas = $this->totalAReceber($receberQuery);
        $contasReceberRecebidas = $this->totalRecebido($receberQuery);
        $contasPagarAbertas = $this->totalAPagar($pagarQuery);
        $contasPagarPagas = $this->totalPago($pagarQuery);
        $contasReceberVencidas = (float) (clone $receberQuery)
            ->where('status', 0)
            ->whereDate('data_vencimento', '<', Carbon::today())
            ->sum(DB::raw('GREATEST(valor_integral - COALESCE(valor_recebido, 0), 0)'));

        $servicosOs = ServicoOs::query()
            ->whereBetween('servico_os.created_at', [$datas['inicio'], $datas['fim']])
            ->whereHas('ordemServico', function ($query) use ($empresaId, $localId) {
                $query->where('empresa_id', $empresaId);
                $this->aplicarFiltroLocal($query, $localId);
            });

        $osTotal = (float) (clone $servicosOs)->sum(DB::raw(
            'COALESCE(sub_total, COALESCE(valor_unitario, 0) * COALESCE(quantidade, 1))'
        ));
        $osQuantidade = (int) (clone $servicosOs)->count();
        $osPorStatus = (clone $servicosOs)
            ->selectRaw('status, COUNT(*) as total, SUM(COALESCE(sub_total, COALESCE(valor_unitario, 0) * COALESCE(quantidade, 1))) as valor_total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $produtos = Produto::query()->where('empresa_id', $empresaId);

        return response()->json([
            'vendas' => round($faturamento, 2),
            'vendas_erp' => round($valorErp, 2),
            'vendas_pdv' => round($valorPdv, 2),
            'vendas_ecommerce' => round($valorEcommerce, 2),
            'vendas_ecommerce_nao_integradas' => round($valorEcommerceNaoIntegrado, 2),
            'quantidade_vendas' => $quantidadeVendas,
            'ticket_medio' => $quantidadeVendas > 0 ? round($faturamento / $quantidadeVendas, 2) : 0,
            'vendas_canceladas' => (int) $canceladasErp->count() + (int) $canceladasPdv->count(),
            'produtos' => (int) $produtos->count(),
            'saldo_financeiro' => round($contasReceberRecebidas - $contasPagarPagas, 2),
            'conta_pagar_abertas' => round($contasPagarAbertas, 2),
            'conta_pagar_pagas' => round($contasPagarPagas, 2),
            'conta_receber_abertas' => round($contasReceberAbertas, 2),
            'conta_receber_recebidas' => round($contasReceberRecebidas, 2),
            'conta_receber_vencidas' => round($contasReceberVencidas, 2),
            'perfil_id' => Empresa::whereKey($empresaId)->value('perfil_id'),
            'servico_os' => [
                'total_valor' => round($osTotal, 2),
                'quantidade' => $osQuantidade,
                'por_status' => $osPorStatus,
            ],
        ]);
    }

    public function produtos(Request $request)
    {
        $datas = $this->getDatas($request);
        $empresaId = $this->empresaId($request);
        $localId = $this->localId($request);
        $meses = [1 => 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $labels = [];
        $cadastrados = [];
        $vendidos = [];
        $semVenda = [];
        $cursor = $datas['inicio']->copy()->startOfMonth();
        $limite = $datas['fim']->copy()->startOfMonth();

        $totalProdutosQuery = Produto::query()->where('empresa_id', $empresaId);
        $totalProdutos = (int) $totalProdutosQuery->count();

        while ($cursor->lte($limite)) {
            $inicioMes = $cursor->copy()->startOfMonth();
            $fimMes = $cursor->copy()->endOfMonth();

            $cadastrados[] = Produto::query()
                ->where('empresa_id', $empresaId)
                ->whereBetween('created_at', [$inicioMes, $fimMes])
                ->count();

            $pdv = DB::table('item_venda_caixas as item')
                ->join('venda_caixas as venda', 'venda.id', '=', 'item.venda_caixa_id')
                ->select('item.produto_id')
                ->where('venda.empresa_id', $empresaId)
                ->whereBetween('venda.data_registro', [$inicioMes, $fimMes])
                ->whereNotNull('item.produto_id');
            $this->aplicarFiltroLocal($pdv, $localId, 'venda.filial_id');
            $this->aplicarVendaValida($pdv, 'venda.', true);

            $erp = DB::table('item_vendas as item')
                ->join('vendas as venda', 'venda.id', '=', 'item.venda_id')
                ->select('item.produto_id')
                ->where('venda.empresa_id', $empresaId)
                ->whereBetween('venda.data_registro', [$inicioMes, $fimMes])
                ->whereNotNull('item.produto_id');
            $this->aplicarFiltroLocal($erp, $localId, 'venda.filial_id');
            $this->aplicarVendaValida($erp, 'venda.');

            $quantidadeVendidos = DB::query()
                ->fromSub($pdv->union($erp), 'produtos_vendidos')
                ->distinct()
                ->count('produto_id');

            $vendidos[] = $quantidadeVendidos;
            $semVenda[] = max(0, $totalProdutos - $quantidadeVendidos);
            $labels[] = $meses[(int) $cursor->format('n')] . '/' . $cursor->format('y');
            $cursor->addMonth();
        }

        return response()->json([
            'meses' => $labels,
            'somaCadastradoMes' => $cadastrados,
            'somaVendidosNoDia' => $vendidos,
            'somaNaoVendidos' => $semVenda,
        ]);
    }
}