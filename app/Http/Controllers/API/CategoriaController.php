<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\SubCategoria;

class CategoriaController extends Controller
{
    public function pesquisa(Request $request){
        $data = Categoria::
        orderBy('nome', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->where('nome', 'like', "%$request->pesquisa%")
        ->get();
        return response()->json($data, 200);

    }

    public function storeCategoria(Request $request){
        try{

            $item = Categoria::create($request->all());
            return response()->json($item, 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 400);
        }
    }

    public function storesubCategoria(Request $request){
        try{

            $item = SubCategoria::create($request->all());
            return response()->json($item, 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 400);
        }
    }

    public function buscarSubCategoria(Request $request){
        $data = SubCategoria::
        where('nome', 'like', "%$request->pesquisa%")
        ->where('categoria_id', $request->categoria_id)
        ->get();

        return response()->json($data, 200);

    }
}
