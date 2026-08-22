<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\ClienteDelivery;
use App\Models\Produto;
use App\Models\PedidoDelivery;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Usuario;
use App\Models\ItemVendaCaixa;

class HomeController extends Controller
{
    protected $empresa_id = null;
    protected $acesso_financeiro = false;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if (session('user_contador')) {
                return redirect('/contador');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $usuario = Usuario::find(get_id_user());
        $filial_id = $usuario->local_padrao;
        $dataFinal = date('d/m/Y');
        $dataInicial = date('d/m/Y', strtotime('-6 day'));
        
        return view('default.grafico', compact('dataInicial', 'dataFinal'));
    }


    private function totalDeVendasHoje()
    {
        $vendas = Venda::select(\DB::raw('sum(valor_total) as total'))
            ->whereBetween('created_at', [
                date('Y-m-d', strtotime('-1 days')),
                date('Y-m-d', strtotime('+1 day'))
            ])
            ->where('empresa_id', $this->empresa_id)
            ->first();

        $vendaCaixas = VendaCaixa::select(\DB::raw('sum(valor_total) as total'))
            ->whereBetween('created_at', [
                date('Y-m-d', strtotime('-1 days')),
                date('Y-m-d', strtotime('+1 day'))
            ])
            ->where('empresa_id', $this->empresa_id)
            ->first();
        return $vendas->total + $vendaCaixas->total;
    }

    private function totalDePedidosDeDliveryHoje()
    {
        $pedidos = PedidoDelivery::select(\DB::raw('count(*) as linhas'))
            ->whereBetween('data_registro', [
                date("Y-m-d"),
                date('Y-m-d', strtotime('+1 day'))
            ])
            ->first();
        return $pedidos->linhas;
    }

    private function totalDeContaReceberHoje()
    {
        $contasReceber = ContaReceber::select(\DB::raw('sum(valor_integral) as total'))
            ->whereBetween('data_vencimento', [
                date("Y-m-d", strtotime('-1 days')),
                date('Y-m-d', strtotime('+1 day'))
            ])
            ->where('status', false)
            ->where('empresa_id', $this->empresa_id)
            ->first();

        // if ($this->acesso_financeiro == 0) return 0;
        return $contasReceber->total ?? 0;
    }

    private function totalDeContaPagarHoje()
    {
        $contasPagar = ContaPagar::select(\DB::raw('sum(valor_integral) as total'))
            ->whereBetween('data_vencimento', [
                date("Y-m-d"),
                date('Y-m-d', strtotime('+1 day'))
            ])
            ->where('status', false)
            ->where('empresa_id', $this->empresa_id)
            ->first();
        // if ($this->acesso_financeiro == 0) return 0;
        return $contasPagar->total ?? 0;
    }


    public function faturamentoDosUltimosSeteDias()
    {
        $arrayVendas = [];
        for ($aux = 0; $aux > -7; $aux--) {
            $vendas = Venda::select(\DB::raw('sum(valor_total) as total'))
                ->whereBetween(
                    'data_registro',
                    [
                        date('Y-m-d', strtotime($aux . ' day')),
                        date('Y-m-d', strtotime(($aux + 1) . ' day'))
                    ]
                )
                ->where('empresa_id', $this->empresa_id)
                ->first();
            $vendaCaixas = VendaCaixa::select(\DB::raw('sum(valor_total) as total'))
                ->whereBetween(
                    'data_registro',
                    [
                        date('Y-m-d', strtotime($aux . ' day')),
                        date('Y-m-d', strtotime(($aux + 1) . ' day'))
                    ]
                )
                ->where('empresa_id', $this->empresa_id)
                ->first();
            $total = (float)str_replace(",", ".", $vendas->total) + (float)str_replace(",", ".", $vendaCaixas->total);
            $temp = [
                'data' => date('d/m', strtotime(($aux) . ' day')),
                'total' => number_format($total, 2, ".", "")
            ];
            array_push($arrayVendas, $temp);
        }
        if ($this->acesso_financeiro == 0) {
            return response()->json(array_reverse([]));
        }
        return response()->json(array_reverse($arrayVendas));
    }

    public function faturamentoFiltrado(Request $request)
    {
        $dataInicial = strtotime(str_replace("/", "-", $request->data_inicial));
        $dataFinal = strtotime(str_replace("/", "-", $request->data_final));
        $diferenca = ($dataFinal - $dataInicial) / 86400; //86400 segundos do dia
        $arrayVendas = [];
        if ($diferenca + 1 > 30) { //filtrar por mes
            $total = 0;
            for ($aux = 0; $aux > (($diferenca + 1) * -1); $aux--) {
                $vendas = Venda::select(\DB::raw('sum(valor_total) as total'))
                    ->whereBetween(
                        'data_registro',
                        [
                            date('Y-m-d', strtotime($aux . ' day')),
                            date('Y-m-d', strtotime(($aux + 1) . ' day'))
                        ]
                    )
                    ->where('empresa_id', $this->empresa_id)
                    ->first();
                $vendaCaixas = VendaCaixa::select(\DB::raw('sum(valor_total) as total'))
                    ->whereBetween(
                        'data_registro',
                        [
                            date('Y-m-d', strtotime($aux . ' day')),
                            date('Y-m-d', strtotime(($aux + 1) . ' day'))
                        ]
                    )
                    ->where('empresa_id', $this->empresa_id)
                    ->first();
                if ($this->confereMesNoArray($arrayVendas, date('m/Y', strtotime(($aux) . ' day')))) {
                    $cont = 0;
                    foreach ($arrayVendas as $arr) {
                        if ($arr['data'] == date('m/Y', strtotime(($aux) . ' day'))) {
                            $arrayVendas[$cont]['total'] += $vendas->total + $vendaCaixas->total;
                        }
                        $cont++;
                    }
                } else {
                    $temp = [
                        'data' => date('m/Y', strtotime(($aux) . ' day')),
                        'total' => number_format($vendas->total + $vendaCaixas->total, 2, '.', '')
                    ];
                    array_push($arrayVendas, $temp);
                }
            }
        } else { //filtro por dia
            for ($aux = 0; $aux > (($diferenca + 1) * -1); $aux--) {
                $vendas = Venda::select(\DB::raw('sum(valor_total) as total'))
                    ->whereBetween(
                        'data_registro',
                        [
                            date('Y-m-d', strtotime($aux . ' day')),
                            date('Y-m-d', strtotime(($aux + 1) . ' day'))
                        ]
                    )
                    ->where('empresa_id', $this->empresa_id)
                    ->first();
                $vendaCaixas = VendaCaixa::select(\DB::raw('sum(valor_total) as total'))
                    ->whereBetween(
                        'data_registro',
                        [
                            date('Y-m-d', strtotime($aux . ' day')),
                            date('Y-m-d', strtotime(($aux + 1) . ' day'))
                        ]
                    )
                    ->where('empresa_id', $this->empresa_id)
                    ->first();
                $temp = [
                    'data' => date('d/m', strtotime(($aux) . ' day')),
                    'total' => number_format(($vendas->total + $vendaCaixas->total), 2, '.', '')
                ];
                array_push($arrayVendas, $temp);
            }
        }
        if ($this->acesso_financeiro == 0) {
            return response()->json(array_reverse([]));
        }
        return response()->json(array_reverse($arrayVendas));
    }

    private function confereMesNoArray($arr, $mes)
    {
        foreach ($arr as $a) {
            if ($a['data'] == $mes) return true;
        }
        return false;
    }

    private function totalDeVendasDias($dias)
    {
        $vendas = Venda::select(\DB::raw('sum(valor_total) as total'))
            ->whereBetween('created_at', [
                date('Y-m-d', strtotime("-$dias days")),
                date('Y-m-d', strtotime('+1 day'))
            ])
            ->where('empresa_id', $this->empresa_id)
            ->first();
        $vendaCaixas = VendaCaixa::select(\DB::raw('sum(valor_total) as total'))
            ->whereBetween('created_at', [
                date('Y-m-d', strtotime("-$dias days")),
                date('Y-m-d', strtotime('+1 day'))
            ])
            ->where('empresa_id', $this->empresa_id)
            ->first();
        return number_format($vendas->total + $vendaCaixas->total, 2, ',', '.');
    }

    private function totalDePedidosDeDliveryDias($dias)
    {
        $pedidos = PedidoDelivery::select(\DB::raw('count(*) as linhas'))
            ->whereBetween(
                'data_registro',
                [
                    date('Y-m-d', strtotime("-$dias days")),
                    date('Y-m-d', strtotime('+1 day'))
                ]
            )
            ->first();
        return $pedidos->linhas;
    }

    private function totalDeContaReceberDias($dias)
    {
        $contas = ContaReceber::select(\DB::raw('sum(valor_integral) as total'))
            ->whereBetween('data_vencimento', [
                date('Y-m-d', strtotime("-$dias days")),
                date('Y-m-d', strtotime('+1 day'))
            ])
            ->where('status', false)
            ->where('empresa_id', $this->empresa_id)
            ->first();

        return $contas->total ? number_format($contas->total, 2, ',', '.') : 0;
    }

    private function totalDeContaPagarDias($dias)
    {
        $contas = ContaPagar::select(\DB::raw('sum(valor_integral) as total'))
            // ->whereBetween('data_vencimento', [
            //     date('Y-m-d', strtotime("-$dias days")),
            //     date('Y-m-d', date('Y-m-d'))
            // ])
            ->whereDate('data_vencimento', '<=', date('Y-m-d'))
            ->whereDate('created_at', '>=', strtotime("-$dias days"))
            ->where('status', false)
            ->where('empresa_id', 4)
            ->first();

        return $contas->total ? number_format($contas->total, 2, ',', '.') : 0;
    }

    public function boxConsulta($dias)
    {
        $data = [
            'totalDeVendas' => $this->totalDeVendasDias($dias),
            'totalDePedidos' => $this->totalDePedidosDeDliveryDias($dias),
            'totalDeContaReceber' => $this->totalDeContaReceberDias($dias),
            'totalDeContaPagar' => $this->totalDeContaPagarDias($dias)
        ];
        return response()->json($data, 200);
    }
}
