<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfigNota;
use App\Models\Tributacao;
use App\Models\VendaCaixa;
use App\Models\Certificado;

class ConfigController extends Controller
{
	public function index(Request $request){
		$config = ConfigNota::
		where('empresa_id', $request->empresa_id)
		->first();

		$tributacao = Tributacao::
		where('empresa_id', $request->empresa_id)
		->first();

		$certificado = Certificado::
		where('empresa_id', $request->empresa_id)
		->first();

		$objeto = [];

		$ultimoNumeroNfce = VendaCaixa::lastNFCe($request->empresa_id);
		
		if($config != null && $tributacao != null){
			$objeto = [
				'numeroSerieNfce' => $config->numero_serie_nfce,
				'ultimoNumeroNfce' => $ultimoNumeroNfce,
				'razao_social' => $config->razao_social,
				'ambiente' => $config->ambiente,
				'regime' => $tributacao->regime,
				'naturezaOperacao' => $config->natureza ? $config->natureza->natureza : '',
				'nome_fantasia' => $config->nome_fantasia,
				'cnpj' => $config->cnpj,
				'ie' => $config->ie,
				'csc' => $config->csc,
				'csc_id' => $config->csc_id,
				'logradouro' => $config->logradouro,
				'numero' => $config->numero,
				'bairro' => $config->bairro,
				'municipio' => $config->municipio,
				'codMun' => $config->codMun,
				'cep' => $config->cep,
				'UF' => $config->UF,
				'fone' => $config->fone,
				'senhaCertificado' => $certificado != null ? $certificado->senha : '',
				'complemento' => $config->complemento,
			];
		}

		return response()->json($objeto, 200);
	}

	public function teste(Request $request){
		if($request->chave == env("KEY_APP")){
			return response()->json("ok", 200);
		}else{
			return response()->json("erro", 401);
		}	
	}

}
