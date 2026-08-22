<?php

namespace App\Http\Controllers;

use App\Http\Middleware\LimiteNFCe;
use App\Models\ConfigCaixa;
use App\Models\ConfigNota;
use App\Models\VendaCaixa;
use App\Models\Usuario;
use App\Services\NFCeService;
use Illuminate\Http\Request;
use NFePHP\DA\NFe\Cupom;
use NFePHP\DA\NFe\CupomNaoFiscal;
use NFePHP\DA\NFe\Danfce;

class NfceController extends Controller
{
	public function __construct()
	{
        $this->middleware(LimiteNFCe::class)->only('create');
	}

	public function inutilizar(Request $request)
	{
		try {
			$config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
			$cnpj = str_replace(".", "", $config->cnpj);
			$cnpj = str_replace("/", "", $cnpj);
			$cnpj = str_replace("-", "", $cnpj);
			$cnpj = str_replace(" ", "", $cnpj);
			$nfce_service = new NFCeService([
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
			]);
			// echo json_encode($request->justificativa);
			$result = $nfce_service->inutilizar(
				$config,
				$request->nInicio,
				$request->nFinal,
				$request->justificativa,
				$request->nSerie
			);
			echo json_encode($result);
		} catch (\Exception $e) {
			return response()->json($e->getMessage(), 401);
		}
	}

	public function xmlTemp($id)
	{

		$venda = VendaCaixa::findOrFail($id);

		$config = ConfigNota::where('empresa_id', $venda->empresa_id)
		->first();

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

		$nfce_service = new NFCeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->cidade->uf,
			"cnpj" => $cnpj,
			"schemes" => "PL_009_V4",
			"versao" => "4.00",
			"tokenIBPT" => "AAAAAAA",
			"CSC" => $config->csc,
			"CSCid" => $config->csc_id
		], $config);
		$nfe = $nfce_service->gerarNFCe($venda);

		if (!isset($nfe['erros_xml'])) {
			$xml = $nfe['xml'];
			return response($xml)
			->header('Content-Type', 'application/xml');
		} else {
			print_r($nfe['erros_xml']);
		}
	}

	public function imprimir($id)
	{
		$venda = VendaCaixa::findOrFail($id);
		if (valida_objeto($venda)) {
			$path = 'xml_nfce/';
			if($venda->contigencia){
				$path = 'xml_nfce_contigencia/';
			}

			if (file_exists(public_path($path) . $venda->chave . '.xml')) {
				try {
					$xml = file_get_contents(public_path($path) . $venda->chave . '.xml');

					$config = ConfigNota::where('empresa_id', $venda->empresa_id)
					->first();

					if ($venda->tipo_pagamento == 17) {
						// $this->gerarPix($config, $venda);
					}

					if ($config->logo) {
						$logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('uploads/configEmitente/') . $config->logo));
					} else {
						$logo = null;
					}

					$usuario = Usuario::find(get_id_user());
					$danfce = new Danfce($xml, $venda);
					if ($usuario->config) {
						$danfce->setPaperWidth($usuario->config->impressora_modelo);
					}
					$pdf = $danfce->render($logo);


					return response($pdf)
					->header('Content-Type', 'application/pdf');
				} catch (\Exception $e) {
					echo $e->getMessage();
				}
			} else {
				echo "Arquivo XML não encontrado!!";
			}
		} else {
			return redirect('/403');
		}
	}

	public function baixarXml($id)
	{
		$item = VendaCaixa::findOrFail($id);
		if (!__valida_objeto($item)) {
			abort(403);
		}
		try {
			return response()->download(public_path('xml_nfce/') . $item->chave . '.xml');
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function show($id)
	{
		$item = VendaCaixa::findOrFail($id);
		if (valida_objeto($item)) {

			$value = session('user_logged');

			return view('frontBox.show')
			->with('item', $item)
			->with('adm', $value['adm'])
			->with('title', 'Detalhes da venda');
		} else {
			return response()->json("Não permitido!", 403);
		}
	}

	public function estadoFiscal($id)
	{
		$item = VendaCaixa::findOrFail($id);

		if (valida_objeto($item)) {

			$value = session('user_logged');

			return view('frontBox.alterar_estado_fiscal')
			->with('item', $item)
			->with('adm', $value['adm'])
			->with('title', 'Alterar estado');
		} else {
			return response()->json("Não permitido!", 403);
		}
	}

	public function updateState(Request $request, $id)
	{
		$venda = VendaCaixa::findOrFail($id);
		try {
			$venda->estado_emissao = $request->estado_emissao;
			if ($request->hasFile('xml')) {
				$xml = simplexml_load_file($request->xml);
				$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
				$file = $request->xml;
				$file->move(public_path('xml_nfce'), $chave . '.xml');
				$venda->chave = $chave;
                // $venda->data_emissao = date('Y-m-d H:i:s');
				$venda->numero_nfce = (int)$xml->NFe->infNFe->ide->nNF;
			}
			$venda->save();
			session()->flash("flash_sucesso", "Estado alterado");
		} catch (\Exception $e) {
			session()->flash("flash_erro", "Erro: " . $e->getMessage());
			__saveLogError($e, request()->empresa_id);
		}
		return redirect()->back();
	}

	public function imprimirNaoFiscal($id)
	{
		$venda = VendaCaixa::
		where('id', $id)
		->first();

		if(valida_objeto($venda)){

			$config = ConfigNota::
			where('empresa_id', request()->empresa_id)
			->first();

			if($config->logo){
				$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(public_path('uploads/configEmitente/') . $config->logo));
			}else{
				$logo = null;
			}
			if($venda->tipo_pagamento == 17){
				// $this->gerarPix($config, $venda);
			}
			$usuario = Usuario::find(get_id_user());

			$configCaixa = ConfigCaixa::where('usuario_id', $usuario->id)->first();

			if($configCaixa != null && $configCaixa->cupom_modelo == 2){
				$public = env('SERVIDOR_WEB') ? 'public/' : '';
				$pathLogo = $public.'logos/' . $config->logo;
				$cupom = new Cupom($venda, $pathLogo, $config, $usuario->config ? $usuario->config->impressora_modelo : 80);
				$cupom->monta();
				$pdf = $cupom->render();
			}else{
				$cupom = new CupomNaoFiscal($venda, $config);

				if($usuario->config){
					$cupom->setPaperWidth($usuario->config->impressora_modelo);
				}
				$pdf = $cupom->render($logo);
			}

			return response($pdf)
			->header('Content-Type', 'application/pdf');
		}else{
			return redirect('/403');
		}
	}
}
