<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthPdv;
use Illuminate\Http\Request;
use App\Models\ImpressaoPedido;

class PedidoController extends Controller
{
    public function index(Request $request){
        $empresaId = (int) $request->attributes->get(AuthPdv::EMPRESA_ID_ATTRIBUTE);

        $data = ImpressaoPedido::
        where('empresa_id', $empresaId)
        ->where('status', 0)
        ->limit(15)
        ->get();

        $itens = [];
        
        if(sizeof($data) > 0){
            $pedidoId = $data[0]->pedido_id;
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
            $empresaId = (int) $request->attributes->get(AuthPdv::EMPRESA_ID_ATTRIBUTE);

            $updated = ImpressaoPedido::query()
                ->where('empresa_id', $empresaId)
                ->where('pedido_id', $request->pedido_id)
                ->update(['status' => 1]);

            if ($updated === 0) {
                return response()->json('Pedido não encontrado', 404);
            }

            return response()->json("ok", 200);

        }catch(\Exception $e){
            return response()->json('Não foi possível atualizar o pedido.', 500);
        }
    }
}
