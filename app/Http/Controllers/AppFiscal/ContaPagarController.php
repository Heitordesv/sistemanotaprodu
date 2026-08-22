<?php

namespace App\Http\Controllers\AppFiscal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContaPagar;
use App\Models\CategoriaConta;

class ContaPagarController extends Controller
{
	public function contas(Request $request){
		$contas = ContaPagar::
		where('empresa_id', $request->empresa_id)
		->whereBetween('data_vencimento', [date("Y-m-d"), 
			date('Y-m-d', strtotime('+1 month'))])
		->orderBy('data_vencimento', 'desc')
		// ->where('status', 0)
		->get();

		foreach($contas as $c){
			if($c->compra_id != null){
				$c->compra->fornecedor;
			}
			$c->categoria;
		}
		
		return response()->json($contas, 200);
	}

	public function filtro(Request $request){

		$dataInicial = $request->data_inicio;
		$dataFinal = $request->data_final;
		$fornecedor = $request->fornecedor;
		// $categoria = $request->categoria;
		$status = $request->estado;
		$contas = [];

		$contas = ContaPagar::
		select('conta_pagars.*');

		if($fornecedor != ""){
			$contas->join('compras', 'compras.id' , '=', 'conta_pagars.compra_id');
			$contas->join('fornecedors', 'fornecedors.id' , '=', 'compras.fornecedor_id');
			$contas->where('fornecedors.razao_social', 'LIKE', "%$fornecedor%");
		}

		if($dataInicial && $dataFinal){
			
			$contas->whereBetween('conta_pagars.data_vencimento', 
				[
					$dataInicial . " 00:00:00",
					$dataFinal . " 23:59:00"
				]
			);
			
		}
		
		if($status != ''){
			if($status == 'pago'){
				$contas->where('status', true);
			} else if($status == 'pendente'){
				$contas->where('status', false);
			}
		}

		// if($categoria != ''){
		// 	$contas->where('categoria_id', $categoria);
		// }

		$contas->where('conta_pagars.empresa_id', $request->empresa_id);

		$contas = $contas->get();

		foreach($contas as $c){
			if($c->compra_id != null){
				$c->compra->fornecedor;
			}
			$c->categoria;
		}
		
		return response()->json($contas, 200);
	}

	public function categoriasConta(Request $request){
		$categorias = CategoriaConta::
		where('empresa_id', $request->empresa_id)
		->where('tipo', 'pagar')
		->get();
		
		return response()->json($categorias, 200);
	}

	public function pagar(Request $request){

		try{
			$conta = ContaPagar::find($request->id);

			$conta->status = true;
			$conta->valor_pago = __replace($request->valor);
			$conta->data_pagamento = date("Y-m-d");
			$conta->tipo_pagamento = $request->tipo_pagamento;

			$conta->save();

			return response()->json("ok", 200);
		}catch(\Exception $e){
			return response()->json("Erro: " . $e->getMessage(), 401);
		}
	}

	public function salvar(Request $request){
		
		if($request->id > 0){
			$conta = ContaPagar::find($request->id);
			$conta->referencia = $request->referencia;
			$conta->valor_integral = __replace($request->valor);

			$conta->categoria_id = $request->categoria;
			$conta->data_vencimento = \Carbon\Carbon::parse($request->vencimento)->format('Y-m-d');
			$conta->status = $request->status ? true : false;

			$res = $conta->save();
		}else{
			$data = [
				'referencia' => $request->referencia,

				'categoria_id' => $request->categoria,
				'data_vencimento' => \Carbon\Carbon::parse($request->vencimento)->format('Y-m-d'),
				'data_pagamento' => \Carbon\Carbon::parse($request->vencimento)->format('Y-m-d'),

				'status' => $request->status ? true : false,
				'compra_id' => null,
				'empresa_id' => $request->empresa_id,
				'valor_pago' => $request->status ? __replace($request->valor) : 0,
				'valor_integral' => __replace($request->valor)
			];
			$res = ContaPagar::create($data);

			$loopRecorrencia = $this->calculaRecorrencia($request->recorrencia);

			if($loopRecorrencia > 0){

				$vencimento = \Carbon\Carbon::parse($request->vencimento)->format('d/m/Y');
				$diaVencimento = substr($vencimento, 0, 2);
				$proximoMes = substr($vencimento, 3, 2);
				$ano = substr($vencimento, 6, 4);

				while($loopRecorrencia > 0){
					$proximoMes = $proximoMes == 12 ? 1 : $proximoMes+1;
					$proximoMes = $proximoMes < 10 ? "0".$proximoMes : $proximoMes;
					if($proximoMes == 1)  $ano++;
					$d = $diaVencimento . "/".$proximoMes . "/" . $ano;

					$result = ContaPagar::create([
						'compra_id' => null,
						'data_vencimento' => $this->parseDate($d),
						'data_pagamento' => $this->parseDate($d),
						'valor_integral' => str_replace(",", ".", $request->valor),
						'valor_pago' => 0,
						'status' => false,
						'referencia' => $request->referencia,
						'categoria_id' => $request->categoria,
						'empresa_id' => $request->empresa_id
					]);
					$loopRecorrencia--;
				}
			}
		}

		
		return response()->json($res, 200);
	}

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	private function calculaRecorrencia($recorrencia){
		if(strlen($recorrencia) == 5){
			$dataAtual = date("Y-m");
			$dif = strtotime($this->parseRecorrencia($recorrencia)) - strtotime($dataAtual);

			$meses = floor($dif / (60 * 60 * 24 * 30));

			return $meses;
		}
		return 0;
	}

	private function parseRecorrencia($rec){
		$temp = explode("/", $rec);
		$rec = "01/".$temp[0]."/20".$temp[1];
		//echo $rec;
		return date('Y-m', strtotime(str_replace("/", "-", $rec)));
	}

	public function delete(Request $request){
		$conta = ContaPagar::find($request->id);
		$conta = $conta->delete();
		return response()->json($conta, 200);
	}
}
