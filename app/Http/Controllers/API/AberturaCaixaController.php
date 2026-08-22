<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AberturaCaixa;
use App\Models\ConfigNota;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;

class AberturaCaixaController extends Controller
{
    // protected $empresa_id = null;
	// public function __construct(){
	// 	$this->middleware(function ($request, $next) {
	// 		$this->empresa_id = $request->empresa_id;
	// 		$value = session('user_logged');
	// 		if(!$value){
	// 			return redirect("/login");
	// 		}
	// 		return $next($request);
	// 	});
	// }

    public function storeCaixa(Request $request){

		$ultimaVendaNfce = VendaCaixa::
		where('empresa_id', $this->$request->empresa_id)
		->orderBy('id', 'desc')->first();

		$ultimaVendaNfe = Venda::
		where('empresa_id', $this->$request->empresa_id)
		->orderBy('id', 'desc')->first();
		$verify = $this->verificaAberturaCaixa();

		if($verify == -1){
			$result = AberturaCaixa::create([
				'usuario_id' => get_id_user(),
				'valor' => __convert_value_bd($request->valor),
				'empresa_id' => $this->$request->empresa_id,
				'primeira_venda_nfe' => $ultimaVendaNfe != null ?
				$ultimaVendaNfe->id : 0,
				'primeira_venda_nfce' => $ultimaVendaNfce != null ?
				$ultimaVendaNfce->id : 0,
				'status' => 0
			]);
			echo json_encode($result);
		}else{
			echo json_encode(true);
		}
	}

    public function verificaHoje(){
		echo json_encode($this->verificaAberturaCaixa());
	}

    private function verificaAberturaCaixa(){
		$config = ConfigNota::where('empresa_id', $this->empresa_id)->first();

		$ab = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('empresa_id', $this->empresa_id)
		->where('status', 0)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		$ab2 = AberturaCaixa::where('ultima_venda_nfe', 0)
		->where('empresa_id', $this->empresa_id)
		->where('status', 0)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->orderBy('id', 'desc')->first();

		if($ab != null && $ab2 == null){
			return $ab->valor;
		}else if($ab == null && $ab2 != null){
			$ab2->valor;
		}else if($ab != null && $ab2 != null){
			if(strtotime($ab->created_at) > strtotime($ab2->created_at)){
				$ab->valor;
			}else{
				$ab2->valor;
			}
		}else{
			return -1;
		}
		if($ab != null) return $ab->valor;
		else return -1;
	}
}
