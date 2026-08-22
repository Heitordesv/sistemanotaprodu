<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ItemOrcamento;
use App\Models\NfeRemessa;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;

class VendaController extends Controller
{

    public function linhaProdutoVenda(Request $request){
        try{
            $qtd = $request->qtd;
            $value_unit = __convert_value_bd($request->value_unit);
            $sub_total = __convert_value_bd($request->sub_total);
            $product_id = $request->product_id;

            $rand = rand(0, 10000000);


            $product = Produto::findOrFail($product_id);
            return view('vendas.partials.row_product_purchase', 
                compact('product', 'qtd', 'value_unit', 'sub_total', 'rand'));
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function linhaParcelaVenda(Request $request){
        try{
            $tipo_pagamento = $request->tipo_pagamento;
            $data_vencimento = $request->data_vencimento;
            $valor_integral = $request->valor_integral;
            $quantidade = $request->quantidade;

            $tipo = Venda::getTipo($tipo_pagamento);

            return view('vendas.partials.row_payment_purchase', compact('tipo', 'tipo_pagamento', 'valor_integral', 'data_vencimento', 'quantidade'));
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function linhaParcelaVendaPersonalizado(Request $request){
        try{
            $parcelas = ($request->parcelas);
            $tipoPagamento = $request->tipo_pagamento;
            $intervalo = $request->intervalo;
            $totalGeral = __convert_value_bd($request->total_geral);

            $valorParcela = $totalGeral / $parcelas;

            $tipo = Venda::getTipo($tipoPagamento);
            return view('vendas.partials.row_payment_personalizado', compact('tipo', 'tipoPagamento', 'valorParcela', 'parcelas', 'intervalo', 'totalGeral'));
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }


    public function linhaProdutoOrcamento(Request $request){
        try{
            $qtd = $request->qtd;
            $value_unit = __convert_value_bd($request->value_unit);
            $sub_total = __convert_value_bd($request->sub_total);
            $product_id = $request->product_id;

            $product = Produto::findOrFail($product_id);

            return view('orcamento.row_product_orcamento', compact('product', 'qtd', 'value_unit', 'sub_total'));
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }
}
