<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function pesquisa(Request $request){
        $data = Marca::
        orderBy('nome', 'desc')
        ->where('empresa_id', $request->empresa_id)
        ->where('nome', 'like', "%$request->pesquisa%")
        ->get();
        return response()->json($data, 200);

    }

    public function store(Request $request){
        try{
            $item = Marca::create($request->all());
            return response()->json($item, 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 400);
        }
    }

}
