<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\Request;

class CotacaoController extends Controller
{
    public function linhaProduto(Request $request){
        try{
            $qtd = $request->qtd;
            $product_id = $request->product_id;
            $product = Produto::findOrFail($product_id);

            return view('cotacao.partials.row_cotacao_purchase', compact('product', 'qtd'));
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }
}
