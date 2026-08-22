<?php

namespace App\Http\Controllers\AppFiscal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContaReceber;
use App\Models\CategoriaConta;

class ContaReceberController extends Controller
{
	public function contas(Request $request){
		$contas = ContaReceber::
		where('empresa_id', $request->empresa_id)
		->whereBetween('data_vencimento', [date("Y-m-d"), 
			date('Y-m-d', strtotime('+1 month'))])
		->orderBy('data_vencimento', 'desc')
		->where('status', 0)
		->get();

		foreach($contas as $c){
			$c->cliente;
			$c->categoria;
		}
		
		return response()->json($contas, 200);
	}

	public function filtro(Request $request){

		$dataInicial = $request->data_inicio;
		$dataFinal = $request->data_final;
		$cliente = $request->cliente;
		$categoria = $request->categoria;
		$status = $request->estado;
		$contas = [];

		$contas = ContaReceber::
		select('conta_recebers.*');

		if($cliente != ""){
			$contas->join('clientes', 'clientes.id' , '=', 'conta_recebers.cliente_id');
			$contas->where('clientes.razao_social', 'LIKE', "%$cliente%");
		}

		if($dataInicial && $dataFinal){
			
			$contas->whereBetween('conta_recebers.data_vencimento', 
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

		if($categoria != ''){
			$contas->where('categoria_id', $categoria);
		}

		$contas->where('conta_recebers.empresa_id', $request->empresa_id);

		$contas = $contas->get();

		foreach($contas as $c){
			$c->cliente;
			$c->categoria;
		}
		
		return response()->json($contas, 200);
	}

	public function categoriasConta(Request $request){
		$categorias = CategoriaConta::
		where('empresa_id', $request->empresa_id)
		->where('tipo', 'receber')
		->get();
		
		return response()->json($categorias, 200);
	}

	public function receber(Request $request){

		try{
			$conta = ContaReceber::find($request->id);

			if($request->inserirOutra == false){
				$conta->status = true;
				$conta->valor_recebido = __replace($request->valor);
				$conta->data_recebimento = date("Y-m-d");

				$conta->save();
			}else{
				$valor = __replace($request->valor);

				$res = ContaReceber::create([
					'venda_id' => $conta->venda_id,
					'venda_caixa_id' => $conta->venda_caixa_id,
					'cliente_id' => $conta->cliente_id,
					'data_vencimento' => $conta->data_vencimento,
					'data_recebimento' => $conta->data_recebimento,
					'valor_integral' => $request->diferenca,
					'valor_recebido' => 0,
					'status' => false,
					'referencia' => $conta->referencia,
					'categoria_id' => $conta->categoria_id,
					'empresa_id' => $request->empresa_id,
				]);

				$conta->status = true;
				$conta->valor_recebido = __replace($request->valor);
				$conta->valor_integral = __replace($request->valor);
				$conta->data_recebimento = date("Y-m-d");

				$result = $conta->save();
			}

			return response()->json("ok", 200);
		}catch(\Exception $e){
			return response()->json("Erro: " . $e->getMessage(), 401);
		}
	}

	public function salvar(Request $request){
		
		if($request->id > 0){
			$conta = ContaReceber::find($request->id);
			$conta->referencia = $request->referencia;
			$conta->valor_integral = __replace($request->valor);
			$conta->cliente_id = $request->cliente;
			$conta->categoria_id = $request->categoria;
			$conta->data_vencimento = \Carbon\Carbon::parse($request->vencimento)->format('Y-m-d');
			$conta->status = $request->status ? true : false;

			$res = $conta->save();
		}else{
			$data = [
				'referencia' => $request->referencia,
				'cliente_id' => $request->cliente,
				'categoria_id' => $request->categoria,
				'data_vencimento' => \Carbon\Carbon::parse($request->vencimento)->format('Y-m-d'),
				'data_recebimento' => \Carbon\Carbon::parse($request->vencimento)->format('Y-m-d'),

				'status' => $request->status ? true : false,
				'venda_id' => null,
				'empresa_id' => $request->empresa_id,
				'valor_recebido' => $request->status ? __replace($request->valor) : 0,
				'valor_integral' => __replace($request->valor)
			];
			$res = ContaReceber::create($data);

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

					$result = ContaReceber::create([
						'venda_id' => null,
						'data_vencimento' => $this->parseDate($d),
						'data_recebimento' => $this->parseDate($d),
						'valor_integral' => str_replace(",", ".", $request->valor),
						'valor_recebido' => 0,
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
		$conta = ContaReceber::find($request->id);
		$conta = $conta->delete();
		return response()->json($conta, 200);
	}

}
