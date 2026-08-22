<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;

class ProdutoController extends Controller
{
    public function index(Request $request){
        $update_date = "";
        if($request->update_date){
            $update_date = $request->update_date;
        }

        $produtos = Produto::
        where('empresa_id', $request->empresa_id);

        if(strlen($update_date) > 0){
            $produtos->where('updated_at', '>', $update_date);
            // $produtos->where('updated_at', '>', '2022-12-06 00:00:00');
        }
        $produtos = $produtos->get();

        foreach($produtos as $p){
            $p->estoque_atual = $p->estoqueAtual2();
            $p->categoria_nome = $p->categoria->nome;
        }

        return response()->json($produtos, 200);
    }

    public function count(Request $request){
        $count = Produto::
        where('empresa_id', $request->empresa_id)
        ->count();

        return response()->json($count, 200);
    }

    public function limit(Request $request){

        $update_date = "";
        if($request->update_date){
            $update_date = $request->update_date;
        }

        // $produtos = Produto::
        // where('produtos.empresa_id', $request->empresa_id)
        // ->limit(1)
        // ->select(
        //     'produtos.nome as nome', 'valor_venda', 'valor_compra', 'perc_glp', 'referencia', 'CST_CSOSN',
        //     'CST_PIS', 'CST_COFINS', 'CST_IPI', 'perc_icms', 'perc_pis', 'perc_cofins', 'perc_ipi',
        //     'categorias.nome as categoria_nome'
        // )
        // ->join('categorias', 'categorias.id', '=', 'produtos.categoria_id')
        // ->where('produtos.id', '>', $request->id);

        // if(strlen($update_date) > 0){
        //     $produtos->where('produtos.updated_at', '>', $update_date);
        // }
        // $produtos = $produtos->get();

        // foreach($produtos as $p){
        //     $p->estoque_atual = $p->estoqueAtual2();
        // }

        $produtos = Produto::
        where('empresa_id', $request->empresa_id)
        ->limit(300);

        if(strlen($update_date) > 0){
            $produtos->where('updated_at', '>', $update_date);
            // $produtos->where('updated_at', '>', '2022-12-06 00:00:00');
        }
        $produtos = $produtos->get();

        foreach($produtos as $p){
            $p->estoque_atual = $p->estoqueAtual2();
            $p->categoria_nome = $p->categoria->nome;
        }

        return response()->json($produtos, 200);
    }
}
