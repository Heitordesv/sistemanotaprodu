<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\SubCategoriaEcommerce;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MergeValue;

class CategoriaEcommerceController extends Controller
{
    public function pesquisa(Request $request){
        $data = CategoriaProdutoEcommerce::
        orderBy('nome', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->where('nome', 'like', "%$request->pesquisa%")
        ->get();
        return response()->json($data, 200);

    }

    public function storeCategoria(Request $request){
        try{
            $request->merge([
                'imagem' => $request->imagem ?? ''
            ]);

            $item = CategoriaProdutoEcommerce::create($request->all());
            return response()->json($item, 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 400);
        }
    }

    public function storesubCategoria(Request $request){
        try{

            $item = SubCategoriaEcommerce::create($request->all());
            return response()->json($item, 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 400);
        }
    }

    public function buscarSubCategoria(Request $request){
        $data = SubCategoriaEcommerce::
        where('nome', 'like', "%$request->pesquisa%")
        ->where('categoria_id', $request->categoria_id)
        ->get();

        return response()->json($data, 200);

    }
}
