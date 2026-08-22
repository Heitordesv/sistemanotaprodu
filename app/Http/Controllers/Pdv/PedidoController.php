<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImpressaoPedido;

class PedidoController extends Controller
{
    public function index(Request $request){
        $data = ImpressaoPedido::
        where('empresa_id', $request->empresa_id)
        ->where('status', 0)
        ->limit(15)
        ->get();
        // return response()->json($data, 200);

        $itens = [];
        
        if(sizeof($data) > 0){
            $pedidoId = $data[0]->pedido_id;
            // return $pedidoId;
            foreach($data as $item){

                if($item->pedido_id == $pedidoId){

                    $item->produto_nome = $item->produto->nome;
                    array_push($itens, $item);
                }
            }
        }

        return response()->json($itens, 200);
    }

    public function setImpresso(Request $request){
        try{
            ImpressaoPedido::where('pedido_id', $request->pedido_id)
            ->update(['status' => 1]);
            return response()->json("ok", 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }
}
