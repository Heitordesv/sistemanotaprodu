<?php

namespace App\Http\Controllers\ControleComanda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\CategoriaProdutoDelivery;
use App\Models\Categoria;
use App\Models\ComplementoDelivery;

class ProdutoController extends Controller
{
    public function index(Request $request){

        try{

            $pesquisa = $request->pesquisa;

            $data = Produto::where('produtos.empresa_id', $request->empresa_id)
            ->select('produtos.*')
            ->with('delivery')
            ->when(($request->categoria_id != -1), function ($query) use ($request) {
                // return $query->join('produto_deliveries', 'produto_deliveries.id', '=',
                //     'produtos.id')
                // ->where('produto_deliveries.categoria_id', $request->categoria_id);
                 return $query->where('produtos.categoria_id', $request->categoria_id);
            })
            ->when(($pesquisa != ''), function ($query) use ($pesquisa) {
                return $query->where('produtos.nome', 'like', "%$pesquisa%");
            })
            ->when(($pesquisa == ''), function ($query) use ($pesquisa) {
                return $query->inRandomOrder()->limit(9);
            })
            ->get();

            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function pizzas(Request $request){

        try{

            $pesquisa = $request->pesquisa;

            $data = Produto::where('produtos.empresa_id', $request->empresa_id)
            ->select('produtos.*')
            ->with('delivery')
            ->join('produto_deliveries', 'produto_deliveries.produto_id', '=', 'produtos.id')
            ->join('produto_pizzas', 'produto_pizzas.produto_id', '=', 'produto_deliveries.id')
            ->when(($pesquisa != ''), function ($query) use ($pesquisa) {
                return $query->where('produtos.nome', 'like', "%$pesquisa%");
            })
            ->when(($pesquisa == ''), function ($query) use ($pesquisa) {
                return $query->inRandomOrder()->limit(9);
            })
            ->groupBy('produto_deliveries.id')
            ->get();

            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function categorias(Request $request){

        try{

            $data = Categoria::where('empresa_id', $request->empresa_id)
            ->get();

            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

    public function adicionais(Request $request){

        try{

            $pesquisa = $request->pesquisa;

            $data = ComplementoDelivery::where('empresa_id', $request->empresa_id)
            ->when(($pesquisa != ''), function ($query) use ($pesquisa) {
                return $query->where('nome', 'like', "%$pesquisa%");
            })
            ->when(($pesquisa == ''), function ($query) use ($pesquisa) {
                return $query->inRandomOrder()->limit(9);
            })
            ->get();

            return response()->json($data, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }
}
