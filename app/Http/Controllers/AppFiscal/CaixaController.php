<?php

namespace App\Http\Controllers\AppFiscal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AberturaCaixa;
use App\Models\VendaCaixa;
use App\Models\Venda;
use App\Models\SuprimentoCaixa;
use App\Models\SangriaCaixa;

class CaixaController extends Controller
{
	public function index(Request $request){

		$abertura = $this->verificaAberturaCaixa($request->empresa_id);

		$ultimaFechadaNfce = AberturaCaixa::where('ultima_venda_nfce', '>', 0)
		->where('empresa_id', $request->empresa_id)
		->orderBy('id', 'desc')->first();

		$ultimaFechadaNfe = AberturaCaixa::where('ultima_venda_nfe', '>', 0)
		->where('empresa_id', $request->empresa_id)
		->orderBy('id', 'desc')->first();

		$ultimaVendaNfce = VendaCaixa::
		where('empresa_id', $request->empresa_id)
		->orderBy('id', 'desc')->first();

		$ultimaVendaNfe = Venda::
		where('empresa_id', $request->empresa_id)
		->orderBy('id', 'desc')->first();



		$vendas = [];
		$somaTiposPagamento = [];

		$caixa = [];

		if($abertura != -1){
			$caixa = $this->getCaixaAberto($request->empresa_id);
		}


		$ab = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('ultima_venda_nfe', 0)
		->where('empresa_id', $request->empresa_id)
		->where('status', 0)
		->orderBy('id', 'desc')->first();

		$retorno = [
			'caixa' => $caixa,
			'abertura' => $ab
		];

		return response()->json($retorno, 200);
	}

	private function verificaAberturaCaixa($empresa_id){
		$retorno = 0;
		$ab = AberturaCaixa::
		where('empresa_id', $empresa_id)
		->where('status', 0)
		->orderBy('id', 'desc')->first();

		if($ab != null) $retorno = $ab->valor;
		else $retorno = -1;

		return $retorno;
	}

	private function getCaixaAberto($empresa_id){
		$aberturaNfe = AberturaCaixa::where('ultima_venda_nfe', 0)
		->where('empresa_id', $empresa_id)
		->orderBy('id', 'desc')->first();

		$aberturaNfce = AberturaCaixa::where('ultima_venda_nfce', 0)
		->where('empresa_id', $empresa_id)
		->orderBy('id', 'desc')->first();

		$ultimaVendaCaixa = VendaCaixa::
		where('empresa_id', $empresa_id)
		->orderBy('id', 'desc')->first();

		$ultimaVenda = Venda::
		where('empresa_id', $empresa_id)
		->orderBy('id', 'desc')->first();

		$vendas = [];
		$somaTiposPagamento = [];
		if($ultimaVendaCaixa != null || $ultimaVenda != null){
			$ultimaVendaCaixa = $ultimaVendaCaixa != null ? $ultimaVendaCaixa->id : 0;
			$ultimaVenda = $ultimaVenda != null ? $ultimaVenda->id : 0;

			$vendasPdv = VendaCaixa
			::whereBetween('id', [($aberturaNfce != null ? $aberturaNfce->primeira_venda_nfce+1 : 0), 
				$ultimaVendaCaixa])
			->where('empresa_id', $empresa_id)
			->get();

			$vendas = Venda
			::whereBetween('id', [($aberturaNfe != null ? $aberturaNfe->primeira_venda_nfe+1 : 0), 
				$ultimaVenda])
			->where('empresa_id', $empresa_id)
			->get();

			$vendas = $this->agrupaVendas($vendas, $vendasPdv);
			$somaTiposPagamento = $this->somaTiposPagamento($vendas);

		}

		$suprimentos = SuprimentoCaixa::
		whereBetween('created_at', [
			$aberturaNfe->created_at, 
			date('Y-m-d H:i:s')
		])
		->where('empresa_id', $empresa_id)
		->get();

		$sangrias = SangriaCaixa::
		whereBetween('created_at', [$aberturaNfe->created_at, 
			date('Y-m-d H:i:s')])
		->where('empresa_id', $empresa_id)
		->get();

		return [
			'vendas' => $vendas,
			'sangrias' => $sangrias,
			'suprimentos' => $suprimentos,
			'somaTiposPagamento' => $this->alteraTipos($somaTiposPagamento)
		];
	}

	private function agrupaVendas($vendas, $vendasPdv){
		$temp = [];
		foreach($vendas as $v){
			$v->tipo = 'VENDA';

			if($v->cliente_id != null){
				$v->clienteNome = $v->cliente->razao_social;
			}else{
				$v->clienteNome = '--';
			}
			array_push($temp, $v);
		}

		foreach($vendasPdv as $v){
			$v->tipo = 'PDV';

			if($v->cliente_id != null){
				$v->clienteNome = $v->cliente->razao_social;
			}else{
				$v->clienteNome = '--';
			}
			array_push($temp, $v);
		}

		return $temp;
	}

	private function somaTiposPagamento($vendas){
		$tipos = $this->preparaTipos();

		foreach($vendas as $v){

			if(isset($tipos[$v->tipo_pagamento])){
				// $tipos[$v->tipo_pagamento] += $v->valor_total;
				
				if($v->tipo_pagamento != 99){
					$tipos[$v->tipo_pagamento] += $v->valor_total;
				}else{
					if($v->valor_pagamento_1 > 0){
						$tipos[$v->tipo_pagamento_1] += $v->valor_pagamento_1;
					}
					if($v->valor_pagamento_2 > 0){
						$tipos[$v->tipo_pagamento_2] += $v->valor_pagamento_2;
					}
					if($v->valor_pagamento_3 > 0){
						$tipos[$v->tipo_pagamento_3] += $v->valor_pagamento_3;
					}
				}
			}
		}
		return $tipos;

	}

	private function preparaTipos(){
		$temp = [];
		foreach(VendaCaixa::tiposPagamento() as $key => $tp){
			$temp[$key] = 0;
		}
		return $temp;
	}

	private function alteraTipos($somas){
		$temp = [];
		foreach($somas as $key => $s){
			// $temp[VendaCaixa::getTipoPagamento($key)] = $s;
			$t = [
				'tipo' => VendaCaixa::getTipoPagamento($key),
				'valor' => $s
			];
			array_push($temp, $t);
		}
		return $temp;
	}

	public function suprimento(Request $request){
		// return response()->json($request->all(), 200);

		try{
			$result = SuprimentoCaixa::create([
				'usuario_id' => $request->usuario_id,
				'valor' => str_replace(",", ".", $request->valor),
				'observacao' => $request->observacao ?? '',
				'empresa_id' => $request->empresa_id
			]);
			return response()->json($result, 200);

		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}

	}

	public function sangria(Request $request){
		// return response()->json($request->all(), 200);

		try{
			$result = SangriaCaixa::create([
				'usuario_id' => $request->usuario_id,
				'valor' => str_replace(",", ".", $request->valor),
				'observacao' => $request->observacao ?? '',
				'empresa_id' => $request->empresa_id
			]);
			return response()->json($result, 200);

		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}

	}

	public function fechar(Request $request){

		try{
			$id = $request->abertura_id;
			$abertura = AberturaCaixa::find($id);

			$ultimaVendaCaixa = VendaCaixa::
			where('empresa_id', $request->empresa_id)
			->orderBy('id', 'desc')->first();

			$ultimaVenda = Venda::
			where('empresa_id', $request->empresa_id)
			->orderBy('id', 'desc')->first();

			$abertura->ultima_venda_nfce = $ultimaVendaCaixa != null ? 
			$ultimaVendaCaixa->id : 0;
			$abertura->ultima_venda_nfe = $ultimaVenda != null ? $ultimaVenda->id : 0;
			$abertura->status = true;
			$abertura->save();
			return response()->json("ok", 200);

		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}

	}

}
