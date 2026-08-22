<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProdutoDelivery;
use App\Models\ComplementoDelivery;
use App\Models\ListaComplementoDelivery;
use App\Models\Produto;
use App\Models\ProdutoDelivery;
use App\Models\ProdutoPizza;
use App\Models\TamanhoPizza;
use Database\Seeders\CategoriaSeed;

use Illuminate\Http\Request;

class ProdutoDeliveryController extends Controller
{
    public function filtroCategoria(Request $request)
    {
        $item = CategoriaProdutoDelivery::findOrFail($request->categoria_id);

        if ($item->tipo_pizza) {
            $tamanhos = TamanhoPizza::where('empresa_id', $request->empresa_id)->get();
            return view('produtos_delivery.partials.valor_pizzas', compact('tamanhos'));
        } else {
            return response()->json("", 200);
        }
    }

    public function filtroAdicionais(Request $request)
    {
        $produto = ProdutoDelivery::findOrFail($request->produto_id);

        $comp = ComplementoDelivery::where('empresa_id', $produto->empresa_id)->get();
        $data = [];
        if ($produto->tem_adicionais) {
            foreach ($comp as $t) {
                $cat = $t->categoria ? json_decode($t->categoria) : [];

                if (in_array($produto->categoria_id, $cat)) {
                    array_push($data, $t);
                }
            }
        }

        // return response()->json($data, 200);

        return view('pedido_delivery.partials.adicionais', compact('data'));
    }

    public function tamanhosPizza(Request $request)
    {
        $produto = ProdutoDelivery::findOrFail($request->produto_id);
        $tamanhos = TamanhoPizza::where('empresa_id', $produto->empresa_id)->get();
        return response()->json($tamanhos, 200);
    }

    public function sabores(Request $request)
    {
        $produto = ProdutoDelivery::findOrFail($request->produto_id);
        $data = ProdutoDelivery::select('produto_deliveries.*')
            ->orderBy('produtos.nome')
            ->join('produtos', 'produtos.id', '=', 'produto_deliveries.produto_id')
            ->join('categoria_produto_deliveries', 'produto_deliveries.categoria_id', '=', 'categoria_produto_deliveries.id')
            ->where('produtos.empresa_id', $produto->empresa_id)
            ->where('produto_deliveries.id', '!=', $produto->id)
            ->where('categoria_produto_deliveries.tipo_pizza', 1)
            ->with('pizza')
            ->get();
        $tamanho = $request->tamanho_id;
        return view('pedido_delivery.partials.sabores_pizza', compact('data', 'tamanho'));
    }

    public function valorPizza(Request $request)
    {
        $data = ProdutoPizza::where('produto_id', $request->produto_id)
        ->where('tamanho_id', $request->tamanho_id)->first();
        return response()->json($data, 200);

        // return view('pedido_delivery.partials.sabores_pizza', compact('data'));
    }
}
