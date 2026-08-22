<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoriaProdutoDelivery;
use App\Models\ProdutoDelivery;
use App\Models\ComplementoDelivery;
use App\Models\CarrosselDelivery;

class ProdutoController extends Controller
{
    public function all(){
        $data = CategoriaProdutoDelivery::
        where('empresa_id', request()->empresa_id)
        ->with('produtos')
        ->get();

        return response()->json($data, 200);
    }

    public function adicionais(Request $request){

        $temp = ComplementoDelivery::
        where('empresa_id', $request->empresa_id)
        ->get();

        $data = [];
        foreach($temp as $t){
            $cat = $t->categoria ? json_decode($t->categoria) : [];
            if(in_array($request->categoria_id, $cat)){
                array_push($data, $t);
            }
        }

        return response()->json($data, 200);
    }

    public function find($id){
        $item = ProdutoDelivery::
        with('produto')
        ->with('categoria')
        ->with('pizza')
        ->findOrFail($id);

        return response()->json($item, 200);
    }

    public function carrossel(){
        $data = CarrosselDelivery::
        where('empresa_id', request()->empresa_id)
        ->where('status', 1)
        ->orderBy('status', 'desc')
        ->orderBy('valor_ordem', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($data, 200);
    }

}
