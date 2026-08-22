<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Cliente;
use App\Models\Empresa;

class LimiteClientes
{

	public function handle($request, Closure $next){

		$value = session('user_logged');
		if($value['super']){
			return $next($request);
		}
		$empresa_id = $value['empresa'];
		$empresa = Empresa::find($empresa_id);
		$dataExp = $empresa->planoEmpresa->expiracao;
		$dataCriacao = substr($empresa->planoEmpresa->created_at, 0, 10);

		$clientes = Cliente::
		whereBetween('created_at', [$dataCriacao, 
            $dataExp])
		->where('empresa_id', $empresa_id)
		->get();

		$contClientes = sizeof($clientes);

		if($empresa->planoEmpresa->plano->maximo_clientes == -1){
			return $next($request);
		}

		if($contClientes < $empresa->planoEmpresa->plano->maximo_clientes){
			return $next($request);
		} else {
            session()->flash('flash_erro', 'Maximo de clientes atingidos ' . $contClientes);
			return redirect()->back();
		}
		
	}

}