<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\ItemVenda;
use App\Models\Produto;
use App\Helpers\StockMove;
use App\Models\ListaPreco;
use App\Models\ContaReceber;
use App\Services\NFService;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\Daevento;
use Dompdf\Dompdf;
use App\Models\ConfigNota;

class NotaFiscalAppController extends Controller
{

	public function transmitir(Request $request){

		$venda = Venda::find($request->venda_id);

		// $config = ConfigNota::first();
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

		if($venda->estado == 'REJEITADO' || $venda->estado == 'DISPONIVEL'){
			header('Content-type: text/html; charset=UTF-8');

			$nfe = $nfe_service->gerarNFe($request->venda_id);

			if(!isset($nfe['erros_xml'])){
			// file_put_contents('xml/teste2.xml', $nfe['xml']);
			// return response()->json($nfe, 200);
				$signed = $nfe_service->sign($nfe['xml']);
				$resultado = $nfe_service->transmitir($signed, $nfe['chave']);

				if(substr($resultado, 0, 4) != 'Erro'){
					$venda->chave = $nfe['chave'];
					$venda->path_xml = $nfe['chave'] . '.xml';
					$venda->estado = 'APROVADO';

					$venda->NfNumero = $nfe['nNf'];
					$venda->save();
					return response()->json($resultado, 200);

				}else{
					$venda->estado = 'REJEITADO';
					$venda->save();
				//403 ja esta REJEITADO!!

					return response()->json($resultado, 401);

				}
			}else{
				return response()->json($nfe['erros_xml'][0], 401);

			}


		}else{
			//403 ja esta aprovado!!
			return response()->json("erro", 403);
		}

	}

	public function renderizarDanfe($id){
		$venda = Venda::find($id);
		// $config = ConfigNota::first();
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
		$xml = $nfe['xml'];

		$public = env('SERVIDOR_WEB') ? 'public/' : '';

		try {
			$danfe = new Danfe($xml);
			// $id = $danfe->monta();
			$pdf = $danfe->render();
			header('Content-Type: application/pdf');
			return response($pdf)
			->header('Content-Type', 'application/pdf');
			// file_put_contents($public.'pdf/DANFE.pdf',$pdf);
			// return response()->json($public.'pdf/DANFE.pdf', 200);
		} catch (InvalidArgumentException $e) {
			return response()->json("erro", 401);
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  
	}

	public function consultar(Request $request){
		// $config = ConfigNota::first();
		$venda = Venda::find($request->id);
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

		$c = $nfe_service->consultar($request->id);
		return response()->json($c, 200);

	}

	public function cancelar(Request $request){
		$venda = Venda::find($request->id);
		// $config = ConfigNota::first();
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

		$nfe = $nfe_service->cancelar($request->id, $request->justificativa);
		if(!isset($nfe['erro'])){

			$venda = Venda::
			where('id', $request->id)
			->first();
			$venda->estado = 'CANCELADO';
			$venda->valor_total = 0;
			$venda->save();

			// $this->removerDuplicadas($venda);
			return response()->json($nfe, 200);

		}else{
			return response()->json($nfe['data'], $nfe['status']);
		}
	}

	public function corrigir(Request $request){
		$venda = Venda::find($request->id);

		// $config = ConfigNota::first();
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

		try{
			$nfe = $nfe_service->cartaCorrecao($request->id, $request->justificativa);
			return response()->json($nfe, 200);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	public function imprimir($id){
		$venda = Venda::find($id);

		$public = env('SERVIDOR_WEB') ? 'public/' : '';

		$xml = file_get_contents($public.'xml_nfe/'.$venda->chave.'.xml');
		// $docxml = FilesFolders::readFile($xml);
		$config = ConfigNota::where('empresa_id', $venda->empresa_id)->first();

		if($config->logo){
			$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'logos/' . $config->logo));

		}else{
			$logo = null;
		}
		try {
			$danfe = new Danfe($xml);
			// $id = $danfe->monta($logo);
			$pdf = $danfe->render($logo);
			header('Content-Type: application/pdf');
			// file_put_contents($public.'pdf/DANFE.pdf',$pdf);
			// return response()->json($public.'pdf/DANFE.pdf', 200);
			return response($pdf)
			->header('Content-Type', 'application/pdf');

		} catch (InvalidArgumentException $e) {
			return response()->json("erro", 401);
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  
	}


	public function imprimirCorrecao($id){
		$venda = Venda::find($id);

		$config = ConfigNota::where('empresa_id', $venda->empresa_id)->first();

		$public = env('SERVIDOR_WEB') ? 'public/' : '';

		$xml = file_get_contents($public.'xml_nfe_correcao/'.$venda->chave.'.xml');
		if($config->logo){
			$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'logos/' . $config->logo));

		}else{
			$logo = null;
		}
		try {

			$dadosEmitente = $this->getEmitente($config);

			$daevento = new Daevento($xml, $dadosEmitente);
			$daevento->debugMode(true);
			$pdf = $daevento->render($logo);
			header('Content-Type: application/pdf');
			// file_put_contents($public.'pdf/DANFE.pdf',$pdf);
			// return response()->json($public.'pdf/DANFE.pdf', 200);
			return response($pdf)
			->header('Content-Type', 'application/pdf');

		} catch (InvalidArgumentException $e) {
			return response()->json("erro", 401);
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  
	}

	public function imprimirCancelada($id){
		$venda = Venda::find($id);

		$config = ConfigNota::where('empresa_id', $venda->empresa_id)->first();

		$public = env('SERVIDOR_WEB') ? 'public/' : '';

		$xml = file_get_contents($public.'xml_nfe_cancelada/'.$venda->chave.'.xml');
		if($config->logo){
			$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'logos/' . $config->logo));

		}else{
			$logo = null;
		}
		try {

			$dadosEmitente = $this->getEmitente($config);

			$daevento = new Daevento($xml, $dadosEmitente);
			$daevento->debugMode(true);
			$pdf = $daevento->render($logo);
			header('Content-Type: application/pdf');
			// file_put_contents($public.'pdf/DANFE.pdf',$pdf);
			// return response()->json($public.'pdf/DANFE.pdf', 200);
			return response($pdf)
			->header('Content-Type', 'application/pdf');

		} catch (InvalidArgumentException $e) {
			return response()->json("erro", 401);
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  
	}

	private function getEmitente($config){
		
		return [
			'razao' => $config->razao_social,
			'logradouro' => $config->logradouro,
			'numero' => $config->numero,
			'complemento' => '',
			'bairro' => $config->bairro,
			'CEP' => $config->cep,
			'municipio' => $config->municipio,
			'UF' => $config->UF,
			'telefone' => $config->telefone,
			'email' => ''
		];
	}

	public function getXml($id){
		$venda = Venda::find($id);

		$public = env('SERVIDOR_WEB') ? 'public/' : '';
		try {

			$xml = file_get_contents($public.'xml_nfe/'.$venda->chave.'.xml');
			
			return response($xml)
			->header('Content-Type', 'application/xml');

		} catch (InvalidArgumentException $e) {
			return response()->json("erro", 401);
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  
	}

	
}