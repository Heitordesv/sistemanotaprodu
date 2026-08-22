<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Venda;
use App\Models\Usuario;
use App\Models\ConfigNota;
use App\Helpers\Menu;

class ValidaAcesso
{
	public function handle($request, Closure $next){

		$value = session('user_logged');

		if(!$value){
			$protocolo = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
			$uri = $_SERVER['REQUEST_URI'];
			$host = $_SERVER['HTTP_HOST'];

			$uri = $protocolo . $host . $uri;
			return redirect("/login")->with('uri', $uri);
		}

		if($request->ajax()){
			return $next($request);
		}

		if($value['super']){
			return $next($request);
		}

		$urip = $_SERVER['REQUEST_URI'];
		$urip = explode("/", $urip);

		$uri = "/".$urip[1];
		if(isset($urip[2])){
			$uri .= "/".$urip[2];
		}
		$value = session('user_logged');
		$usuario = Usuario::find($value['id']);
		$permissao = json_decode($usuario->permissao);	
		// dd($permissao);
		// die();
		foreach($permissao as $p){
			if($p == $uri){
				return $next($request);
			}
		}
		$valida = $this->validaRotaInexistente($uri);
		
		if($valida == true){
			return redirect('/error');
		}else{
			// se a rota nao disponivel no helper menu.php quer dizer que não precisa ser controlada
			return $next($request);
		}
	}

	private function validaRotaInexistente($uri){
		$existe = false;

		$menu = new Menu();
		$menu = $menu->getMenu();
		foreach($menu as $m){
			foreach($m['subs'] as $s){

				if($s['rota'] == $uri){
					$existe = true;
				}
			}
		}
		return $existe;
	}

}