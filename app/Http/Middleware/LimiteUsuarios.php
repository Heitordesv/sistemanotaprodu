<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Usuario;
use App\Models\Empresa;

class LimiteUsuarios
{

	public function handle($request, Closure $next){

		// return $next($request);
		
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		if($value['super']){
			return $next($request);
		}
		$empresa = Empresa::find($empresa_id);
		$dataExp = $empresa->planoEmpresa->expiracao;
		$dataCriacao = substr($empresa->planoEmpresa->created_at, 0, 10);

		$produtos = Usuario::
		where('empresa_id', $empresa_id)
		->get();

		$contProdutos = sizeof($produtos);

		if($empresa->planoEmpresa->plano->maximo_usuario == -1 || $empresa->planoEmpresa->plano->armazenamento > 0){
			return $next($request);
		}

		if($contProdutos < $empresa->planoEmpresa->plano->maximo_usuario){
			return $next($request);
		} else {
			session()->flash('flash_erro', 'Maximo de usuários atingidos ' . $contProdutos);
			return redirect()->back();
		}
		
	}

}