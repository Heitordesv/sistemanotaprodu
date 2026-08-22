<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\ClienteDelivery;
use App\Models\VendaCaixa;
use App\Models\Venda;

class HomeController extends Controller
{
	public function dadosGrafico(Request $request){
		return response()->json($this->totalizacao($request->empresa_id), 200);
	}
	
	private function totalizacao($empresa_id){
        $totalDeProdutos = sizeof(Produto::where('empresa_id', $empresa_id)->get());
        $totalDeClientes = sizeof(Cliente::where('empresa_id', $empresa_id)->get());

        return [
            'totalDeClientes' => $totalDeClientes,
            'totalDeProdutos' => $totalDeProdutos,
            'totalDeVendas' => $this->totalDeVendasHoje($empresa_id),
            'totalDeContaReceber' => $this->totalDeContaReceberHoje($empresa_id),
            'totalDeContaPagar' => $this->totalDeContaPagarHoje($empresa_id),
            'faturamentoDosUltimosSeteDias' => $this->faturamentoDosUltimosSeteDias($empresa_id)
        ];
    }

    private function totalDeVendasHoje($empresa_id){
        $vendas = Venda::
        select(\DB::raw('sum(valor_total) as total'))
        ->whereBetween('data_registro', [date("Y-m-d"), 
            date('Y-m-d', strtotime('+1 day'))])
        ->where('empresa_id', $empresa_id)
        ->first();

        $vendaCaixas = VendaCaixa::
        select(\DB::raw('sum(valor_total) as total'))
        ->whereBetween('data_registro', [date("Y-m-d"), 
            date('Y-m-d', strtotime('+1 day'))])
        ->where('empresa_id', $empresa_id)
        ->first();


        return $vendas->total + $vendaCaixas->total;

    }


    private function totalDeContaReceberHoje($empresa_id){
        $contas = ContaReceber::
        select(\DB::raw('sum(valor_integral) as total'))
        ->whereBetween('data_vencimento', [date("Y-m-d"), 
            date('Y-m-d', strtotime('+1 day'))])
        ->where('status', false)
        ->where('empresa_id', $empresa_id)
        ->first(); 
        return $contas->total ?? 0;
    }

    private function totalDeContaPagarHoje($empresa_id){
        $contas = ContaPagar::
        select(\DB::raw('sum(valor_integral) as total'))
        ->whereBetween('date_register', [date("Y-m-d"), 
            date('Y-m-d', strtotime('+1 day'))])
        ->where('status', false)
        ->where('empresa_id', $empresa_id)
        ->first(); 
        return $contas->total ?? 0;
    }



    public function faturamentoDosUltimosSeteDias($empresa_id){

        $arrayVendas = [];
        for($aux = 0; $aux > -7; $aux--){
            $vendas = Venda::
            select(\DB::raw('sum(valor_total) as total'))
            ->whereBetween('data_registro', 
                [
                    date('Y-m-d', strtotime($aux.' day')), 
                    date('Y-m-d', strtotime(($aux+1).' day'))
                ]
            )
            ->where('empresa_id', $empresa_id)
            ->first();


            $vendaCaixas = VendaCaixa::
            select(\DB::raw('sum(valor_total) as total'))
            ->whereBetween('data_registro', 
                [
                    date('Y-m-d', strtotime($aux.' day')), 
                    date('Y-m-d', strtotime(($aux+1).' day'))
                ]
            )
            ->where('empresa_id', $empresa_id)
            ->first();
            
            $temp = [
                'data' => date('d/m', strtotime(($aux).' day')),
                'total' => number_format(($vendas->total + $vendaCaixas->total), 2)
            ];
            array_push($arrayVendas, $temp);
        }
        return array_reverse($arrayVendas);
        
    }

    public function faturamentoFiltrado(Request $request){

        $dataInicial = strtotime(str_replace("/", "-", $request->data_inicial));
        $dataFinal = strtotime(str_replace("/", "-", $request->data_final));

        $diferenca = ($dataFinal - $dataInicial)/86400; //86400 segundos do dia

        $arrayVendas = [];
        
        if($diferenca+1 > 30){ //filtrar por mes

            $total = 0;
            for($aux = 0; $aux > (($diferenca+1)*-1); $aux--){
                $vendas = Venda::
                select(\DB::raw('sum(valor_total) as total'))
                ->whereBetween('data_registro', 
                    [
                        date('Y-m-d', strtotime($aux.' day')), 
                        date('Y-m-d', strtotime(($aux+1).' day'))
                    ]
                )
                ->first();


                $vendaCaixas = VendaCaixa::
                select(\DB::raw('sum(valor_total) as total'))
                ->whereBetween('data_registro', 
                    [
                        date('Y-m-d', strtotime($aux.' day')), 
                        date('Y-m-d', strtotime(($aux+1).' day'))
                    ]
                )
                ->first();

                if($this->confereMesNoArray($arrayVendas, date('m/Y', strtotime(($aux).' day')))){
                    $cont = 0;
                    foreach($arrayVendas as $arr){
                        if($arr['data'] == date('m/Y', strtotime(($aux).' day'))){
                            $arrayVendas[$cont]['total'] += $vendas->total + $vendaCaixas->total;

                        }
                        $cont++;
                    }
                }else{
                    $temp = [
                        'data' => date('m/Y', strtotime(($aux).' day')),
                        'total' => number_format($total, 2)
                    ];
                    array_push($arrayVendas, $temp);
                }
                
            }
            
        }else{ //filtro por dia
            for($aux = 0; $aux > (($diferenca+1)*-1); $aux--){
                $vendas = Venda::
                select(\DB::raw('sum(valor_total) as total'))
                ->whereBetween('data_registro', 
                    [
                        date('Y-m-d', strtotime($aux.' day')), 
                        date('Y-m-d', strtotime(($aux+1).' day'))
                    ]
                )
                ->first();


                $vendaCaixas = VendaCaixa::
                select(\DB::raw('sum(valor_total) as total'))
                ->whereBetween('data_registro', 
                    [
                        date('Y-m-d', strtotime($aux.' day')), 
                        date('Y-m-d', strtotime(($aux+1).' day'))
                    ]
                )
                ->first();
                $temp = [
                    'data' => date('d/m', strtotime(($aux).' day')),
                    'total' => number_format(($vendas->total + $vendaCaixas->total), 2)
                ];
                array_push($arrayVendas, $temp);
            }
        }
        return response()->json(array_reverse($arrayVendas));
        
    }

    private function confereMesNoArray($arr, $mes){
        foreach($arr as $a){
            if($a['data'] == $mes) return true;
        }
        return false;
    }
}