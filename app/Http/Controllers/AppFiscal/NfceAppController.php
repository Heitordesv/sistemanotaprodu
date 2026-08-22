<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\ConfigNota;
use App\Models\VendaCaixa;
use App\Services\NFCeService;
use NFePHP\DA\NFe\Danfce;

class NfceAppController extends Controller
{
	public function transmitir(Request $request){
		$vendaId = $request->venda_id;

		$venda = VendaCaixa::find($vendaId);

		$config = ConfigNota::
		where('empresa_id', $venda->empresa_id)
		->first();

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
		], $config->empresa_id);

		if($venda->estado == 'REJEITADO' || $venda->estado == 'DISPONIVEL'){
			header('Content-type: text/html; charset=UTF-8');

			$nfce = $nfe_service->gerarNFCe($vendaId);
			if(!isset($nfce['erros_xml'])){

				$public = env('SERVIDOR_WEB') ? 'public/' : '';
				$signed = $nfe_service->sign($nfce['xml']);
			// file_put_contents($public.'xml_nfce/'.$venda->id.'.xml',$signed);
				$resultado = $nfe_service->transmitirNfce($signed, $nfce['chave']);

				if(substr($resultado, 0, 4) != 'Erro'){
					$venda->chave = $nfce['chave'];
					$venda->path_xml = $nfce['chave'] . '.xml';
					$venda->estado = 'APROVADO';

					$venda->NFcNumero = $nfce['nNf'];
					$venda->save();
					$this->imprimir($venda->id);
					$res = [
						'protocolo' => $resultado,
						'url' => env("PATH_URL") . '/' . $public.'pdf/DANFCE.pdf'
					];
					return response()->json($res, 200);

				}else{
					$venda->estado = 'REJEITADO';
					$venda->save();
				}
				return response()->json($resultado, 401);
			}else{
				return response()->json($nfce['erros_xml'][0], 401);
			}

		}else{
			return response()->json("erro", 403);
		}
	}

	public function imprimir($id){
		$venda = VendaCaixa::find($id);

		$public = env('SERVIDOR_WEB') ? 'public/' : '';

		$xml = file_get_contents($public.'xml_nfce/'.$venda->chave.'.xml');
		// $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'imgs/logo.jpg'));
		// $docxml = FilesFolders::readFile($xml);

		$config = ConfigNota::
		where('empresa_id', $venda->empresa_id)
		->first();
		
		if($config->logo){
			$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents($public.'logos/' . $config->logo));
		}else{
			$logo = null;
		}

		try {
			$danfce = new Danfce($xml, $venda);
			// $danfce->monta($logo);
			$pdf = $danfce->render($logo);

			return response($pdf)
			->header('Content-Type', 'application/pdf');
		} catch (InvalidArgumentException $e) {
			return response()->json("erro", 401);
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  
	}

	public function consultar(Request $request){
		$venda = VendaCaixa::find($request->id);
		$config = ConfigNota::
		where('empresa_id', $venda->empresa_id)
		->first();

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
		], $config->empresa_id);

		$c = $nfe_service->consultarNFCe($venda);
		return response()->json($c, 200);

	}

	public function cancelar(Request $request){
		$venda = VendaCaixa::
		where('id', $request->id)
		->first();
		// $config = ConfigNota::first();
		$config = ConfigNota::
		where('empresa_id', $venda->empresa_id)
		->first();

		if(strlen($request->justificativa) < 15){
			return response()->json('Informe um motivo com pelo menos 15 caracteres!', 401);
		}

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
		], $config->empresa_id);

		$nfe = $nfe_service->cancelarNFCe($request->id, $request->justificativa);
		if(!isset($nfe['erro'])){

			$venda->estado = 'CANCELADO';
			$venda->valor_total = 0;
			$venda->save();

			return response()->json($nfe, 200);

		}else{
			return response()->json($nfe['mensagem'], 401);
		}
	}

	public function getXml($id){
		$venda = VendaCaixa::find($id);

		$public = env('SERVIDOR_WEB') ? 'public/' : '';
		try {

			$xml = file_get_contents($public.'xml_nfce/'.$venda->chave.'.xml');
			
			return response($xml)
			->header('Content-Type', 'application/xml');

		} catch (InvalidArgumentException $e) {
			return response()->json("erro", 401);
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  
	}
}
