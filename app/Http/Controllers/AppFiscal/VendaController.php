<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Models\ItemVenda;
use App\Models\Produto;
use App\Helpers\StockMove;
use App\Models\ListaPreco;
use App\Models\ContaReceber;
use App\Services\NFService;
use NFePHP\DA\NFe\Danfe;
use Dompdf\Dompdf;
use App\Models\ConfigNota;
use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\FaturaOrcamento;

class VendaController extends Controller
{

	public function index(Request $request){
		$vendas = Venda::orderBy('id', 'desc')
		->where('empresa_id', $request->empresa_id)
		->limit(40)->get();
		foreach($vendas as $v){
			foreach($v->itens as $i){
				$i->produto;
			}
			$v->cliente;
			$v->natureza;
		}
		return response()->json($vendas, 200);
	}

	public function orcamentos(Request $request){
		$orcamentos = Orcamento::orderBy('id', 'desc')
		->where('empresa_id', $request->empresa_id)
		->limit(40)->get();
		foreach($orcamentos as $v){
			foreach($v->itens as $i){
				$i->produto;
			}
			$v->cliente;
			$v->natureza;
		}
		return response()->json($orcamentos, 200);
	}

	public function filtroVendas(Request $request){
		$dataInicial = $request->data_inicio;
		$dataFinal = $request->data_final;
		$cliente = $request->cliente;
		$estado = $request->estado ? $request->estado : 'TODOS';

		if(isset($cliente) && isset($dataInicial) && isset($dataFinal)){
			$vendas = Venda::filtroDataCliente(
				$cliente, 
				$this->parseDate($dataInicial),
				$this->parseDate($dataFinal, true),
				$estado
			);
		}else if(isset($dataInicial) && isset($dataFinal)){
			$vendas = Venda::filtroData(
				$this->parseDate($dataInicial),
				$this->parseDate($dataFinal, true),
				$estado
			);
		}else if(isset($cliente)){
			$vendas = Venda::filtroCliente(
				$cliente,
				$estado
			);


		}else if(isset($estado)){
			$vendas = Venda::filtroEstado(
				$estado
			);
		}

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

	public function tiposDePagamento(){
		return response()->json($this->itetable(VendaCaixa::tiposPagamento()), 200);
	}

	public function listaDePrecos(){
		$listas = ListaPreco::where('empresa_id', request()->empresa_id)->get();
		return response()->json($listas, 200);
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
		$venda = Venda::find($id);
		$venda->cliente;
		$venda->natureza;
		$venda->itens;
		foreach($venda->itens as $i){
			$i->produto;
		}
		return response()->json($venda, 200);
	}

	public function salvar(Request $request){
		try{
			$frete = null;
			if($request->tipoFrete != '9'){
				$frete = Frete::create([
					'placa' => $request->placa,
					'valor' => $request->valorFrete,
					'tipo' => (int)$request->tipoFrete,
					'qtdVolumes' => $request->quantidadeVolumes,
					'uf' => $request->uf,
					'numeracaoVolumes' => $request->numeracaoVolumes,
					'especie' => $request->especie,
					'peso_liquido' => $request->pesoLiquido,
					'peso_bruto' => $request->pesoBruto
				]);
			}

			$result = Venda::create([
				'cliente_id' => $request->cliente,
				'transportadora_id' => $request->transportadora,
				'forma_pagamento' => $request->formaPagamento,
				'tipo_pagamento' => $request->tipoPagamento,
				'usuario_id' => $request->user_id,
				'valor_total' => $request->total,
				'desconto' => 0,
				'frete_id' => $frete,
				'NfNumero' => 0,
				'natureza_id' => $request->natureza,
				'path_xml' => '',
				'chave' => '',
				'sequencia_cce' => 0,
				'observacao' => '',
				'estado' => 'DISPONIVEL',
				'empresa_id' => $request->empresa_id
			]);

			$itens = $request->itens;
			$stockMove = new StockMove();
			foreach ($itens as $i) {
				$pTemp = Produto::find((int)$i['item_id']);
				ItemVenda::create([
					'venda_id' => $result->id,
					'produto_id' => (int) $i['item_id'],
					'quantidade' => (float) str_replace(",", ".", $i['quantidade']),
					'valor' => (float) str_replace(",", ".", $i['valor']),
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


			if($request->formaPagamento != 'a_vista' && $request->formaPagamento != 'conta_crediario'){
				$fatura = $request->fatura;

				foreach ($fatura as $key=> $f) {
					$valorParcela = str_replace(",", ".", $f['valor']);

					$resultFatura = ContaReceber::create([
						'venda_id' => $result->id,
						'data_vencimento' => $this->parseDate($f['vencimento']),
						'data_recebimento' => $this->parseDate($f['vencimento']),
						'valor_integral' => $valorParcela,
						'valor_recebido' => 0,
						'status' => false,
						'referencia' => "Parcela, ".($key+1).", da Venda " . $result->id,
						'categoria_id' => 2,
						'empresa_id' => $request->empresa_id
					]);
				}
			}
			return response()->json("sucesso", 200);

		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	public function salvarOrcamento(Request $request){

		try{
			$dt = date("Y-m-d");
			$result = Orcamento::create([
				'cliente_id' => $request->cliente,
				'transportadora_id' => $request->transportadora,
				'forma_pagamento' => $request->formaPagamento,
				'tipo_pagamento' => $request->tipoPagamento,
				'usuario_id' => $request->user_id,
				'valor_total' => $request->total,
				'desconto' => 0,
				'frete_id' => null,
				'natureza_id' => $request->natureza,
				'observacao' => '',
				'estado' => 'NOVO',
				'email_enviado' => 0,
				'validade' => date( "Y-m-d", strtotime( "$dt +7 day" )),
				'venda_id' => 0,
				'empresa_id' => $request->empresa_id
			]);


			$itens = $request->itens;
			foreach ($itens as $i) {
				ItemOrcamento::create([
					'orcamento_id' => $result->id,
					'produto_id' => (int) $i['item_id'],
					'quantidade' => (float) str_replace(",", ".", $i['quantidade']),
					'valor' => (float) str_replace(",", ".", $i['valor'])
				]);
			}

			if($request->formaPagamento != 'a_vista' && $request->formaPagamento != 'conta_crediario'){
				$fatura = $request->fatura;

				foreach ($fatura as $f) {
					$valorParcela = str_replace(",", ".", $f['valor']);

					$resultFatura = FaturaOrcamento::create([
						'orcamento_id' => $result->id,
						'vencimento' => $this->parseDate($f['vencimento']),
						'valor' => $valorParcela
					]);
				}
			}else{
				$resultFatura = FaturaOrcamento::create([
					'orcamento_id' => $result->id,
					'vencimento' => date('Y-m-d'),
					'empresa_id' => $request->empresa_id,
					'valor' => $request->total
				]);
			}
			return response()->json("sucesso", 200);


		}catch(\Exception $e){
			return response()->json("Erro: " . $e->getMessage(), 401);
		}

		
	}

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	public function delete(Request $request){
		$venda = Venda::find($request->id);
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
		$venda = Venda::find($id);
		$config = ConfigNota::where('empresa_id', $venda->empresa_id)->first();

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
		$nfe_service = new NFService([
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
		], $config->empresa_id);

		$nfe = $nfe_service->gerarNFe($id);

		if(!isset($nfe['erros_xml'])){

			$xml = $nfe['xml'];
			try {
				$danfe = new Danfe($xml);
				// $id = $danfe->monta();
				$pdf = $danfe->render();
				return response($pdf)
				->header('Content-Type', 'application/pdf');
			} catch (\Exception $e) {
				return response()->json("erro: " . $e->getMessage(), 401);
			}  
		}else{
			return response()->json($nfe['erros_xml'], 401);
		}
	}

	public function renderizarXml($id){
		$venda = Venda::find($id);
		$config = ConfigNota::where('empresa_id', $venda->empresa_id)->first();

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$nfe_service = new NFService([
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
		], $config->empresa_id);
		$nfe = $nfe_service->gerarNFe($id);
		try {
			$xml = $nfe['xml'];

			// return response()->json($xml, 200);
			// header('Content-Type: application/xml');
			return response($xml)
			->header('Content-Type', 'application/xml');
		} catch (\Exception $e) {
			return response()->json("erro", 401);
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  

	}

	public function ambiente(Request $request){
		$config = ConfigNota::find($request->empresa_id);
		if($config != null){
			return response()->json($config->ambiente, 200);
		}else{
			return response()->json('erro', 401);
		}
	}

}