<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\ComissaoVenda;
use App\Models\Compra;
use App\Models\ConfigNota;
use App\Models\ContaReceber;
use App\Models\Cte;
use App\Models\Estoque;
use App\Models\Funcionario;
use App\Models\ItemCompra;
use App\Models\ItemVenda;
use App\Models\ItemVendaCaixa;
use App\Models\ListaPreco;
use App\Models\Marca;
use App\Models\Devolucao;
use App\Models\Mdfe;
use App\Models\VendaCaixa;
use App\Models\RemessaNfe;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\SubCategoria;
use App\Models\Empresa;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Carbon\Carbon;

class RelatorioController extends Controller
{
	protected $empresa_id = null;
	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			$this->empresa_id = $request->empresa_id;
			$value = session('user_logged');
			if (!$value) {
				return redirect("/login");
			}
			return $next($request);
		});
	}

public function index(Request $request)
{
    $vendedor = Funcionario::where('empresa_id', $request->empresa_id)->get();
    $listaPreco = ListaPreco::where('empresa_id', $request->empresa_id)->get();
    $empresa = Empresa::where('id', $request->empresa_id)->first(); // <-- use first() para um único registro
   //dd($empresa);
    $marca = Marca::where('empresa_id', $request->empresa_id)->get();
    $categoria = Categoria::where('empresa_id', $request->empresa_id)->get();
    $sub_categoria = SubCategoria::all();
    $naturezaOperacao = NaturezaOperacao::where('empresa_id', $request->empresa_id)->get();
    
    $start_date = $request->get('start_date');
    $end_date = $request->get('end_date');
    $tipo_pagamento = $request->get('tipo_pagamento');

    $clientes = Cliente::where('empresa_id', $request->empresa_id)->get();
    $produtos = Produto::where('empresa_id', $request->empresa_id)->get();
    $cfops = $this->getCfopDistintos();

    $data = Venda::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('data_emissao', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('data_emissao', '<=', $end_date);
        })
        ->when(!empty($tipo_pagamento), function ($query) use ($tipo_pagamento) {
            return $query->where('tipo_pagamento', $tipo_pagamento);
        })
        ->orderBy('data_emissao', 'asc')
        ->paginate(env("PAGINACAO"));

    return view('relatorios.index', compact(
        'data',
        'vendedor',
        'listaPreco',
        'marca',
        'categoria',
        'sub_categoria',
        'naturezaOperacao',
        'clientes',
        'produtos',
        'cfops',
        'empresa' // <-- adiciona aqui
    ));
}
	private function getCfopDistintos()
	{
		$cfops1 = Produto::select(\DB::raw('distinct(CFOP_saida_estadual) as cfop'))
		->where('empresa_id', $this->empresa_id)
		->get();
		$cfops2 = Produto::select(\DB::raw('distinct(CFOP_saida_inter_estadual) as cfop'))
		->where('empresa_id', $this->empresa_id)
		->get();
		$cfops = [];
		foreach ($cfops1 as $c) {
			array_push($cfops, $c->cfop);
		}
		foreach ($cfops2 as $c) {
			if (!in_array($c->cfop, $cfops)) {
				array_push($cfops, $c->cfop);
			}
		}
		return $cfops;
	}

	public function somaVendas(Request $request)
	{
		$start_date = $request->start_date;
		$end_date = $request->end_date;
		$total_resultados = $request->total_resultados;
		$ordem = $request->ordem ?? 'desc';
		$vendas = Venda::select(\DB::raw('DATE_FORMAT(vendas.data_registro, "%d-%m-%Y") as data, sum(vendas.valor_total-vendas.desconto-vendas.acrescimo) as total'))
		->when(!empty($start_date), function ($query) use ($start_date) {
			return $query->whereDate('created_at', '>=', $start_date);
		})
		->when(!empty($end_date), function ($query) use ($end_date) {
			return $query->whereDate('created_at', '<=', $end_date);
		})
		->where('vendas.empresa_id', $request->empresa_id)
		->where('vendas.estado_emissao', '!=', 'cancelado')
		->groupBy('data')
		->orderBy($ordem == 'data' ? 'data' : 'total', $ordem == 'data' ? 'desc' : $ordem)
		->limit($total_resultados ?? 1000000)
		->get();

		$vendasCaixa = VendaCaixa::select(\DB::raw('DATE_FORMAT(venda_caixas.data_registro, "%d-%m-%Y") as data, sum(venda_caixas.valor_total) as total'))
		->when(!empty($start_date), function ($query) use ($start_date) {
			return $query->whereDate('created_at', '>=', $start_date);
		})
		->when(!empty($end_date), function ($query) use ($end_date) {
			return $query->whereDate('created_at', '<=', $end_date);
		})
		->where('venda_caixas.empresa_id', $request->empresa_id)
		->where('venda_caixas.estado_emissao', '!=', 'cancelado')
		->groupBy('data')
		->orderBy($ordem == 'data' ? 'data' : 'total', $ordem == 'data' ? 'desc' : $ordem)
		->limit($total_resultados ?? 1000000)
		->get();

		$arr = $this->uneArrayVendas($vendas, $vendasCaixa);
		if ($total_resultados) {
			$arr = array_slice($arr, 0, $total_resultados);
		}
		usort($arr, function ($a, $b) use ($ordem) {
			if ($ordem == 'asc') return $a['total'] > $b['total'] ? 1 : 0;
			else if ($ordem == 'desc') return $a['total'] < $b['total'] ? 1 : 0;
			else return $a['data'] < $b['data'] ? 1 : 0;
		});
		if (sizeof($arr) == 0) {
			session()->flash("flash_erro", "Relatório sem registro!");
			return redirect('/relatorios');
		}
		$p = view('relatorios.relatorio_somatorio_venda')
		->with('ordem', $ordem == 'asc' ? 'Menos' : 'Mais')
		->with('data_inicial', $request->data_inicial)
		->with('data_final', $request->data_final)
		->with('title', 'Relatório Somatório de vendas')
		->with('vendas', $arr);
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Somatório Somatório de vendas.pdf", array("Attachment" => false));
	}

public function filtroCompras(Request $request)
{
    $start_date = $request->start_date;
    $end_date = $request->end_date;
    $total_resultados = $request->total_resultados ?? 1000; // Limite padrão
    

    $compras = Compra::select(\DB::raw('
        DATE_FORMAT(compras.created_at, "%d-%m-%Y") as data, 
        SUM(compras.total) as total, 
        COUNT(id) as compras_diarias
    '))
    ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
        return $query->whereBetween('compras.created_at', [$start_date, $end_date]);
    })
    ->where('estado', '!=', 'cancelado')
    ->where('empresa_id', $request->empresa_id)
    ->groupBy('data')
    ->limit($total_resultados)
    ->get();

    if (count($compras) === 0) {
        session()->flash("flash_erro", "Relatório sem registro!");
        return redirect('/relatorios');
    }

    $view = view('relatorios.relatorio_compra', [
        'data_inicial' => $request->start_date,
        'data_final' => $request->end_date,
        'title' => 'Relatório de compras',
        'compras' => $compras
    ]);

    $domPdf = new Dompdf(["enable_remote" => true]);
    $domPdf->loadHtml($view);
    $domPdf->setPaper("A4");
    $domPdf->render();
    return $domPdf->stream("Relatório de compras.pdf", ["Attachment" => false]);
}

	private function uneArrayVendas($vendas, $vendasCaixa)
	{
		$adicionados = [];
		$arr = [];
		foreach ($vendas as $v) {
			$temp = [
				'data' => $v->data,
				'total' => $v->total,
				// 'itens' => $v->itens
			];
			array_push($adicionados, $v->data);
			array_push($arr, $temp);
		}
		foreach ($vendasCaixa as $v) {
			if (!in_array($v->data, $adicionados)) {
				$temp = [
					'data' => $v->data,
					'total' => $v->total,
					// 'itens' => $v->itens
				];
				array_push($adicionados, $v->data);
				array_push($arr, $temp);
			} else {
				for ($aux = 0; $aux < count($arr); $aux++) {
					if ($arr[$aux]['data'] == $v->data) {
						$arr[$aux]['total'] += $v->total;
						// $arr[$aux]['itens'] += $i->itens;
					}
				}
			}
		}
		return $arr;
	}



public function relatorioVendasGeral(Request $request)
{
    $start = $request->start_date;
    $end   = $request->end_date;

    if (!$start || !$end) {
        return back()->with('flash_erro', 'Informe o período!');
    }

    $empresa_id = $request->empresa_id;

    if (!$empresa_id) {
        return back()->with('flash_erro', 'Empresa não informada!');
    }

    // 🔒 Limita período (evita travar sistema)
    if (Carbon::parse($start)->diffInDays($end) > 90) {
        return back()->with('flash_erro', 'Período máximo de 90 dias!');
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY PEDIDOS
    |--------------------------------------------------------------------------
    */
    $q1 = DB::table('vendas')
        ->select([
            'vendas.id',
            'vendas.created_at',
            DB::raw("COALESCE(clientes.razao_social, 'Consumidor final') as cliente"),
            'vendas.valor_total',
            'vendas.desconto',
            DB::raw("(vendas.valor_total - vendas.desconto) as valor_liquido"),

            // 🔥 FORMA DE PAGAMENTO
            DB::raw("
                CASE vendas.tipo_pagamento
                    WHEN '01' THEN 'Dinheiro'
                    WHEN '02' THEN 'Cheque'
                    WHEN '03' THEN 'Cartão de Crédito'
                    WHEN '04' THEN 'Cartão de Débito'
                    WHEN '05' THEN 'Crédito Loja'
                    WHEN '06' THEN 'Crediário'
                    WHEN '10' THEN 'Vale Alimentação'
                    WHEN '11' THEN 'Vale Refeição'
                    WHEN '12' THEN 'Vale Presente'
                    WHEN '13' THEN 'Vale Combustível'
                    WHEN '14' THEN 'Duplicata Mercantil'
                    WHEN '15' THEN 'Boleto Bancário'
                    WHEN '16' THEN 'Depósito Bancário'
                    WHEN '17' THEN 'Pagamento Instantâneo (PIX)'
                    WHEN '19' THEN 'Pagamento QRCODE (PIX)'
                    WHEN '90' THEN 'Sem pagamento'
                    ELSE 'Outros'
                END as tipo_pagamento
            "),

            DB::raw("'pedido' as tipo"),
        ])
        ->leftJoin('clientes', 'clientes.id', '=', 'vendas.cliente_id')
        ->where('vendas.empresa_id', $empresa_id)
        ->where('vendas.estado_emissao', '!=', 'cancelado')
        ->whereBetween('vendas.created_at', [$start.' 00:00:00', $end.' 23:59:59']);

    /*
    |--------------------------------------------------------------------------
    | QUERY PDV
    |--------------------------------------------------------------------------
    */
    $q2 = DB::table('venda_caixas')
        ->select([
            'venda_caixas.id',
            'venda_caixas.created_at',
            DB::raw("COALESCE(clientes.razao_social, 'Consumidor final') as cliente"),
            'venda_caixas.valor_total',
            'venda_caixas.desconto',
            DB::raw("venda_caixas.valor_total as valor_liquido"),

            DB::raw("
                CASE venda_caixas.tipo_pagamento
                    WHEN '01' THEN 'Dinheiro'
                    WHEN '02' THEN 'Cheque'
                    WHEN '03' THEN 'Cartão de Crédito'
                    WHEN '04' THEN 'Cartão de Débito'
                    WHEN '05' THEN 'Crédito Loja'
                    WHEN '06' THEN 'Crediário'
                    WHEN '10' THEN 'Vale Alimentação'
                    WHEN '11' THEN 'Vale Refeição'
                    WHEN '12' THEN 'Vale Presente'
                    WHEN '13' THEN 'Vale Combustível'
                    WHEN '14' THEN 'Duplicata Mercantil'
                    WHEN '15' THEN 'Boleto Bancário'
                    WHEN '16' THEN 'Depósito Bancário'
                    WHEN '17' THEN 'Pagamento Instantâneo (PIX)'
                    WHEN '19' THEN 'Pagamento QRCODE (PIX)'
                    WHEN '90' THEN 'Sem pagamento'
                    ELSE 'Outros'
                END as tipo_pagamento
            "),

            DB::raw("'pdv' as tipo"),
        ])
        ->leftJoin('clientes', 'clientes.id', '=', 'venda_caixas.cliente_id')
        ->where('venda_caixas.empresa_id', $empresa_id)
        ->where('venda_caixas.estado_emissao', '!=', 'cancelado')
        ->where('venda_caixas.rascunho', 0)
        ->where('venda_caixas.consignado', 0)
        ->whereBetween('venda_caixas.created_at', [$start.' 00:00:00', $end.' 23:59:59']);

    /*
    |--------------------------------------------------------------------------
    | FILTROS
    |--------------------------------------------------------------------------
    */
    if ($request->tipo_pagamento) {
        $q1->where('vendas.tipo_pagamento', $request->tipo_pagamento);
        $q2->where('venda_caixas.tipo_pagamento', $request->tipo_pagamento);
    }

    if ($request->vendedor) {
        $func = Funcionario::find($request->vendedor);
        if ($func) {
            $q1->where('vendas.usuario_id', $func->usuario_id);
            $q2->where('venda_caixas.usuario_id', $func->usuario_id);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UNION FINAL
    |--------------------------------------------------------------------------
    */
    $union = $q1->unionAll($q2);

    $vendas = DB::query()
        ->fromSub($union, 'v')
        ->orderBy('created_at', 'desc')
        ->limit(1000) // 🔥 IMPORTANTE (não travar PDF)
        ->get();

    if ($vendas->isEmpty()) {
        return back()->with('flash_erro', 'Sem dados!');
    }

    /*
    |--------------------------------------------------------------------------
    | RESUMO POR PAGAMENTO
    |--------------------------------------------------------------------------
    */
    $resumo = collect($vendas)
        ->groupBy('tipo_pagamento')
        ->map(fn($itens) => $itens->sum('valor_total'));

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */
    $html = view('relatorios.vendas_geral', [
        'vendas' => $vendas,
        'resumo' => $resumo,
        'data_inicial' => $start,
        'data_final' => $end,
        'title' => 'Relatório de Vendas Geral'
    ])->render();

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */
    $pdf = new Dompdf();
    $pdf->loadHtml($html);
    $pdf->setPaper("A4", "landscape");
    $pdf->render();

    return $pdf->stream("Relatorio_Vendas_Geral.pdf", ["Attachment" => false]);
}
public function relatorioVendasGeralView(Request $request)
{
    // 📅 Datas automáticas (mês atual)
    $start = $request->start_date 
        ? $request->start_date 
        : Carbon::now()->startOfMonth()->format('Y-m-d');

    $end = $request->end_date 
        ? $request->end_date 
        : Carbon::now()->endOfMonth()->format('Y-m-d');

    $empresa_id = $request->empresa_id ?? auth()->user()->empresa_id ?? null;

    if (!$empresa_id) {
        return back()->with('flash_erro', 'Empresa não informada!');
    }

    // 🔒 Limite
    if (Carbon::parse($start)->diffInDays($end) > 120) {
        return back()->with('flash_erro', 'Período máximo de 120 dias!');
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY BASE (PEDIDOS)
    |--------------------------------------------------------------------------
    */
    $q1 = DB::table('vendas')
        ->select([
            'vendas.id',
            'vendas.created_at',
            DB::raw("COALESCE(clientes.razao_social, 'Consumidor final') as cliente"),
            'vendas.valor_total',
            'vendas.desconto',
            DB::raw("(vendas.valor_total - vendas.desconto) as valor_liquido"),
            DB::raw("
                CASE vendas.tipo_pagamento
                    WHEN '01' THEN 'Dinheiro'
                    WHEN '02' THEN 'Cheque'
                    WHEN '03' THEN 'Cartão de Crédito'
                    WHEN '04' THEN 'Cartão de Débito'
                    WHEN '05' THEN 'Crédito Loja'
                    WHEN '06' THEN 'Crediário'
                    WHEN '10' THEN 'Vale Alimentação'
                    WHEN '11' THEN 'Vale Refeição'
                    WHEN '12' THEN 'Vale Presente'
                    WHEN '13' THEN 'Vale Combustível'
                    WHEN '14' THEN 'Duplicata Mercantil'
                    WHEN '15' THEN 'Boleto Bancário'
                    WHEN '16' THEN 'Depósito Bancário'
                    WHEN '17' THEN 'Pagamento Instantâneo (PIX)'
                    WHEN '19' THEN 'Pagamento QRCODE (PIX)'
                    WHEN '90' THEN 'Sem pagamento'
                    ELSE 'Outros'
                END as tipo_pagamento
            "),
            DB::raw("'pedido' as tipo"),
        ])
        ->leftJoin('clientes', 'clientes.id', '=', 'vendas.cliente_id')
        ->where('vendas.empresa_id', $empresa_id)
        ->where('vendas.estado_emissao', '!=', 'cancelado')
        ->whereBetween('vendas.created_at', [$start.' 00:00:00', $end.' 23:59:59']);

    /*
    |--------------------------------------------------------------------------
    | QUERY BASE (PDV)
    |--------------------------------------------------------------------------
    */
    $q2 = DB::table('venda_caixas')
        ->select([
            'venda_caixas.id',
            'venda_caixas.created_at',
            DB::raw("COALESCE(clientes.razao_social, 'Consumidor final') as cliente"),
            'venda_caixas.valor_total',
            'venda_caixas.desconto',
            DB::raw("venda_caixas.valor_total as valor_liquido"),
            DB::raw("
                CASE venda_caixas.tipo_pagamento
                    WHEN '01' THEN 'Dinheiro'
                    WHEN '02' THEN 'Cheque'
                    WHEN '03' THEN 'Cartão de Crédito'
                    WHEN '04' THEN 'Cartão de Débito'
                    WHEN '05' THEN 'Crédito Loja'
                    WHEN '06' THEN 'Crediário'
                    WHEN '10' THEN 'Vale Alimentação'
                    WHEN '11' THEN 'Vale Refeição'
                    WHEN '12' THEN 'Vale Presente'
                    WHEN '13' THEN 'Vale Combustível'
                    WHEN '14' THEN 'Duplicata Mercantil'
                    WHEN '15' THEN 'Boleto Bancário'
                    WHEN '16' THEN 'Depósito Bancário'
                    WHEN '17' THEN 'Pagamento Instantâneo (PIX)'
                    WHEN '19' THEN 'Pagamento QRCODE (PIX)'
                    WHEN '90' THEN 'Sem pagamento'
                    ELSE 'Outros'
                END as tipo_pagamento
            "),
            DB::raw("'pdv' as tipo"),
        ])
        ->leftJoin('clientes', 'clientes.id', '=', 'venda_caixas.cliente_id')
        ->where('venda_caixas.empresa_id', $empresa_id)
        ->where('venda_caixas.estado_emissao', '!=', 'cancelado')
        ->where('venda_caixas.rascunho', 0)
        ->where('venda_caixas.consignado', 0)
        ->whereBetween('venda_caixas.created_at', [$start.' 00:00:00', $end.' 23:59:59']);

    /*
    |--------------------------------------------------------------------------
    | FILTROS
    |--------------------------------------------------------------------------
    */
    if ($request->tipo_pagamento) {
        $q1->where('vendas.tipo_pagamento', $request->tipo_pagamento);
        $q2->where('venda_caixas.tipo_pagamento', $request->tipo_pagamento);
    }

    if ($request->vendedor) {
        $func = \App\Models\Funcionario::find($request->vendedor);
        if ($func) {
            $q1->where('vendas.usuario_id', $func->usuario_id);
            $q2->where('venda_caixas.usuario_id', $func->usuario_id);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UNION PAGINADO (LISTA)
    |--------------------------------------------------------------------------
    */
    // Guarde consultas independentes para o resumo antes de montar a union
    // paginada. unionAll() altera o builder original e fazia o PDV entrar duas
    // vezes no total do resumo.
    $resumoQ1 = clone $q1;
    $resumoQ2 = clone $q2;
    $union = $q1->unionAll($q2);

    $vendas = DB::query()
        ->fromSub($union, 'v')
        ->orderBy('created_at', 'desc')
        ->paginate(50);

    /*
    |--------------------------------------------------------------------------
    | 🔥 RESUMO REAL (SEM PAGINAÇÃO)
    |--------------------------------------------------------------------------
    */
    $resumoUnion = $resumoQ1->unionAll($resumoQ2);

    $resumo = DB::query()
        ->fromSub($resumoUnion, 'v')
        ->select('tipo_pagamento', DB::raw('SUM(valor_total) as total'))
        ->groupBy('tipo_pagamento')
        ->get();

    $totalGeral = $resumo->sum('total');

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */
    return view('relatorios.vendas_geral_page', [
        'vendas' => $vendas,
        'resumo' => $resumo,
        'totalGeral' => $totalGeral,
        'data_inicial' => $start,
        'data_final' => $end,
        'title' => 'Relatório de Vendas Geral'
    ]);
}
	public function filtroVendas2(Request $request)
	{
		$start_date = $request->start_date;
		$end_date = $request->end_date;
		$cliente_id = $request->cliente_id;
		$vendedor = $request->vendedor;
		$vendas = Venda::when(!empty($start_date), function ($query) use ($start_date) {
			return $query->whereDate('created_at', '>=', $start_date);
		})
		->when(!empty($end_date), function ($query) use ($end_date) {
			return $query->whereDate('created_at', '<=', $end_date);
		})
		->where('vendas.empresa_id', $request->empresa_id)
		->when($cliente_id, function ($q) use ($cliente_id) {
			return $q->where('vendas.cliente_id', $cliente_id);
		})
		->where('vendas.estado_emissao', '!=', 'cancelado')
		->limit(1000000);
		if ($request->tipo_pagamento) {
			$vendas->where('tipo_pagamento', $request->tipo_pagamento);
		}
		if ($vendedor) {
			$funcionario = Funcionario::findOrFail($vendedor);
			$vendas->where('usuario_id', $funcionario->usuario_id);
		}
		$vendas = $vendas->get();
		$vendasCaixa = VendaCaixa::when(!empty($start_date), function ($query) use ($start_date) {
			return $query->whereDate('created_at', '>=', $start_date);
		})
		->when(!empty($end_date), function ($query) use ($end_date) {
			return $query->whereDate('created_at', '<=', $end_date);
		})
		->where('venda_caixas.empresa_id', $request->empresa_id)
		->where('venda_caixas.estado_emissao', '!=', 'cancelado')
		->where('venda_caixas.rascunho', 0)
		->where('venda_caixas.consignado', 0)
		->when($cliente_id, function ($q) use ($cliente_id) {
			return $q->where('venda_caixas.cliente_id', $cliente_id);
		})
		->limit(1000000);
		if ($request->tipo_pagamento) {
			$vendasCaixa->where('tipo_pagamento', $request->tipo_pagamento);
		}
		if ($vendedor) {
			$funcionario = Funcionario::findOrFail($vendedor);
			$vendasCaixa->where('usuario_id', $funcionario->usuario_id);
		}
		$vendasCaixa = $vendasCaixa->get();
		$arr = $this->uneArrayVendas2($vendas, $vendasCaixa);
		usort($arr, function ($a, $b) {
			return $a['created_at'] > $b['created_at'] ? 1 : 0;
		});
		if (sizeof($arr) == 0) {
			session()->flash("flash_erro", "Relatório sem registro!");
			return redirect('/relatorios');
		}
		$p = view('relatorios/relatorio_venda2')
		->with('data_inicial', $request->data_inicial)
		->with('data_final', $request->data_final)
		->with('title', 'Relatório de vendas')
		->with('vendas', $arr);
		// return $p;
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4", "landscape");
		$domPdf->render();
		$domPdf->stream("Relatório de vendas.pdf", array("Attachment" => false));
	}

	private function uneArrayVendas2($vendas, $vendasCaixa)
	{
		$arr = [];
		foreach ($vendas as $v) {
			$v->tbl = 'pedido';
			array_push($arr, $v);
		}
		foreach ($vendasCaixa as $v) {
			$v->tbl = 'pdv';
			array_push($arr, $v);
		}
		return $arr;
	}

	public function filtroLucro(Request $request)
	{
		$start_date = $request->start_date;
		$end_date = $request->end_date;
		$tipo = $request->tipo;
		if ($tipo == 'detalhado') {
			if (!$start_date) {
				session()->flash("flash_erro", "Informe a data para gerar o relatório!");
				return redirect('/relatorios');
			}
			$vendas = Venda::when(!empty($start_date), function ($query) use ($start_date) {
				return $query->whereDate('created_at', '>=', $start_date);
			})
			->when(!empty($end_date), function ($query) use ($end_date) {
				return $query->whereDate('created_at', '<=', $end_date);
			})
			->where('vendas.empresa_id', $request->empresa_id)
			->where('vendas.estado_emissao', '!=', 'cancelado')
			->limit(1000000)
			->get();
			$vendasCaixa = VendaCaixa::when(!empty($start_date), function ($query) use ($start_date) {
				return $query->whereDate('created_at', '>=', $start_date);
			})
			->when(!empty($end_date), function ($query) use ($end_date) {
				return $query->whereDate('created_at', '<=', $end_date);
			})
			->where('venda_caixas.empresa_id', $request->empresa_id)
			->where('venda_caixas.estado_emissao', '!=', 'cancelado')
			->where('venda_caixas.rascunho', 0)
			->where('venda_caixas.consignado', 0)
			->limit(1000000)
			->get();
			$arr = [];
			foreach ($vendas as $v) {
				$total = $v->valor_total;
				$somaValorCompra = 0;
				foreach ($v->itens as $i) {
					//pega valor de compra
					$vCompra = 0;
					$vCompra = $i->produto->valor_compra;
					if (!$vCompra == 0) {
						$estoque = Estoque::ultimoValorCompra($i->produto_id);
						if ($estoque != null) {
							$vCompra = $estoque->valor_compra;
						}
					}
					$somaValorCompra = $i->quantidade * $vCompra;
				}
				$lucro = $total - $somaValorCompra;
				if ($somaValorCompra == 0) {
					$somaValorCompra = 1;
				}
				$temp = [
					'valor_venda' => $total,
					'valor_compra' => $somaValorCompra,
					'lucro' => $lucro,
					'lucro_percentual' =>
					number_format((($somaValorCompra - $total) / $somaValorCompra * 100) * -1, 2),
					'local' => 'NF-e',
					'cliente' => $v->cliente->razao_social,
					'horario' => \Carbon\Carbon::parse($v->created_at)->format('H:i')
				];
				array_push($arr, $temp);
			}

			foreach ($vendasCaixa as $v) {
				$total = $v->valor_total;
				$somaValorCompra = 0;
				foreach ($v->itens as $i) {
					//pega valor de compra
					$vCompra = 0;
					$vCompra = $i->produto->valor_compra;
					if ($vCompra == 0) {
						$estoque = Estoque::ultimoValorCompra($i->produto_id);
						if ($estoque != null) {
							$vCompra = $estoque->valor_compra;
						}
					}
					$somaValorCompra += $i->quantidade * $vCompra;
				}
				// echo "VendaID $v->id | Total: ". $total . " | soma itens: " . $somaValorCompra . "<br>";
				$lucro = $total - $somaValorCompra;
				if ($somaValorCompra == 0) {
					$somaValorCompra = 1;
				}
				$temp = [
					'valor_venda' => $total,
					'valor_compra' => $somaValorCompra,
					'lucro' => $lucro,
					'lucro_percentual' =>
					number_format((($somaValorCompra - $total) / $somaValorCompra * 100) * -1, 2),
					'local' => 'PDV',
					'cliente' => $v->cliente ? $v->cliente->razao_social : 'Cliente padrão',
					'horario' => \Carbon\Carbon::parse($v->created_at)->format('H:i')
				];
				array_push($arr, $temp);
			}
			if (sizeof($arr) == 0) {
				session()->flash("flash_erro", "Relatório sem registro!");
				return redirect('/relatorios');
			}
			$p = view('relatorios.lucro_detalhado')
			->with('data_inicial', $request->start_date)
			->with('title', 'Relatório de lucro')
			->with('lucros', $arr);
			// return $p;
			$domPdf = new Dompdf(["enable_remote" => true]);
			$domPdf->loadHtml($p);
			$pdf = ob_get_clean();
			$domPdf->setPaper("A4");
			$domPdf->render();
			$domPdf->stream("Relatório de lucro detalhado.pdf",  array("Attachment" => false));
		} else {
			$vendas = Venda::when(!empty($start_date), function ($query) use ($start_date) {
				return $query->whereDate('created_at', '>=', $start_date);
			})
			->when(!empty($end_date), function ($query) use ($end_date) {
				return $query->whereDate('created_at', '<=', $end_date);
			})
			->where('empresa_id', $request->empresa_id)
			->where('estado_emissao', '!=', 'cancelado')
			->get();
			$vendasCaixa = VendaCaixa::when(!empty($start_date), function ($query) use ($start_date) {
				return $query->whereDate('created_at', '>=', $start_date);
			})
			->when(!empty($end_date), function ($query) use ($end_date) {
				return $query->whereDate('created_at', '<=', $end_date);
			})
			->where('empresa_id', $request->empresa_id)
			->where('estado_emissao', '!=', 'cancelado')
			->get();
			$tempVenda = [];
			foreach ($vendas as $v) {
				$total = $v->valor_total;
				$somaValorCompra = 0;
				foreach ($v->itens as $i) {
					//pega valor de compra
					$vCompra = 0;
					$vCompra = $i->produto->valor_compra;
					if ($vCompra == 0) {
						$estoque = Estoque::ultimoValorCompra($i->produto_id);
						if ($estoque != null) {
							$vCompra = $estoque->valor_compra;
						}
					}
					$somaValorCompra += $i->quantidade * $vCompra;
				}
				$lucro = $total - $somaValorCompra;
				if (!isset($tempVenda[\Carbon\Carbon::parse($v->created_at)->format('d/m/Y')])) {
					$tempVenda[\Carbon\Carbon::parse($v->created_at)->format('d/m/Y')] = $lucro;
				} else {
					$tempVenda[\Carbon\Carbon::parse($v->created_at)->format('d/m/Y')] += $lucro;
				}
			}
			$tempCaixa = [];
			foreach ($vendasCaixa as $v) {
				$total = $v->valor_total;
				$somaValorCompra = 0;
				foreach ($v->itens as $i) {
					//pega valor de compra
					$vCompra = 0;
					$vCompra = $i->produto->valor_compra;
					if ($vCompra == 0) {
						$estoque = Estoque::ultimoValorCompra($i->produto_id);
						if ($estoque != null) {
							$vCompra = $estoque->valor_compra;
						}
					}
					$somaValorCompra += $i->quantidade * $vCompra;
				}
				$lucro = $total - $somaValorCompra;
				if (!isset($tempCaixa[\Carbon\Carbon::parse($v->created_at)->format('d/m/Y')])) {
					$tempCaixa[\Carbon\Carbon::parse($v->created_at)->format('d/m/Y')] = $lucro;
				} else {
					$tempCaixa[\Carbon\Carbon::parse($v->created_at)->format('d/m/Y')] += $lucro;
				}
			}
			// print_r($tempVenda);
			// print_r($tempCaixa);
			$arr = $this->criarArrayDeDatas($start_date, $end_date, $tempVenda, $tempCaixa);
			$p = view('relatorios.lucro')
			->with('data_inicial', $request->start_date)
			->with('data_final', $request->end_date)
			->with('title', 'Relatório de lucro sintético')
			->with('lucros', $arr);
			// return $p;
			$domPdf = new Dompdf(["enable_remote" => true]);
			$domPdf->loadHtml($p);
			$pdf = ob_get_clean();
			$domPdf->setPaper("A4");
			$domPdf->render();
			$domPdf->stream("Relatório de lucro.pdf",  array("Attachment" => false));
		}
	}

	private function criarArrayDeDatas($inicio, $fim, $tempVenda, $tempCaixa)
	{
		$diferenca = strtotime($fim) - strtotime($inicio);
		$dias = floor($diferenca / (60 * 60 * 24));
		$global = [];
		$dataAtual = $inicio;
		for ($aux = 0; $aux < $dias + 1; $aux++) {
			// echo \Carbon\Carbon::parse($dataAtual)->format('d/m/Y');
			$rs['data'] = $this->parseViewData($dataAtual);
			if (isset($tempCaixa[\Carbon\Carbon::parse($dataAtual)->format('d/m/Y')])) {
				$rs['valor_caixa'] = $tempCaixa[\Carbon\Carbon::parse($dataAtual)->format('d/m/Y')];
			} else {
				$rs['valor_caixa'] = 0;
			}
			if (isset($tempVenda[\Carbon\Carbon::parse($dataAtual)->format('d/m/Y')])) {
				$rs['valor'] = $tempVenda[\Carbon\Carbon::parse($dataAtual)->format('d/m/Y')];
			} else {
				$rs['valor'] = 0;
			}
			array_push($global, $rs);
			$dataAtual = date('Y-m-d', strtotime($dataAtual . '+1day'));
		}
		return $global;
	}


	private function parseViewData($date)
	{
		return date('d/m/Y', strtotime(str_replace("/", "-", $date)));
	}


	public function listaPreco(Request $request)
	{
		$lista = ListaPreco::find($request->lista);
		if (!$lista) {
			session()->flash("flash_erro", "Relatório sem registro!");
			return redirect('/relatorios');
		}
		$d1 = str_replace("/", "-", $request->start_date);
		$p = view('relatorios.lista_preco')
		->with('lista', $lista)
		->with('title', 'Relatório de lista de preço')
		->with('data', $d1);
		// return $p;
		// if($request->excel == 0){
		$domPdf = new Dompdf();
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatório de lista de preço.pdf", array("Attachment" => false));
		// }else{
		// 	$relatorioEx = new RelatorioListaPrecoExport($lista->itens);
		// 	return Excel::download($relatorioEx, 'relatorio lista de preço.xlsx');
		// }
	}

	public function filtroEstoqueMinimo(Request $request)
	{
		$total_resultados = $request->n_resultados;
		$produtos = Produto::where('empresa_id', $request->empresa_id)
		->when(!empty($total_resultados), function ($query) use ($total_resultados) {
			return $query->limit($total_resultados);
		})
		->get();
		$arrDesfalque = [];
		foreach ($produtos as $p) {
			if ($p->estoque_minimo > 0) {
				$estoque = Estoque::where('produto_id', $p->id)->first();
				$temp = null;
				if ($estoque == null) {
					$temp = [
						'id' => $p->id,
						'nome' => $p->nome . ($p->grade ? " $p->str_grade" : ""),
						'estoque_minimo' => $p->estoque_minimo,
						'estoque_atual' => 0,
						'total_comprar' => $p->estoque_minimo,
						'valor_compra' => 0
					];
				} else {
					$temp = [
						'id' => $p->id,
						'nome' => $p->nome . ($p->grade ? " $p->str_grade" : ""),
						'estoque_minimo' => $p->estoque_minimo,
						'estoque_atual' => $estoque->quantidade,
						'total_comprar' => $p->estoque_minimo - $estoque->quantidade,
						'valor_compra' => $estoque->valor_compra
					];
				}
				array_push($arrDesfalque, $temp);
			}
		}
		if ($total_resultados) {
			$arrDesfalque = array_slice($arrDesfalque, 0, $total_resultados);
		}
		// print_r($arrDesfalque);
		$p = view('relatorios.relatorio_estoque_minimo')
		->with('title', 'Relatório de Estoque Mínimo')
		->with('itens', $arrDesfalque);
		// return $p;
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatório de estoque minimo.pdf", array("Attachment" => false));
	}

	public function filtroVendaProdutos(Request $request)
	{
		$start_date = $request->start_date;
		$end_date = $request->end_date;
		$total_resultados = $request->n_resultados;
		$ordem = $request->ordem;
		$categoria_id = $request->categoria;
		$marca_id = $request->marca;
		$sub_categoria_id = $request->sub_categoria;
		$itensVenda = ItemVenda::select(\DB::raw('produtos.id as id, produtos.nome as nome, produtos.grade as grade, produtos.str_grade as str_grade, produtos.valor_venda, produtos.valor_compra, sum(item_vendas.quantidade) as total, sum(item_vendas.quantidade * item_vendas.valor) as total_dinheiro, produtos.unidade_venda'))
		->join('produtos', 'produtos.id', '=', 'item_vendas.produto_id')
		->when(!empty($start_date), function ($query) use ($start_date) {
			return $query->whereDate('item_vendas.created_at', '>=', $start_date);
		})
		->when(!empty($end_date), function ($query) use ($end_date) {
			return $query->whereDate('item_vendas.created_at', '<=', $end_date);
		})
		->where('produtos.empresa_id', $request->empresa_id)
		->groupBy('produtos.id')
		->orderBy($ordem == 'data' ? 'data' : 'total', $ordem == 'data' ? 'desc' : $ordem)
		->limit($total_resultados ?? 1000000)

		->when(!empty($categoria_id), function ($query) use ($categoria_id) {
			return $query->where('produtos.categoria_id', $categoria_id);
		})
		->when(!empty($marca_id), function ($query) use ($marca_id) {
			return $query->where('produtos.marca_id', $marca_id);
		})
		->when(!empty($sub_categoria_id), function ($query) use ($sub_categoria_id) {
			return $query->where('produtos.sub_categoria_id', $sub_categoria_id);
		})
		->get();
		$itensVendaCaixa = ItemVendaCaixa::select(\DB::raw('produtos.id as id, produtos.nome as nome, produtos.grade as grade, produtos.str_grade as str_grade, produtos.valor_venda, produtos.valor_compra, sum(item_venda_caixas.quantidade) as total, sum(item_venda_caixas.quantidade * item_venda_caixas.valor) as total_dinheiro, produtos.unidade_venda'))
		->join('produtos', 'produtos.id', '=', 'item_venda_caixas.produto_id')
		->when(!empty($start_date), function ($query) use ($start_date) {
			return $query->whereDate('item_venda_caixas.created_at', '>=', $start_date);
		})
		->when(!empty($end_date), function ($query) use ($end_date) {
			return $query->whereDate('item_venda_caixas.created_at', '<=', $end_date);
		})
		->where('empresa_id', $request->empresa_id)
		->groupBy('produtos.id')
		->orderBy($ordem == 'data' ? 'data' : 'total', $ordem == 'data' ? 'desc' : $ordem)
		->limit($total_resultados ?? 1000000)
		->when(!empty($categoria_id), function ($query) use ($categoria_id) {
			return $query->where('produtos.categoria_id', $categoria_id);
		})
		->when(!empty($marca_id), function ($query) use ($marca_id) {
			return $query->where('produtos.marca_id', $marca_id);
		})
		->when(!empty($sub_categoria_id), function ($query) use ($sub_categoria_id) {
			return $query->where('produtos.sub_categoria_id', $sub_categoria_id);
		})
		->get();
		$arr = $this->uneArrayProdutos($itensVenda, $itensVendaCaixa);
		usort($arr, function ($a, $b) use ($ordem) {
			if ($ordem == 'alfa') {
				return $a['nome'] < $b['nome'] ? 1 : -1;
			} else {
				if ($ordem == 'asc') return $a['total'] > $b['total'] ? 1 : 0;
				else return $a['total'] < $b['total'] ? 1 : 0;
			}
		});
		if (sizeof($arr) == 0) {
			session()->flash("flash_erro", "Relatório sem registro!");
			return redirect('/relatorios');
		}
		if ($total_resultados) {
			$arr = array_slice($arr, 0, $total_resultados);
		}
		$p = view('relatorios/relatorio_venda_produtos')
		->with('ordem', $ordem == 'asc' ? 'Menos' : 'Mais')
		->with('data_inicial', $request->start_date)
		->with('data_final', $request->end_date)
		->with('title', 'Relatório de produtos')
		->with('itens', $arr);
		// return $p;
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4", "landscape");
		$domPdf->render();
		$domPdf->stream("Relatório de produtos.pdf", array("Attachment" => false));
	}

	private function uneArrayProdutos($itemVenda, $itemVendasCaixa)
	{
		$adicionados = [];
		$arr = [];
		foreach ($itemVenda as $i) {
			$temp = [
				'id' => $i->id,
				'nome' => $i->nome,
				'valor_venda' => $i->valor_venda,
				'valor_compra' => $i->valor_compra,
				'total' => $i->total,
				'total_dinheiro' => $i->total_dinheiro,
				'grade' => $i->grade,
				'unidade' => $i->unidade_venda,
				'str_grade' => $i->str_grade,
			];
			array_push($adicionados, $i->id);
			array_push($arr, $temp);
		}
		foreach ($itemVendasCaixa as $i) {
			if (!in_array($i->id, $adicionados)) {
				$temp = [
					'id' => $i->id,
					'nome' => $i->nome,
					'valor_venda' => $i->valor_venda,
					'valor_compra' => $i->valor_compra,
					'total' => $i->total,
					'total_dinheiro' => $i->total_dinheiro,
					'grade' => $i->grade,
					'unidade' => $i->unidade_venda,
					'str_grade' => $i->str_grade,
				];
				array_push($adicionados, $i->id);
				array_push($arr, $temp);
			} else {
				for ($aux = 0; $aux < count($arr); $aux++) {
					if ($arr[$aux]['id'] == $i->id) {
						$arr[$aux]['total'] += $i->total;
						$arr[$aux]['total_dinheiro'] += $i->total;
					}
				}
			}
		}
		return $arr;
	}

	public function cadastroProdutos(Request $request)
	{
		$start_date = $request->start_date;
		$end_date = $request->end_date;
		$produtos = Produto::when(!empty($start_date), function ($query) use ($start_date) {
			return $query->whereDate('created_at', '>=', $start_date);
		})
		->when(!empty($end_date), function ($query) use ($end_date) {
			return $query->whereDate('created_at', '<=', $end_date);
		})
		->where('empresa_id', $request->empresa_id)
		->orderBy('created_at', 'asc')
		->get();
		$p = view('relatorios/cadastro_produtos')
		->with('produtos', $produtos)
		->with('data_inicial', $request->start_date)
		->with('title', 'Relatório cadastro de produtos')
		->with('data_final', $request->end_date);
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatório cadastro de produtos.pdf", array("Attachment" => false));
	}
    
    public function fiscal(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $cliente_id = $request->cliente_id;
        $estado = $request->estado;
        $tipo = $request->tipo;
        $cfop = $request->cfop;
        $natureza_id = $request->natureza_id;
        $empresa_id = $request->empresa_id;
    
        $data = [];
    
        /*
        |--------------------------------------------------------------------------
        | NFE - Refatorado com whereHas para evitar duplicidade por CFOP
        |--------------------------------------------------------------------------
        */
        if ($tipo == 'todos' || $tipo == 'nfe') {
            $nfes = Venda::with(['cliente'])
                ->when($start_date, fn($q) => $q->whereDate('data_emissao', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('data_emissao', '<=', $end_date))
                ->where('numero_nfe', '>', 0)
                ->where('empresa_id', $empresa_id)
                ->when($cliente_id, fn($q) => $q->where('cliente_id', $cliente_id))
                ->when($natureza_id, fn($q) => $q->where('natureza_id', $natureza_id))
                ->when($estado != 'todos', function ($q) use ($estado) {
                    return $q->where('estado_emissao', $estado == 'aprovados' ? 'aprovado' : 'cancelado');
                })
                ->when($cfop, function ($q) use ($cfop) {
                    return $q->whereHas('itens', fn($sub) => $sub->where('cfop', $cfop));
                })
                ->get();
    
            foreach ($nfes as $n) {
                $data[] = [
                    'valor_total' => ($n->valor_total - ($n->desconto ?? 0)) + ($n->acrescimo ?? 0),
                    'data' => \Carbon\Carbon::parse($n->data_emissao)->format('d/m/y H:i'),
                    'cliente' => $n->cliente->razao_social ?? 'Não identificado',
                    'chave' => $n->chave,
                    'estado' => $n->estado_emissao,
                    'numero' => $n->numero_nfe,
                    'tipo_pagamento' => $n->tipo_pagamento ?? '--',
                    'cfop' => $cfop ?? '',
                    'tipo' => 'nfe',
                ];
            }
    
            // Remessas
            $remessas = RemessaNfe::with(['cliente'])
                ->when($start_date, fn($q) => $q->whereDate('data_emissao', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('data_emissao', '<=', $end_date))
                ->where('numero_nfe', '>', 0)
                ->where('empresa_id', $empresa_id)
                ->when($cliente_id, fn($q) => $q->where('cliente_id', $cliente_id))
                ->when($natureza_id, fn($q) => $q->where('natureza_id', $natureza_id))
                ->when($estado != 'todos', fn($q) => $q->where('estado_emissao', $estado == 'aprovados' ? 'aprovado' : 'cancelado'))
                ->when($cfop, function ($q) use ($cfop) {
                    return $q->whereHas('itens', fn($sub) => $sub->where('cfop', $cfop));
                })
                ->get();
    
            foreach ($remessas as $n) {
                $data[] = [
                    'valor_total' => $n->valor_total,
                    'data' => \Carbon\Carbon::parse($n->data_emissao)->format('d/m/y H:i'),
                    'cliente' => $n->cliente->razao_social ?? '',
                    'chave' => $n->chave,
                    'estado' => $n->estado_emissao,
                    'numero' => $n->numero_nfe,
                    'tipo_pagamento' => '--',
                    'cfop' => $cfop ?? '',
                    'tipo' => 'remessa',
                ];
            }
    
            // Devoluções
            $devolucoes = Devolucao::with(['fornecedor'])
                ->when($start_date, fn($q) => $q->whereDate('created_at', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('created_at', '<=', $end_date))
                ->where('numero_gerado', '>', 0)
                ->where('empresa_id', $empresa_id)
                ->when($natureza_id, fn($q) => $q->where('natureza_id', $natureza_id))
                ->when($estado != 'todos', fn($q) => $q->where('estado_emissao', $estado == 'aprovados' ? 'aprovado' : 'cancelado'))
                ->when($cfop, function ($q) use ($cfop) {
                    return $q->whereHas('itens', fn($sub) => $sub->where('cfop', $cfop));
                })
                ->get();
    
            foreach ($devolucoes as $n) {
                $data[] = [
                    'valor_total' => $n->valor_devolvido,
                    'data' => $n->created_at->format('d/m/y H:i'),
                    'cliente' => $n->fornecedor->razao_social ?? '',
                    'chave' => $n->chave_gerada,
                    'estado' => $n->estado_emissao,
                    'numero' => $n->numero_gerado,
                    'tipo_pagamento' => '--',
                    'cfop' => $cfop ?? '',
                    'tipo' => 'devolucao',
                ];
            }
        }
    
        /*
        |--------------------------------------------------------------------------
        | NFCE
        |--------------------------------------------------------------------------
        */
        if ($tipo == 'todos' || $tipo == 'nfce') {
            $nfces = VendaCaixa::with(['cliente'])
                ->when($start_date, fn($q) => $q->whereDate('created_at', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('created_at', '<=', $end_date))
                ->where('empresa_id', $empresa_id)
                ->when($cliente_id, fn($q) => $q->where('cliente_id', $cliente_id))
    						->when($natureza_id, fn($q) => $q->where('natureza_id', $natureza_id))
                ->when($estado != 'todos', fn($q) => $q->where('estado_emissao', $estado == 'aprovados' ? 'aprovado' : 'cancelado'))
                ->when($cfop, function ($q) use ($cfop) {
                    return $q->whereHas('itens', fn($sub) => $sub->where('cfop', $cfop));
                })
                ->get();
    
            foreach ($nfces as $n) {
                $data[] = [
                    'valor_total' => $n->valor_total,
                    'data' => $n->created_at->format('d/m/y H:i'),
                    'cliente' => $n->cliente->razao_social ?? 'Consumidor Final',
                    'chave' => $n->chave,
                    'estado' => $n->estado_emissao,
                    'numero' => $n->numero_nfce,
                    'tipo_pagamento' => $n->tipo_pagamento ?? '--',
                    'cfop' => $cfop ?? '',
                    'tipo' => 'nfce',
                ];
            }
        }
    
        /*
        |--------------------------------------------------------------------------
        | CTE
        |--------------------------------------------------------------------------
        */
        if ($tipo == 'todos' || $tipo == 'cte') {
            $ctes = Cte::with(['destinatario'])
                ->when($start_date, fn($q) => $q->whereDate('created_at', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('created_at', '<=', $end_date))
                ->where('empresa_id', $empresa_id)
                ->when($cliente_id, fn($q) => $q->where('destinatario_id', $cliente_id))
                ->when($natureza_id, fn($q) => $q->where('natureza_id', $natureza_id))
                ->when($estado != 'todos', fn($q) => $q->where('estado_emissao', $estado == 'aprovados' ? 'aprovado' : 'cancelado'))
                ->get();
    
            foreach ($ctes as $n) {
                $data[] = [
                    'valor_total' => $n->valor_receber,
                    'data' => $n->created_at->format('d/m/y H:i'),
                    'cliente' => $n->destinatario->razao_social ?? '',
                    'chave' => $n->chave,
                    'estado' => $n->estado_emissao,
                    'numero' => $n->cte_numero,
                    'tipo_pagamento' => '--',
                    'cfop' => '',
                    'tipo' => 'cte',
                ];
            }
        }
    
        /*
        |--------------------------------------------------------------------------
        | MDFE
        |--------------------------------------------------------------------------
        */
        if ($tipo == 'todos' || $tipo == 'mdfe') {
            $mdfes = Mdfe::when($start_date && $end_date, fn($q) => $q->whereBetween('created_at', [$start_date, $end_date]))
                ->where('empresa_id', $empresa_id)
                ->when($estado != 'todos', fn($q) => $q->where('estado_emissao', $estado == 'aprovados' ? 'aprovado' : 'cancelado'))
                ->get();
    
            foreach ($mdfes as $n) {
                $data[] = [
                    'valor_total' => $n->valor_carga,
                    'data' => $n->created_at->format('d/m/y H:i'),
                    'cliente' => '--',
                    'chave' => $n->chave,
                    'estado' => $n->estado_emissao,
                    'numero' => $n->mdfe_numero,
                    'tipo_pagamento' => '--',
                    'cfop' => '',
                    'tipo' => 'mdfe',
                ];
            }
        }
    
        if (empty($data)) {
            return redirect('/relatorios')->with('flash_erro', 'Relatório sem registro!');
        }
    
        // Ordenar por data (opcional, mas recomendado para relatórios)
        usort($data, function ($a, $b) {
            return strtotime($a['data']) - strtotime($b['data']);
        });
    
    		$natureza_id = $request->natureza_id;
    		$natureza_nome = null;
    
    		if($natureza_id){
    				// Busca o nome da natureza para exibir no cabeçalho do PDF
    				$nat = NaturezaOperacao::find($natureza_id); // Ajuste o nome do model se for diferente
    				$natureza_nome = $nat ? $nat->natureza : null;
    		}
    	
        $view = view('relatorios.relatorio_fiscal', [
            'data' => $data,
            'd1' => $start_date,
            'd2' => $end_date,
            'estado' => $estado,
            'tipo' => $tipo,
    				'natureza_nome' => $natureza_nome,
            'title' => 'Relatório Fiscal',
        ])->render();
    
        $pdf = new \Dompdf\Dompdf();
        $pdf->loadHtml($view);
        $pdf->setPaper("A4", "landscape");
        $pdf->render();
        
        return $pdf->stream("Relatorio_fiscal.pdf", ["Attachment" => false]);
    }

	public function boletos(Request $request)
	{
		$data_inicial = $request->star_date;
		$data_final = $request->end_date;
		$status = $request->status;
		$contas = ContaReceber::orderBy('data_vencimento', 'desc')
		->join('boletos', 'boletos.conta_id', '=', 'conta_recebers.id')
		->select('conta_recebers.*');
		if ($data_final && $data_final) {
			$data_inicial = $request->star_date;
			$data_final = $request->end_date;
			$contas->whereBetween(
				'conta_recebers.data_vencimento',
				[$data_inicial, $data_final]
			);
		}
		if ($status != "") {
			$contas->where('conta_recebers.status', $status == "recebido" ? 1 : 0);
		}
		$contas = $contas->get();
		$p = view('relatorios.relatorio_boletos')
		->with('contas', $contas)
		->with('data_inicial', $data_inicial)
		->with('data_final', $data_final)
		->with('status', $status)
		->with('title', 'Relatório de Boletos');
		// return $p;
		$domPdf = new Dompdf();
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatório Boletos.pdf", array("Attachment" => false));
	}

	public function filtroVendaClientes(Request $request)
	{
		$data_inicial = $request->start_date;
		$data_final = $request->end_date;
		$total_resultados = $request->total_resultados;
		$ordem = $request->ordem;
		$cliente_id = $request->cliente;
		if ($data_final && $data_final) {
			$data_inicial = $request->start_date;
			$data_final = $request->end_date;
		}

		if ($cliente_id == null) {
			session()->flash('flash_erro', 'Selecione um cliente para continuar');
			return redirect()->back();
		}

		$vendas = Venda::select(\DB::raw('clientes.id as id, clientes.razao_social as nome, count(*) as total, sum(valor_total-desconto+acrescimo) as total_dinheiro'))
		->when(!empty($data_inicial), function ($query) use ($data_inicial) {
			return $query->whereDate('vendas.created_at', '>=', $data_inicial);
		})
		->when(!empty($end_date), function ($query) use ($data_final) {
			return $query->whereDate('vendas.created_at', '<=', $data_final);
		})
		->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
		->orWhere(function ($q) use ($data_inicial, $data_final) {
			if ($data_final && $data_final) {
				return $q->whereBetween('vendas.data_registro', [
					$data_inicial,
					$data_final
				]);
			}
		})
		->where('vendas.empresa_id', $request->empresa_id)
		->where('vendas.cliente_id', $cliente_id)
		->groupBy('clientes.id')
		->orderBy($ordem == 'data' ? 'data' : 'total', $ordem == 'data' ? 'desc' : $ordem)
		->limit($total_resultados ?? 1000000)
		->get();
		$vendaCaixa = VendaCaixa::select(\DB::raw('clientes.id as id, clientes.razao_social as nome, count(*) as total, sum(valor_total) as total_dinheiro'))
		->when(!empty($data_inicial), function ($query) use ($data_inicial) {
			return $query->whereDate('venda_caixas.created_at', '>=', $data_inicial);
		})
		->when(!empty($end_date), function ($query) use ($data_final) {
			return $query->whereDate('venda_caixas.created_at', '<=', $data_final);
		})
		->join('clientes', 'clientes.id', '=', 'venda_caixas.cliente_id')
		->orWhere(function ($q) use ($data_inicial, $data_final) {
			if ($data_final && $data_final) {
				return $q->whereBetween('venda_caixas.created_at', [
					$data_inicial,
					$data_final
				]);
			}
		})
		->where('venda_caixas.empresa_id', $request->empresa_id)
		->where('venda_caixas.cliente_id', $cliente_id)
		->orderBy($ordem == 'data' ? 'data' : 'total', $ordem == 'data' ? 'desc' : $ordem)
		->limit($total_resultados ?? 1000000)
		->get();

		if (sizeof($vendas) == 0 && sizeof($vendaCaixa) == 0) {
			session()->flash("flash_erro", "Relatório sem registro!");
			return redirect('/relatorios');
		}
		$temp = [];
		$add = [];
		foreach ($vendas as $v) {
			array_push($temp, $v);
			array_push($add, $v->nome);
		}
		for ($i = 0; $i < sizeof($vendaCaixa); $i++) {
			try {
				if (in_array($vendaCaixa[$i]->nome, $add)) {
					$indice = $this->getIndice($vendaCaixa[$i]->nome, $temp);
					$temp[$i]->total_dinheiro += $vendas[$indice]->total_dinheiro;
					$temp[$i]->total += $vendas[$indice]->total;
				} else {
					array_push($temp, $vendaCaixa[$i]);
				}
			} catch (\Exception $e) {
				echo $e->getMessage();
			}
		}
		$cliente = Cliente::findOrFail($cliente_id);
		$p = view('relatorios.relatorio_clientes')
		->with('ordem', $ordem == 'mais' ? 'Menos' : 'Mais')
		->with('data_inicial', $request->data_inicial)
		->with('data_final', $request->data_final)
		->with('cliente', $cliente)
		->with('title', 'Relatório de vendas por cliente(s)')
		->with('vendas', $temp);
		// return $p;
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatório de vendas por cliente(s).pdf", array("Attachment" => false));
	}

	private function getIndice($nome, $arr)
	{
		for ($i = 0; $i < sizeof($arr); $i++) {
			if ($arr[$i]->nome == $nome) return $i;
		}
	}


	public function estoqueProduto(Request $request)
	{
		$ordem = $request->ordem;
		$total_resultados = $request->nr_resultados ?? 1000;
		$categoria = $request->categoria;
		$produtos = Produto::select(\DB::raw('produtos.id, produtos.referencia, produtos.str_grade, produtos.valor_compra, produtos.nome, produtos.unidade_venda, estoques.quantidade, produtos.valor_venda, produtos.percentual_lucro'))
		->leftJoin('estoques', 'produtos.id', '=', 'estoques.produto_id')
		->when(!empty($categoria_id), function ($query) use ($categoria) {
			return $query->where('produtos.categoria_id', $categoria);
		})
		->limit($total_resultados)
		->where('produtos.empresa_id', $request->empresa_id)
		->orderBy('produtos.nome');
		if ($request->categoria != 'todos') {
			$produtos->where('produtos.categoria_id', $request->categoria);
		}
		if ($ordem == 'qtd') {
			$produtos = $produtos->orderBy('estoques.quantidade', 'desc');
		}
		$produtos = $produtos->get();

		$data = [];
		$dataAdd = [];
		foreach ($produtos as $p) {
			$item = ItemCompra::where('produto_id', $p->id)
			->orderBy('id', 'desc')
			->first();
			if ($item != null) {
				$p->data_ultima_compra = \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:m');
			} else {
				$p->data_ultima_compra = '--';
			}

			if(!in_array($p->id, $dataAdd)){
				array_push($data, $p);
				array_push($dataAdd, $p->id);
			}

		}

		// echo $produtos;
		// die();
		$categoria = 'Todos';
		if ($request->categoria != 'todos') {
			$categoria = Categoria::findOrFail($request->categoria)->nome;
		}
		$p = view('relatorios/relatorio_estoque')
		->with('ordem', $ordem == 'asc' ? 'Menos' : 'Mais')
		->with('categoria', $categoria)
		->with('title', 'Relatório de estoque')
		->with('produtos', $data);
		// return $p;
		// if($request->excel == 0){
		$domPdf = new Dompdf();
		$domPdf->loadHtml($p);
		$domPdf->setPaper("A4", "landscape");
		$domPdf->render();
		$domPdf->stream("Relatório de estoque.pdf", array("Attachment" => false));
		// }else{
		// 	$relatorioEx = new RelatorioExport($produtos);
		// 	return Excel::download($relatorioEx, 'estoque de produtos.xlsx');
		// }
	}

	public function comissaoVendas(Request $request)
	{
		$data_inicial = $request->start_date;
		$data_final = $request->end_date;
		$funcionario = $request->funcionario;
		$produto = $request->produto_id;

		$comissoes = ComissaoVenda::where('empresa_id', $request->empresa_id)
		->get();
		$comissoes = ComissaoVenda::select(\DB::raw('comissao_vendas.created_at, comissao_vendas.venda_id, comissao_vendas.valor, funcionarios.nome as funcionario, comissao_vendas.tabela'))
		->when(!empty($data_inicial), function ($query) use ($data_inicial) {
			return $query->whereDate('comissao_vendas.created_at', '>=', $data_inicial);
		})
		->when(!empty($data_final), function ($query) use ($data_final) {
			return $query->whereDate('comissao_vendas.created_at', '<=', $data_final);
		})
		->where('comissao_vendas.empresa_id', $request->empresa_id)
		->join('funcionarios', 'funcionarios.id', '=', 'comissao_vendas.funcionario_id');
		// ->get();
		if ($funcionario == null) {
			session()->flash('flash_erro', 'Selecione um vendedor para continuar');
			return redirect()->back();
		}

		if ($funcionario != 'null') {
			$comissoes = $comissoes->where('funcionario_id', $funcionario);
			$funcionario = Funcionario::findOrFail($funcionario)->nome;
		}
		$comissoes = $comissoes->get();
		$temp = [];

		foreach ($comissoes as $c) {
			// echo $c;
			$c->valor_total_venda = $this->getValorDaVenda($c);
			$c->tipo = $c->tipo();
			if ($produto != 'null') {
				// echo $c;
				$res = $this->getVenda($c, $produto);
				if ($res) {
					array_push($temp, $c);
				}
			} else {
				array_push($temp, $c);
			}
		}
		// if($produto != 'null'){
		// 	$produto = Produto::find($produto)->nome;
		// }
		$p = view('relatorios/relatorio_comissao')
		->with('funcionario', $funcionario)
		->with('produto', $produto)
		->with('title', 'Relatório de comissão')
		->with('data_inicial', $request->data_inicial)
		->with('data_final', $request->data_final)
		->with('comissoes', $comissoes);
		// return $p;
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatório de comissão.pdf", array("Attachment" => false));
	}

	private function getValorDaVenda($comissao)
	{
		$tipo = $comissao->tipo();
		$venda = null;
		if ($tipo == 'PDV') {
			$venda = VendaCaixa::where('id', $comissao->venda_id)
			->where('empresa_id', $this->empresa_id)
			->first();
		} else {
			$venda = Venda::where('id', $comissao->venda_id)
			->where('empresa_id', $this->empresa_id)
			->first();
		}
		if ($venda == null) return 0;
		return $venda->valor_total;
	}

	private function getVenda($comissao, $produto_id)
	{
		$tipo = $comissao->tipo();
		if ($tipo == 'PDV') {
			$venda = VendaCaixa::find($comissao->venda_id);
			foreach ($venda->itens as $i) {
				if ($i->produto_id == $produto_id) {
					return true;
				}
			}
			return false;
		} else {
			$venda = Venda::findOrFail($comissao->venda_id);
			foreach ($venda->itens as $i) {
				if ($i->produto_id == $produto_id) {
					return true;
				}
			}
			return false;
		}
	}

	public function filtroVendaDiaria(Request $request)
	{
		$data = $request->start_date;
		$total_resultados = $request->total_resultados;

		$data_inicial = null;
		$data_final = null;
		if (strlen($data) == 0) {
			session()->flash("flash_erro", "Informe o dia para gerar o relatório!");
			return redirect('/relatorios');
		} else {
			$data_inicial = $data;
			$data_final = $data;
		}
		$vendasCaixa = VendaCaixa::select(\DB::raw('DATE_FORMAT(venda_caixas.data_registro, "%d/%m/%Y %H:%i") as data, sum(venda_caixas.valor_total-venda_caixas.desconto-venda_caixas.acrescimo) as valor_total'))
		->join('item_venda_caixas', 'item_venda_caixas.venda_caixa_id', '=', 'venda_caixas.id')
		->when(!empty($data), function ($query) use ($data) {
			return $query->whereDate('venda_caixas.created_at', $data);
		})
		->where('venda_caixas.empresa_id', $request->empresa_id)
		->groupBy('venda_caixas.id')
		->limit($total_resultados ?? 1000000)
		->get();

		// dd($vendasCaixa);
		$vendas = Venda::select(\DB::raw('DATE_FORMAT(vendas.data_registro, "%d/%m/%Y %H:%i") as data, sum(vendas.valor_total-vendas.desconto-vendas.acrescimo) as valor_total'))
		->when(!empty($data), function ($query) use ($data) {
			return $query->whereDate('vendas.created_at', $data);
		})
		->join('item_vendas', 'item_vendas.venda_id', '=', 'vendas.id')
		->where('vendas.empresa_id', $request->empresa_id)
		->groupBy('vendas.id')
		->limit($total_resultados ?? 1000000)
		->get();

		$arr = $this->uneArrayVendasDay($vendas, $vendasCaixa);
		if($total_resultados){
			$arr = array_slice($arr, 0, $total_resultados);
		}
		// dd($arr);

		// usort($arr, function($a, $b) use ($ordem){
		// 	if($ordem == 'asc') return $a['total'] > $b['total'];
		// 	else if($ordem == 'desc') return $a['total'] < $b['total'];
		// 	else return $a['data'] < $b['data'];
		// });
		if (sizeof($arr) == 0) {
			session()->flash("flash_erro", "Relatório sem registro!");
			return redirect('/relatorios');
		}

		$p = view('relatorios.relatorio_diario')
		->with('data_inicial', $request->data_inicial)
		->with('data_final', $request->data_final)
		->with('title', 'Relatório de vendas')
		->with('vendas', $arr);
		// return $p;
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatório de vendas.pdf", array("Attachment" => false));
	}

	private function uneArrayVendasDay($vendas, $vendasCaixa)
	{
		$adicionados = [];

		$arr = [];

		foreach ($vendas as $v) {

			$temp = [
				'id' => $v->id,
				'data' => $v->data,
				'total' => $v->valor_total,
				'itens' => $v->itens
			];
			array_push($adicionados, $v->data);
			array_push($arr, $temp);
		}
		// foreach ($vendasCaixa as $v) {
		// 	if (!in_array($v->data, $adicionados)) {
		// 		$temp = [
		// 			'id' => $v->id,
		// 			'data' => $v->data,
		// 			'total' => $v->valor_total,
		// 			'itens' => $v->itens
		// 		];
		// 		array_push($adicionados, $v->data);
		// 		array_push($arr, $temp);
		// 	} else {
		// 		for ($aux = 0; $aux < count($arr); $aux++) {
		// 			if ($arr[$aux]['id'] == $v->id) {
		// 				$arr[$aux]['data'] += $v->data;
		// 				$arr[$aux]['total'] += $v->total;
		// 				$arr[$aux]['itens'] += $v->itens;
		// 			}
		// 		}
		// 	}
		// }
		// return $arr;
		foreach($vendasCaixa as $v){

			$temp = [
				'id' => $v->id,
				'data' => $v->data,
				'total' => $v->valor_total,
				'itens' => $v->itens
			];
			array_push($adicionados, $v->data);
			array_push($arr, $temp);

		}
		return $arr;
	}


	public function tiposPagamento(Request $request)
	{
		$data_inicial = $request->start_date;
		$data_final = $request->end_date;
		if (!$data_inicial || !$data_final) {
			session()->flash("flash_erro", "Informe a data inicial e final!");
			return redirect('/relatorios');
		}
		$vendasPdv = VendaCaixa::whereBetween('created_at', [
			$data_inicial = $request->start_date,
			$data_final = $request->end_date
		])
		->where('empresa_id', $this->empresa_id)
		->get();
		$vendas = Venda::whereBetween('created_at', [
			$data_inicial = $request->start_date,
			$data_final = $request->end_date
		])
		->where('empresa_id', $this->empresa_id)
		->get();
		$vendas = $this->agrupaVendas($vendas, $vendasPdv);
		$somaTiposPagamento = $this->somaTiposPagamento($vendas);
		$p = view('relatorios/tipos_pagamento')
		->with('somaTiposPagamento', $somaTiposPagamento)
		->with('data_inicial', $request->data_inicial)
		->with('title', 'Relatório tipos de pagamento')
		->with('data_final', $request->data_final);
		// return $p;
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4");
		$domPdf->render();
		$domPdf->stream("Relatório tipos de pagamento.pdf", array("Attachment" => false));
	}

	private function agrupaVendas($vendas, $vendasPdv)
	{
		$temp = [];
		foreach ($vendas as $v) {
			$v->tipo = 'VENDA';
			array_push($temp, $v);
		}
		foreach ($vendasPdv as $v) {
			$v->tipo = 'PDV';
			array_push($temp, $v);
		}
		return $temp;
	}

	private function preparaTipos()
	{
		$temp = [];
		foreach (VendaCaixa::tiposPagamento() as $key => $tp) {
			$temp[$key] = 0;
		}
		return $temp;
	}

	private function somaTiposPagamento($vendas)
	{
		$tipos = $this->preparaTipos();
		foreach ($vendas as $v) {
			if ($v->estado_emissao != 'cancelado') {
				if (isset($tipos[$v->tipo_pagamento])) {
					if ($v->tipo_pagamento != 99) {
						if (isset($v->numero_nfce)) {
							if (!$v->rascunho && !$v->consignado) {
								$tipos[$v->tipo_pagamento] += $v->valor_total;
							}
						} else {
							if ($v->duplicatas && sizeof($v->duplicatas) > 0) {
								foreach ($v->duplicatas as $d) {
									$tipos[Venda::getTipoPagamentoNFe($d->tipo_pagamento)] += $d->valor_integral;
								}
							} else {
								$tipos[$v->tipo_pagamento] += $v->valor_total - $v->desconto;
							}
						}
					} else {
						if ($v->fatura) {
							foreach ($v->fatura as $f) {
								$tipos[$f->forma_pagamento] += $f->valor;
							}
						}
					}
				}
			}
		}
		return $tipos;
	}

	public function vendaDeProdutos(Request $request)
	{
		$data_inicial = $request->start_date;
		$data_final = $request->end_date;
		$natureza_id = $request->natureza_id;
		$produto_id = $request->produto_id;
		$categoria_id = $request->categoria_id;
		if (!$data_inicial || !$data_final) {
			session()->flash("flash_erro", "Informe a data inicial e final!");
			return redirect('/relatorios');
		}
		// if($data_inicial && $data_final){
		// 	$data_inicial = $request->start_date;
		// 	$data_final = $request->end_date;
		// }
		$diferenca = strtotime($data_final) - strtotime($data_inicial);
		$dias = floor($diferenca / (60 * 60 * 24));
		$dataAtual = $data_inicial;
		$global = [];
		for ($aux = 0; $aux < $dias; $aux++) {

			$itens = ItemVenda::select(\DB::raw('sum(quantidade*valor) as subtotal, sum(quantidade*valor_custo) as subtotalcusto, sum(quantidade) as soma_quantidade, produto_id, avg(valor) as media, valor, valor_custo'))
			->whereBetween(
				'item_vendas.created_at',
				[
					$dataAtual . " 00:00:00",
					$dataAtual . " 23:59:59"
				]
			)
			->join('produtos', 'produtos.id', '=', 'item_vendas.produto_id')
			->join('categorias', 'categorias.id', '=', 'produtos.categoria_id')
			->join('vendas', 'vendas.id', '=', 'item_vendas.venda_id')
			->groupBy('item_vendas.produto_id')
			->where('produtos.empresa_id', $request->empresa_id);
			if ($produto_id) {
				$itens->where('produtos.id', $produto_id);
			}
			if ($categoria_id) {
				$itens->where('categorias.id', $categoria_id);
			}
			if ($natureza_id) {
				$itens->where('vendas.natureza_id', $natureza_id);
			}
			$itens = $itens->get();

			$itensCaixa = ItemVendaCaixa::select(\DB::raw('sum(quantidade*valor) as subtotal, sum(quantidade*valor_custo) as subtotalcusto, sum(quantidade) as soma_quantidade, produto_id, avg(valor) as media, valor, valor_custo'))
			->whereBetween(
				'item_venda_caixas.created_at',
				[
					$dataAtual . " 00:00:00",
					$dataAtual . " 23:59:59"
				]
			)
			->join('produtos', 'produtos.id', '=', 'item_venda_caixas.produto_id')
			->join('categorias', 'categorias.id', '=', 'produtos.categoria_id')
			->join('venda_caixas', 'venda_caixas.id', '=', 'item_venda_caixas.venda_caixa_id')
			->where('produtos.empresa_id', $this->empresa_id)
			->where('venda_caixas.rascunho', 0)
			->where('venda_caixas.consignado', 0)
			->groupBy('item_venda_caixas.produto_id');
			if ($produto_id) {
				$itensCaixa->where('item_venda_caixas.produto_id', $produto_id);
			}
			if ($categoria_id) {
				$itensCaixa->where('categorias.id', $categoria_id);
			}
			$itensCaixa = $itensCaixa->get();
			$todosItens = $this->uneArrayItens($itens, $itensCaixa, $request->ordem);
			$temp = [
				'data' => $dataAtual,
				'itens' => $todosItens,
			];
			array_push($global, $temp);
			$dataAtual = date('Y-m-d', strtotime($dataAtual . '+1day'));
		}
		$config = ConfigNota::where('empresa_id', $request->empresa_id)
		->first();
		$d1 = str_replace("/", "-", $request->start_date);
		$d2 = str_replace("/", "-", $request->end_date);
		$p = view('relatorios.venda_por_produtos')
		->with('itens', $global)
		->with('config', $config)
		->with('title', 'Relatório de venda por produtos')
		->with('data_inicial', $d1)
		->with('data_final', $d2);
		// return $p;
		$domPdf = new Dompdf(["enable_remote" => true]);
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4", "landscape");
		$domPdf->render();
		$domPdf->stream("Relatório de venda por produtos.pdf", array("Attachment" => false));
	}

	private function uneArrayItens($itens, $itensCaixa, $ordem)
	{
		$data = [];
		$adicionados = [];
		foreach ($itens as $i) {
			$temp = [
				'quantidade' => $i->soma_quantidade,
				'subtotal' => $i->subtotal,
				'subtotalcusto' => $i->subtotalcusto,
				'valor' => $i->valor,
				'valor_custo' => $i->valor_custo,
				'media' => $i->media,
				'produto' => $i->produto
			];
			array_push($data, $temp);
			// array_push($adicionados, $i->produto->id);
		}
		// print_r($data[0]['produto']);
		foreach ($itensCaixa as $i) {
			$indiceAdicionado = $this->jaAdicionadoProduto($data, $i->produto->id);
			if ($indiceAdicionado == -1) {
				$temp = [
					'quantidade' => $i->soma_quantidade,
					'subtotal' => $i->subtotal,
					'subtotalcusto' => $i->subtotalcusto,
					'valor' => $i->valor,
					'valor_custo' => $i->valor_custo,
					'media' => $i->media,
					'produto' => $i->produto
				];
				array_push($data, $temp);
			} else {
				$data[$indiceAdicionado]['quantidade'] += $i->soma_quantidade;
				$data[$indiceAdicionado]['subtotal'] += $i->subtotal;
				$data[$indiceAdicionado]['media'] = ($data[$indiceAdicionado]['media'] + $i->media) / 2;
			}
		}
		usort($data, function ($a, $b) use ($ordem) {
			if ($ordem == 'asc') return $a['quantidade'] > $b['quantidade'] ? 1 : 0;
			else if ($ordem == 'desc') return $a['quantidade'] < $b['quantidade'] ? 1 : 0;
			else return $a['produto']->nome > $b['produto']->nome ? 1 : 0;
		});
		return $data;
	}

	private function jaAdicionadoProduto($array, $produtoId)
	{
		for ($i = 0; $i < sizeof($array); $i++) {
			if ($array[$i]['produto']->id == $produtoId) {
				return $i;
			}
		}
		return -1;
	}

public function porCfop(Request $request)
{
    $start_date = $request->start_date;
    $end_date = $request->end_date;
    $cfop = $request->cfop;

    // Consulta unificada para buscar produtos com o CFOP especificado ou todos os CFOPs
    $produtos1 = Produto::where('empresa_id', $request->empresa_id)
        ->when(!empty($cfop) && $cfop !== 'Todos', function ($query) use ($cfop) {
            return $query->where('CFOP_saida_estadual', $cfop);
        })
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->get();

    $produtos2 = Produto::where('empresa_id', $request->empresa_id)
        ->when(!empty($cfop) && $cfop !== 'Todos', function ($query) use ($cfop) {
            return $query->where('CFOP_saida_inter_estadual', $cfop);
        })
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->get();

    // Unir os dois resultados
    $produtos = $produtos1->merge($produtos2);

    // Gerar o relatório em PDF
    $p = view('relatorios.por_cfop')
        ->with('produtos', $produtos)
        ->with('title', 'Relatório CFOP')
        ->with('cfop', $cfop);

    $domPdf = new Dompdf();
    $domPdf->loadHtml($p);
    $pdf = ob_get_clean();
    $domPdf->setPaper("A4");
    $domPdf->render();
    $domPdf->stream("Relatório CFOP.pdf", array("Attachment" => false));
}

	public function clientes(Request $request)
	{
		$start_date = $request->start_date;
		$end_date = $request->end_date;
		$data = Cliente::where('empresa_id', $request->empresa_id)
		->when(!empty($start_date), function ($query) use ($start_date) {
			return $query->whereDate('created_at', '>=', $start_date);
		})
		->when(!empty($end_date), function ($query) use ($end_date) {
			return $query->whereDate('created_at', '<=', $end_date);
		})
		->get();
		
		$p = view('relatorios.clientes')
		->with('data', $data)
		->with('title', 'Relatório de Clientes');
		// return $p;
		$domPdf = new Dompdf();
		$domPdf->loadHtml($p);
		$pdf = ob_get_clean();
		$domPdf->setPaper("A4", "landscape");
		$domPdf->render();
		$domPdf->stream("Relatório de Clientes.pdf", array("Attachment" => false));
	}
}