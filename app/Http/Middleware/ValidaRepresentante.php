<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\RepresentanteEmpresa;
use App\Models\Representante;

class validaRepresentante
{
	public function handle($request, Closure $next){

		$value = session('user_logged');
		$uri = $_SERVER['REQUEST_URI'];
		$uri = explode("/", $uri);
		$representanteId = $uri[3];
		$empresaId = $value['empresa'];

		$representante = Representante::
		where('usuario_id', get_id_user())
		->first();

		if($representante == null){
			return redirect('/403');
		}

		// echo $representate;
		$rep = RepresentanteEmpresa::
		where('empresa_id', $representanteId)
		->where('representante_id', $representante->id)
		->exists();

		if(!$rep){
			return redirect('/403');
		}
		return $next($request);

	}

}