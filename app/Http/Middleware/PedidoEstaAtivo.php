<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Pedido;

class PedidoEstaAtivo
{

	public function handle($request, Closure $next){

		$mesa_open = session('mesa_open');
		if(!isset($mesa_open['pedido_id'])){
			session()->flash("message_erro", "Esta mesa foi desativada!!");
			return redirect('/pedido');
		}else{
			$pedido = Pedido::find($mesa_open['pedido_id']);

			if($pedido->mesa_ativa == 1 && $pedido->desativado == 0){
				return $next($request);
			} else {
				session()->forget('mesa_open');
				session()->flash("message_erro", "Esta mesa foi desativada!!");
				return redirect('/pedido');
			}
		}

		
	}

}