<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Pedido;

class MesaAtiva
{
	public function handle($request, Closure $next){
		$mesa_open = session('mesa_open');
		if(!isset($mesa_open['pedido_id'])){
			return response()->json('A mesa não esta aberta!!', 404);
		}else{
			$pedido = Pedido::find($mesa_open['pedido_id']);
			if($pedido != null){
				if($pedido->mesa_ativa == 1){
					return $next($request);
				} else {
					return response()->json('O caixa irá autorizar sua mesa, aguarde por gentileza!!', 404);
				}
			}else{
				session()->forget('mesa_open');
				return response()->json('Por favor escaneie o QrCode novamente!!', 404);
			}
		}
		
	}

}