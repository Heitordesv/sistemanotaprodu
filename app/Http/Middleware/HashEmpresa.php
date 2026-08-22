<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Empresa;

class HashEmpresa{
	public function handle($request, Closure $next){

		$empresa = Empresa::where('hash', $request->hash)->first();
		if($empresa != null) {
			$request->merge(['empresa_id' => $empresa->id]);
		}

		return $next($request);
	}
}