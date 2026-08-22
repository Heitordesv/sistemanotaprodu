<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Venda;
use App\Models\ConfigNota;

class ValidaEvento
{
	public function handle($request, Closure $next){

		$value = session('user_logged');
		$uri = $_SERVER['REQUEST_URI'];
		$uri = explode("/", $uri);

		if($value['adm']){
			return $next($request);
		}else{
			if(!isset($uri[2])){
				return $next($request);
			}else{
				if($uri[2] == 'funcionarios'){
					return redirect('/403');
				}
				// if($uri[2] == 'novo'){
				// 	return redirect('/403');
				// }
				// if($uri[2] == 'movimentacao'){
				// 	return redirect('/403');
				// }
			}

			return $next($request);
		}

	}

}