<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Venda;
use App\Models\Usuario;
use App\Models\ConfigNota;
use App\Models\UsuarioAcesso;

class VerificaEmpresa
{

	public function handle($request, Closure $next){

		$value = session('user_logged');
		if($value){
			$usuario = Usuario::find($value['id']);
			if(sizeof($usuario->acessos) > 0){
				$ult = $usuario->acessos[sizeof($usuario->acessos)-1];
				$ult->updated_at = date('Y-m-d H:i:s');
				$ult->save();
			}

			if(!$usuario->empresa->status && $value['super'] == 0){
				$usuarioSessao = UsuarioAcesso::
				where('usuario_id', $value['id'])
				->where('status', 0)
				->get();

				foreach($usuarioSessao as $u){
					$u->status = 1;
					$u->save();
				}

				session()->forget('user_logged');
				if($usuario->empresa->mensagem_bloqueio != ""){
					session()->flash('flash_erro', $usuario->empresa->mensagem_bloqueio);
				}else{
					session()->flash('flash_erro', 'Empresa desativada');
				}
				return redirect("/login");
			}

			$request->merge([ 'empresa_id' => $value['empresa'] ?? null]);

			return $next($request);
		}else{
			return redirect('/login');
		}
	}

}