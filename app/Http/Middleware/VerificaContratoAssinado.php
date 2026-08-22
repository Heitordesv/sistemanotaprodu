<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Empresa;
use App\Models\Contrato;

class VerificaContratoAssinado
{

	public function handle($request, Closure $next){

		$value = session('user_logged');

		if($value['super']){
			return $next($request);
		}

		$uri = $_SERVER['REQUEST_URI'];
		if($uri == '/configNF' || $uri == '/configNF/save'){
			return $next($request);
		}

		$empresa = Empresa::find($value['empresa']);
		$dataHoje = strtotime(date('Y-m-d'));

		$contrato = Contrato::first();

		$exp = $empresa->planoEmpresa ? $empresa->planoEmpresa->expiracao : null;
		$dif = strtotime($exp) - $dataHoje;
		$dias = $dif/60/60/24;

		if(!$empresa->planoEmpresa){
			session()->flash("flash_erro", "Realize o pagamento para continuar!");
			return redirect()->route('payment.index');
		}

		if($dias <= 0 && $empresa->planoEmpresa->expiracao != '0000-00-00'){
			session()->flash("flash_erro", "Realize o pagamento para continuar!");
			return redirect('/payment');
		}

		if($contrato != null){
			$dias = env("DIAS_ASSINAR_CONTRATO");

			if($empresa->contrato == null){
				return $next($request);
			}
			
			$dataContrato = \Carbon\Carbon::parse($empresa->contrato->created_at)->format('Y-m-d');

			$dataContrato = strtotime($dataContrato);
			$dif = $dataHoje - $dataContrato;
			$dif = $dif/24/60/60;

			if($dif >= $dias && $empresa->contrato->status == 0){
				session()->flash("flash_erro", "Assine o contrato para continuar!");
				return redirect('/assinarContrato');
			}
		}
		return $next($request);

	}

}