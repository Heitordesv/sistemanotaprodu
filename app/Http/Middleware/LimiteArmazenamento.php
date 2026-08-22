<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Empresa;
use \DB;

class LimiteArmazenamento
{

	public function handle($request, Closure $next)
	{
		$value = session('user_logged');
		if ($value['super']) {
			return $next($request);
		}
		$empresa_id = $value['empresa'];
		$empresa = Empresa::find($empresa_id);
		if (!$empresa->planoEmpresa) {
			session()->flash("flash_login", "Empresa sem plano atribuído!");
			return redirect()->route('logoff');
		}

		if ($empresa->planoEmpresa->plano->armazenamento <= 0) {
			return $next($request);
		}

		$armazenamento = number_format($this->totalArmazenamento($empresa) / 1000, 2);
		$totalParaArmazenar = $empresa->planoEmpresa->plano->armazenamento;

		if ($armazenamento >= $totalParaArmazenar) {
			session()->flash('flash_erro', 'Limite de armazenamento atingido, faça um upgrade de plano ou contate o suporte!');
			return redirect('/payment');
		}
		return $next($request);
	}

	private function totalArmazenamento($empresa)
	{
		$armazenamento = $empresa->planoEmpresa->plano->armazenamento;
		$tabelasArmazenamento = tabelasArmazenamento();

		$soma = 0;
		foreach ($tabelasArmazenamento as $key => $t) {
			try {
				$res = DB::table($key)
					->select(DB::raw('count(*) as linhas'))
					->where('empresa_id', $empresa->id)
					->first();

				$soma += $res->linhas * $t;
			} catch (\Exception $e) {
			}
		}

		return $soma;
	}
}
