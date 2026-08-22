<?php

namespace App\Http\Middleware;

use Closure;
use Response;

class PedidoAtivo
{

	public function handle($request, Closure $next){

		$modulo = env('PEDIDO_QRCODE');
		if($modulo == 1){
			return $next($request);
		} else {
			return redirect('http://google.com');
		}
		
	}

}