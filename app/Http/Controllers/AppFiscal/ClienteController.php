<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Cidade;
use App\Models\ConfigNota;
use App\Models\Certificado;
use App\Services\NFService;

class ClienteController extends Controller
{
	public function clientes(Request $request){

		$clientes = Cliente::
		where('empresa_id', $request->empresa_id)
		->get();
		foreach($clientes as $c){
			$c->cidade;
		}
		return response()->json($clientes, 200);
	}

	public function salvar(Request $request){
		
		if($request->id > 0){
			$cliente = Cliente::find($request->id);
			$cliente->razao_social = $request->razao_social;
			$cliente->nome_fantasia = $request->nome_fantasia;
			$cliente->bairro = $request->bairro;
			$cliente->numero = $request->numero;
			$cliente->rua = $request->logradouro;
			$cliente->cpf_cnpj = $request->cpf_cnpj;
			$cliente->telefone = $request->telefone;
			$cliente->celular = $request->celular;
			$cliente->email = $request->email ?? '';
			$cliente->cep = $request->cep;
			$cliente->ie_rg = $request->ie_rg;
			$cliente->consumidor_final = $request->consumidor_final;
			$cliente->limite_venda = $request->limite_venda;
			$cliente->cidade_id = $request->cidade;
			$cliente->contribuinte = $request->contribuinte;
			$res = $cliente->save();
		}else{
			$data = [
				'razao_social' => $request->razao_social,
				'nome_fantasia' => $request->nome_fantasia,
				'bairro' => $request->bairro,
				'numero' => $request->numero,
				'rua' => $request->logradouro,
				'cpf_cnpj' => $request->cpf_cnpj,
				'telefone' => $request->telefone ?? '',
				'celular' => $request->celular ?? '',
				'email' => $request->email ?? '',
				'cep' => $request->cep,
				'ie_rg' => $request->ie_rg,
				'consumidor_final' => $request->consumidor_final,
				'limite_venda' => $request->limite_venda,
				'cidade_id' => $request->cidade,
				'contribuinte' => $request->contribuinte,
				'rua_cobranca' => '',
				'numero_cobranca' => '',
				'bairro_cobranca' => '',
				'cep_cobranca' => '',
				'cidade_cobranca_id' => NULL,
				'empresa_id' => $request->empresa_id
			];
			$res = Cliente::create($data);
		}

		
		return response()->json($res, 200);
	}

	public function cidades(){
		$cidades = Cidade::all();
		return response()->json($cidades, 200);
	}

	public function ufs(){
		$ufs = Cidade::
		selectRaw('distinct(uf) as uf')
		->orderBy('uf')
		->get();
		$arrTemp = [];
		foreach($ufs as $u){
			array_push($arrTemp, $u->uf);
		}
		return response()->json($arrTemp, 200);
	}

	public function delete(Request $request){
		$cliente = Cliente::find($request->id);
		$delete = $cliente->delete();
		return response()->json($delete, 200);
	}

	public function consultaCnpj(Request $request){

		$config = ConfigNota::
		where('empresa_id', $request->empresa_id)
		->first();

		$certificado = Certificado::
		where('empresa_id', $request->empresa_id)
		->first();

		if($certificado == null){
			return response()->json('É necessário configurar o certificado!', 401);
		}

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		
		try{
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
			$cnpj = $request->cnpj;
			$uf = $request->uf;
			$consulta = $nfe_service->consultaCadastro($cnpj, $uf);

			if($consulta['erro'] == 1){
				return response()->json($consulta['json'], 401);
			}
			return response()->json($consulta['json'], 200);

		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
		// return response()->json("chupa!!", 200); 
	}
}