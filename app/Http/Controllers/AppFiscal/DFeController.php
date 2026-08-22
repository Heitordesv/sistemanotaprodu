<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\ItemDfe;
use App\Models\ManifestaDfe;
use App\Models\ConfigNota;
use App\Models\Produto;
use App\Services\DFeService;
use NFePHP\DA\NFe\Danfe;
use NFePHP\NFe\Common\Standardize;

class DFeController extends Controller
{
	public function index(Request $request){
		
		$manifestos = ManifestaDfe::
		where('empresa_id', $request->empresa_id)
		->orderBy('id', 'desc')
		->get();
		return response()->json($manifestos, 200);
	}

	public function manifestar(Request $request){
		$dfe = ManifestaDfe::find($request->id);
		$evento = $request->tipo;
		$motivo = $request->motivo ?? '';

		$config = ConfigNota::where('empresa_id', $request->empresa_id)
		->first();

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$dfe_service = new DFeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => 1,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->UF,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		], 55, $config->empresa_id);

		try{
			$manifestaAnterior = $this->verificaAnterior($dfe->chave);
			if($evento == 1){
				$res = $dfe_service->manifesta($dfe->chave,	 
					$manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1);
			}else if($evento == 2){
				$res = $dfe_service->confirmacao($dfe->chave,	 
					$manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1);
			}else if($evento == 3){
				$res = $dfe_service->desconhecimento($dfe->chave,	 
					$manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1, $motivo);
			}else if($evento == 4){
				$res = $dfe_service->operacaoNaoRealizada($dfe->chave,	 
					$manifestaAnterior != null ? ($manifestaAnterior->sequencia_evento + 1) : 1, $motivo);
			}

			if($res['retEvento']['infEvento']['cStat'] == '135'){ 
				
				$dfe->tipo = $evento;
				$dfe->save();
				return response()->json($dfe, 201);

			}else{

				$dfe->tipo = $evento;
				$dfe->save();
				return response()->json($dfe, 200);

			}

		}catch(\Exception $e){
			return response()->json($e, 401);
		}
	}

	private function verificaAnterior($chave){
		return ManifestaDfe::where('chave', $chave)->first();
	}

	public function novosDocumentos(Request $request){
		try{

			$config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

			$cnpj = str_replace(".", "", $config->cnpj);
			$cnpj = str_replace("/", "", $cnpj);
			$cnpj = str_replace("-", "", $cnpj);
			$cnpj = str_replace(" ", "", $cnpj);


			$dfe_service = new DFeService([
				"atualizacao" => date('Y-m-d h:i:s'),
				"tpAmb" => 1,
				"razaosocial" => $config->razao_social,
				"siglaUF" => $config->UF,
				"cnpj" => $cnpj,
				"schemes" => "PL_009_V4",
				"versao" => "4.00",
				"tokenIBPT" => "AAAAAAA",
				"CSC" => $config->csc,
				"CSCid" => $config->csc_id
			], 55, $config->empresa_id);

			$manifesto = ManifestaDfe::
			where('empresa_id', $request->empresa_id)
			->orderBy('nsu', 'desc')->first();

			if($manifesto == null) $nsu = 0;
			else $nsu = $manifesto->nsu;

			$docs = $dfe_service->novaConsulta($nsu);

			$novos = [];
			// foreach($docs as $d) {
			// 	if($this->validaNaoInserido($d['chave'])){
			// 		if($d['valor'] > 0 && $d['nome']){
			// 			ManifestaDfe::create($d);
			// 			array_push($novos, $d);
			// 		}
			// 	}
			// }
			// return response()->json($novos, 200);

			if(!isset($docs['erro'])){

				$novos = [];
				foreach($docs as $d) {
					if($this->validaNaoInserido($d['chave'])){
						if($d['valor'] > 0 && $d['nome']){
							ManifestaDfe::create($d);
							array_push($novos, $d);
						}
					}
				}

				ManifestoDia::create([
					'empresa_id' => $this->empresa_id
				]);
				return response()->json($novos, 200);
			}else{
				return response()->json($docs, 401);
			}

		}catch(Exception $e){
			return response()->json($e->getMessage(), 403);
		}

	}

	private function validaNaoInserido($chave){
		$m = ManifestaDfe::
		where('empresa_id', $this->empresa_id)
		->where('chave', $chave)->first();
		if($m == null) return true;
		else return false;
	}

	public function filtroManifestos(Request $request){
		$tipo = $request->estado;
		$dataInicial = $request->data_inicio;
		$dataFinal = $request->data_final;

		$config = ConfigNota::first();

		if($config == null){
			session()->flash('color', 'red');
			session()->flash('message', 'Configure o Emitente');
			return redirect('configNF');
		}

		$docs = ManifestaDfe::orderBy('id', 'desc')->get();

		$arrayDocs = [];

		if($dataInicial){
			foreach($docs as $d){
				$dIni = str_replace("/", "-", $dataInicial);
				$dFim = str_replace("/", "-", $dataFinal);

				$dIni = \Carbon\Carbon::parse($dIni)->format('Y-m-d');
				$dFim = \Carbon\Carbon::parse($dFim)->format('Y-m-d');
				$data_dfe = \Carbon\Carbon::parse($d->data_emissao)->format('Y-m-d');
				if($tipo != '9'){
					if(strtotime($data_dfe) >= strtotime($dIni) && strtotime($data_dfe) <= strtotime($dFim)){
						if($d->tipo == $tipo){
							array_push($arrayDocs, $d);
						}
					}
				}else{
					if(strtotime($data_dfe) >= strtotime($dIni) && strtotime($data_dfe) <= strtotime($dFim)){
						array_push($arrayDocs, $d);
					}
				}
			}
		}else{
			foreach($docs as $d){
				$dIni = str_replace("/", "-", $dataInicial);
				$dFim = str_replace("/", "-", $dataFinal);

				$dIni = \Carbon\Carbon::parse($dIni)->format('Y-m-d');
				$dFim = \Carbon\Carbon::parse($dFim)->format('Y-m-d');
				$data_dfe = \Carbon\Carbon::parse($d->data_emissao)->format('Y-m-d');
				if($tipo != '9'){
					if($d->tipo == $tipo){
						array_push($arrayDocs, $d);
					}
				}else{
					array_push($arrayDocs, $d);

				}
			}

		}

		return response()->json($arrayDocs, 200);

	}

	public function renderizarDanfe($id){
		try{
			$dfe = ManifestaDfe::find($id);
			$config = ConfigNota::where('empresa_id', $dfe->empresa_id)->first();

			$cnpj = str_replace(".", "", $config->cnpj);
			$cnpj = str_replace("/", "", $cnpj);
			$cnpj = str_replace("-", "", $cnpj);
			$cnpj = str_replace(" ", "", $cnpj);

			$dfe_service = new DFeService([
				"atualizacao" => date('Y-m-d h:i:s'),
				"tpAmb" => 1,
				"razaosocial" => $config->razao_social,
				"siglaUF" => $config->UF,
				"cnpj" => $cnpj,
				"schemes" => "PL_009_V4",
				"versao" => "4.00",
				"tokenIBPT" => "AAAAAAA",
				"CSC" => $config->csc,
				"CSCid" => $config->csc_id
			], 55, $config->empresa_id);
			$response = $dfe_service->download($dfe->chave);

			$stz = new Standardize($response);


			$std = $stz->toStd();
			if ($std->cStat != 138) {
				return response()->json("Documento não retornado. [$std->cStat] $std->xMotivo" . ", aguarde alguns instantes e atualize a pagina!", 403);
			}    
			$zip = $std->loteDistDFeInt->docZip;
			$xml = gzdecode(base64_decode($zip));
			
			$danfe = new Danfe($xml);
			// $danfe->monta();
			$pdf = $danfe->render();
			header('Content-Type: application/pdf');
			return response($pdf)
			->header('Content-Type', 'application/pdf');


		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}

	}

	public function find($id){
		try{
			$dfe = ManifestaDfe::find($id);

			$dfe = ManifestaDfe::find($id);
			$config = ConfigNota::where('empresa_id', $dfe->empresa_id)->first();

			$cnpj = str_replace(".", "", $config->cnpj);
			$cnpj = str_replace("/", "", $cnpj);
			$cnpj = str_replace("-", "", $cnpj);
			$cnpj = str_replace(" ", "", $cnpj);

			$dfe_service = new DFeService([
				"atualizacao" => date('Y-m-d h:i:s'),
				"tpAmb" => 1,
				"razaosocial" => $config->razao_social,
				"siglaUF" => $config->UF,
				"cnpj" => $cnpj,
				"schemes" => "PL_009_V4",
				"versao" => "4.00",
				"tokenIBPT" => "AAAAAAA",
				"CSC" => $config->csc,
				"CSCid" => $config->csc_id
			], 55, $config->empresa_id);
			$response = $dfe_service->download($dfe->chave);

			$stz = new Standardize($response);

			$std = $stz->toStd();
			if ($std->cStat != 138) {
				return response()->json("Documento não retornado. [$std->cStat] $std->xMotivo" . ", aguarde alguns instantes e atualize a pagina!", 403);
			}    
			$zip = $std->loteDistDFeInt->docZip;
			$xml = gzdecode(base64_decode($zip));
			$nfe = simplexml_load_string($xml);

			$itens = $this->getItensDaNFe($nfe);
			$infos = $this->getInfosDaNFe($nfe);
			$fatura = $this->getFaturaDaNFe($nfe);

			$rs = [
				'itens' => $itens,
				'infos' => $infos,
				'fatura' => $fatura
			];
			return response()->json($rs, 200);

		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	private function getItensDaNFe($xml){
		$itens = [];
		foreach($xml->NFe->infNFe->det as $item) {

			$produto = Produto::verificaCadastrado($item->prod->cEAN,
				$item->prod->xProd, $item->prod->cProd);

			$produtoNovo = !$produto ? true : false;

			$tp = null;
			if($produto != null){
				$tp = ItemDfe::
				where('produto_id', $produto->id)
				->where('numero_nfe', $xml->NFe->infNFe->ide->nNF)
				->first();
			}

			$item = [
				'codigo' => $item->prod->cProd,
				'xProd' => $item->prod->xProd,
				'NCM' => $item->prod->NCM,
				'CFOP' => $item->prod->CFOP,
				'uCom' => $item->prod->uCom,
				'vUnCom' => $item->prod->vUnCom,
				'qCom' => $item->prod->qCom,
				'codBarras' => $item->prod->cEAN,
				'produtoNovo' => $produtoNovo,
				'produtoSetadoEstoque' => $tp != null ? true : false,
				'produtoId' => $produtoNovo ? '0' : $produto->id,
				'conversao_unitaria' => $produtoNovo ? '' : $produto->conversao_unitaria
			];
			array_push($itens, $item);
		}
		return $itens;
	}

	private function getInfosDaNFe($xml){
		$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
		$vFrete = number_format((double) $xml->NFe->infNFe->total->ICMSTot->vFrete, 
			2, ",", ".");
		$vDesc = number_format((double) $xml->NFe->infNFe->total->ICMSTot->vDesc, 2, ",", ".");
		return [
			'chave' => $chave,
			'vProd' => $xml->NFe->infNFe->total->ICMSTot->vProd,
			'indPag' => $xml->NFe->infNFe->ide->indPag,
			'nNf' => $xml->NFe->infNFe->ide->nNF,
			'vFrete' => $vFrete,
			'vDesc' => $vDesc
		];
	}

	private function getFaturaDaNFe($xml){
		if (!empty($xml->NFe->infNFe->cobr->dup))
		{	
			$fatura = [];
			$cont = 1;
			foreach($xml->NFe->infNFe->cobr->dup as $dup) {
				$titulo = $dup->nDup;
				$vencimento = $dup->dVenc;
				$vencimento = explode('-', $vencimento);
				$vencimento = $vencimento[2]."/".$vencimento[1]."/".$vencimento[0];
				$vlr_parcela = number_format((double) $dup->vDup, 2, ",", ".");	

				$parcela = [
					'numero' => $titulo,
					'vencimento' => $vencimento,
					'valor_parcela' => $vlr_parcela,
					'referencia' => $xml->NFe->infNFe->ide->nNF . "/" . $cont
				];
				array_push($fatura, $parcela);
				$cont++;
			}
			return $fatura;
		}
		return [];
	}


}