<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Produto;
use App\Models\Empresa;

class LimiteProdutos
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

		$produtos = Produto::
		whereBetween('created_at', [$dataCriacao, 
            $dataExp])
		->where('empresa_id', $empresa_id)
		->get();

		$contProdutos = sizeof($produtos);

		if($empresa->planoEmpresa->plano->maximo_produtos == -1 || $empresa->planoEmpresa->plano->armazenamento > 0){
			return $next($request);
		}

		if($contProdutos < $empresa->planoEmpresa->plano->maximo_produtos){
			return $next($request);
		} else {
            session()->flash('flash_erro', 'Maximo de podutos atingidos ' . $contProdutos);
			return redirect()->back();
		}
		
	}

}