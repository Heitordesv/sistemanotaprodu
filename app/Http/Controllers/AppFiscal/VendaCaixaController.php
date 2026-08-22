<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\VendaCaixa;
use App\Models\Venda;
use App\Models\ItemVendaCaixa;
use App\Models\Produto;
use App\Helpers\StockMove;
use App\Models\ListaPreco;
use App\Models\ContaReceber;
use App\Models\CategoriaConta;
use App\Services\NFCeService;
use NFePHP\DA\NFe\Danfce;
use Dompdf\Dompdf;
use NFePHP\DA\NFe\Cupom;
use App\Models\ConfigNota;
use App\Models\AberturaCaixa;

class VendaCaixaController extends Controller
{

	public function index(Request $request){
		$vendas = VendaCaixa::orderBy('id', 'desc')
		->where('empresa_id', $request->empresa_id)
		->limit(50)->get();

		$config = ConfigNota::
		where('empresa_id', $request->empresa_id)
		->first();
		$public = env('SERVIDOR_WEB') ? 'public/' : '';

		foreach($vendas as $v){
			foreach($v->itens as $i){
				$i->produto;
				$i->valor = number_format($i->valor, $config->casas_decimais);

			}
			$v->tpPag = VendaCaixa::getTipoPagamento($v->tipo_pagamento);
			$v->cliente;
			$v->natureza;
			$v->config = $config;
			$v->valor_total = number_format($v->valor_total, $config->casas_decimais);

			$v->urlChave = '';
			if($v->chave != ''){
				try{
					$xml = simplexml_load_file($public.'xml_nfce/'.$v->chave.'.xml');
					$v->urlChave = (string)$xml->NFe->infNFeSupl->urlChave;

					$v->nNF = (int)$xml->NFe->infNFe->ide->nNF[0];
					$v->nSerie = (int)$xml->NFe->infNFe->ide->serie[0];
					$v->dtEmi = (string)$xml->NFe->infNFe->ide->dhEmi[0];
					$v->dtAut = (string)$xml->protNFe->infProt->dhRecbto[0];
					$v->nProt = (int)$xml->protNFe->infProt->nProt[0];
				}catch(\Exception $e){
					$v->urlChave = "";
					$v->nNF = "";
					$v->nSerie = "";
					$v->dtEmi = "";
					$v->dtAut = "";
					$v->nProt = "";
				}
			}

		}
		return response()->json($vendas, 200);
	}

	public function ambiente(Request $request){
		$config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
		if($config != null){
			return response()->json($config->ambiente, 200);
		}else{
			return response()->json('erro', 401);
		}
	}

	public function filtroVendas(Request $request){
		$dataInicial = $request->data_inicio;
		$dataFinal = $request->data_final;
		$estado = $request->estado ? $request->estado : 'TODOS';

		// if(isset($dataInicial) && isset($dataFinal)){
		// 	$vendas = VendaCaixa::filtroDataApp(
		// 		$this->parseDate($dataInicial),
		// 		$this->parseDate($dataFinal, true),
		// 		$request->empresa_id
		// 	);
		// }else if(isset($estado)){
		// 	$vendas = VendaCaixa::filtroEstadoApp(
		// 		$estado,
		// 		$request->empresa_id
		// 	);
		// }

		$vendas = VendaCaixa::
		where('empresa_id', $request->empresa_id)
		->where('forma_pagamento', '!=', 'conta_crediario');

		if(isset($dataInicial) && isset($dataFinal)){
			$vendas->whereBetween('created_at', [
				$this->parseDate($dataInicial),
				$this->parseDate($dataFinal, true)
			]);
		}

		if($estado != 'TODOS'){
			$vendas->where('estado', $estado);
		}

		$vendas = $vendas->get();

		// $vendas = Venda::orderBy('id', 'desc')->get();
		foreach($vendas as $v){
			foreach($v->itens as $i){
				$i->produto;
			}
			$v->cliente;
			$v->natureza;
		}
		return response()->json($vendas, 200);
	}

	private function itetable($array){
		$temp = [];
		foreach($array as $key => $a){
			$t = [
				'cod' => $key,
				'value' => $a
			];
			array_push($temp, $t);
		}
		return $temp;
	}

	public function getVenda($id){
		$venda = VendaCaixa::find($id);
		$venda->cliente;
		$venda->natureza;
		// $venda->itens;
		foreach($venda->itens as $i){
			$i->produto;
		}
		return response()->json($venda, 200);
	}

	public function salvar(Request $request){
		try{
			// $config = ConfigNota::first();
			$config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

			$arrVenda = [
				'cliente_id' => $request->cliente,
				'usuario_id' => $request->usuario_id,
				'valor_total' => $request->total,
				'NFcNumero' => 0,
				'natureza_id' => $config->nat_op_padrao,
				'chave' => '',
				'path_xml' => '',
				'estado' => 'DISPONIVEL',
				'tipo_pagamento' => $request->tipoPagamento,
				'forma_pagamento' => $request->formaPagamento,
				'dinheiro_recebido' => $request->valor_recebido,
				'troco' => $request->troco,
				'nome' => '',
				'cpf' => $request->cpf ?? '',
				'observacao' => $request->observacao ?? '',
				'desconto' => $request->desconto,
				'acrescimo' => 0,
				'pedido_delivery_id' => 0,
				'empresa_id' => $request->empresa_id
			];

			$result = VendaCaixa::create($arrVenda);

			$itens = $request->itens;
			$stockMove = new StockMove();
			foreach ($itens as $i) {
				$t = [
					'venda_caixa_id' => $result->id,
					'produto_id' => (int) $i['item_id'],
					'quantidade' => (float) str_replace(",", ".", $i['quantidade']),
					'valor' => (float) str_replace(",", ".", $i['valor']),
					'item_pedido_id' => null, 
					'observacao' => ''
				];
				$pTemp = Produto::find((int)$i['item_id']);
				ItemVendaCaixa::create([
					'venda_caixa_id' => $result->id,
					'produto_id' => (int) $i['item_id'],
					'quantidade' => (float) str_replace(",", ".", $i['quantidade']),
					'valor' => (float) str_replace(",", ".", $i['valor']),
					'item_pedido_id' => null, 
					'observacao' => '',
					'valor_custo' => $pTemp->valor_compra
				]);

				$prod = Produto
				::where('id', $i['item_id'])
				->first();

				if(!empty($prod->receita)){
				//baixa por receita
					$receita = $prod->receita; 
					foreach($receita->itens as $rec){


						if(!empty($rec->produto->receita)){ 

							$receita2 = $rec->produto->receita; 

							foreach($receita2->itens as $rec2){
								$stockMove->downStock(
									$rec2->produto_id, 
									(float) str_replace(",", ".", $i['quantidade']) * 
									($rec2->quantidade/$receita2->rendimento)
								);
							}
						}else{

							$stockMove->downStock(
								$rec->produto_id, 
								(float) str_replace(",", ".", $i['quantidade']) * 
								($rec->quantidade/$receita->rendimento)
							);
						}
					}
				}else{
					$stockMove->downStock(
						(int) $i['item_id'], (float) str_replace(",", ".", $i['quantidade']));
				}
			}

			$v = VendaCaixa::find($result->id);
			foreach($v->itens as $i){
				$i->produto;
				$i->valor = number_format($i->valor, $config->casas_decimais);

			}
			$v->cliente;
			$v->natureza;
			$v->tpPag = VendaCaixa::getTipoPagamento($v->tipo_pagamento);
			$v->valor_total = number_format($v->valor_total, $config->casas_decimais);
			$v->config = $config;
			$v->urlChave = '';
			$public = env('SERVIDOR_WEB') ? 'public/' : '';
			if($v->chave != ''){
				$xml = simplexml_load_file($public.'xml_nfce/'.$v->chave.'.xml');
				$v->urlChave = $xml->NFe->infNFeSupl->urlChave;
			}

			if($v->tipo_pagamento == '06'){
				$categoria = $this->categoriaCrediario($request->empresa_id);

				foreach ($request->fatura as $f) {
					// $dataVenc = date('Y-m-d', strtotime("+30 days",
					// 	strtotime(date('Y-m-d'))));
					$resultConta = ContaReceber::create([
						'venda_caixa_id' => $v->id,
						'venda_id' => NULL,
						'data_vencimento' => $this->parseDate($f['vencimento']),
						'data_recebimento' => $this->parseDate($f['vencimento']),
						'valor_integral' => __replace($f['valor']),
						'valor_recebido' => 0,
						'status' => false,
						'referencia' => "Venda PDV " . $result->id,
						'categoria_id' => $categoria,
						'empresa_id' => $request->empresa_id
					]);
				}
			}

			return response()->json($v, 200);

		}catch(\Exception $e){
			return response()->json($e->getMessage(), 403);
		}
	}

	private function categoriaCrediario($empresa_id){
		$cat = CategoriaConta::
		where('empresa_id', $empresa_id)
		->where('nome', 'Crediário')
		->first();
		if($cat != null) return $cat->id;
		$cat = CategoriaConta::create([
			'nome' => 'Crediário',
			'empresa_id' => $empresa_id,
			'tipo'=> 'receber'
		]);
		return $cat->id;
	}

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	// public function delete(Request $request){
	// 	$venda = VendaCaixa::find($request->id);
	// 	$delete = $venda->delete();
	// 	return response()->json($delete, 200);
	// }

	public function delete(Request $request){
		$venda = VendaCaixa::find($request->id);
		$config = ConfigNota::where('empresa_id', $venda->empresa_id)->first();
		$senha = $request->senha;

		if($config->senha_remover == ""){
			$delete = $venda->delete();
			return response()->json($delete, 200);
		}else{
			if(md5($senha) == $config->senha_remover){
				$delete = $venda->delete();
				return response()->json($delete, 200);
			}
			else{
				return response()->json("Senha incorreta", 403);
			}
		}
	}

	public function renderizarDanfe($id){

		$venda = VendaCaixa::find($id);

		$config = ConfigNota::where('empresa_id', $venda->empresa_id)->first();

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$nfe_service = new NFCeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		], $venda->empresa_id);


		$nfe = $nfe_service->gerarNFCe($id);

		$xml = $nfe['xml'];
		$signed = $nfe_service->sign($xml);
		$public = env('SERVIDOR_WEB') ? 'public/' : '';

		if($config->logo){
			$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'logos/' . $config->logo));
		}else{
			$logo = null;
		}

		try {
			$danfce = new Danfce($signed, $venda);
			// $danfce->monta($logo);
			$pdf = $danfce->render($logo);
			header('Content-Type: application/pdf');
			return response($pdf)
			->header('Content-Type', 'application/pdf');
		} catch (InvalidArgumentException $e) {
			return response()->json("erro", 401);
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  
	}

	public function cupomNaoFiscal($id){
		$venda = VendaCaixa::
		where('id', $id)
		->first();
		$public = env('SERVIDOR_WEB') ? 'public/' : '';
		// $pathLogo = $public.'imgs/logo.jpg';
		$config = ConfigNota::where('empresa_id', $venda->empresa_id)->first();
		

		if($config->logo){
			$pathLogo = $public.'logos/' . $config->logo;
		}else{
			$pathLogo = $public.'imgs/logo.jpg';
		}

		$config = ConfigNota::where('empresa_id', $venda->empresa_id)->first();

		$cupom = new Cupom($venda, $pathLogo, $config);
		$cupom->monta();
		$pdf = $cupom->render();

		return response($pdf)
		->header('Content-Type', 'application/pdf');
	}

	public function caixaAberto(Request $request){
		$retorno = 0;
		$ab = AberturaCaixa::
		where('empresa_id', $request->empresa_id)
		->where('status', 0)
		->orderBy('id', 'desc')->first();

		if($ab != null) $retorno = $ab->valor;
		else $retorno = -1;

		return response()->json($retorno, 200);

		// $ab2 = AberturaCaixa::where('ultima_venda_nfe', 0)
		// ->where('empresa_id', $request->empresa_id)
		// ->where('status', 0)
		// ->orderBy('id', 'desc')->first();

		// // echo $ab;
		// // die();

		// if($ab != null && $ab2 == null){
		// 	$retorno = $ab->valor;
		// }else if($ab == null && $ab2 != null){
		// 	$ab2->valor;
		// }else if($ab != null && $ab2 != null){
		// 	if(strtotime($ab->created_at) > strtotime($ab2->created_at)){
		// 		$ab->valor;
		// 	}else{
		// 		$ab2->valor;
		// 	}
		// }else{
		// 	$retorno = -1;
		// }

		// if($ab != null) $retorno = $ab->valor;
		// else $retorno = -1;

		// return response()->json($retorno, 200);
	}

	public function abrirCaixa(Request $request){

		$ultimaVendaNfce = VendaCaixa::
		where('empresa_id', $request->empresa_id)
		->orderBy('id', 'desc')->first();

		$ultimaVendaNfe = Venda::
		where('empresa_id', $request->empresa_id)
		->orderBy('id', 'desc')->first();

		try{
			$result = AberturaCaixa::create([
				'usuario_id' => $request->usuario_id,
				'valor' => str_replace(",", ".", $request->valor),
				'empresa_id' => $request->empresa_id,
				'primeira_venda_nfe' => $ultimaVendaNfe != null ? 
				$ultimaVendaNfe->id : 0,
				'primeira_venda_nfce' => $ultimaVendaNfce != null ? 
				$ultimaVendaNfce->id : 0,
				'status' => 0
			]);
			return response()->json($result, 200);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

}