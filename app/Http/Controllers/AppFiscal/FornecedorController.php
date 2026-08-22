<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\Fornecedor;
use App\Models\Cidade;
use App\Rules\ValidaDocumento;

class FornecedorController extends Controller
{
	public function fornecedores(Request $request){
		$fornecedores = Fornecedor::
		where('empresa_id', $request->empresa_id)
		->get();
		foreach($fornecedores as $c){
			$c->cidade;
		}
		return response()->json($fornecedores, 200);
	}

	public function salvar(Request $request){
		
		if($request->id > 0){
			$fornecedor = Fornecedor::find($request->id);
			$fornecedor->razao_social = $request->razao_social;
			$fornecedor->nome_fantasia = $request->nome_fantasia;
			$fornecedor->bairro = $request->bairro;
			$fornecedor->numero = $request->numero;
			$fornecedor->rua = $request->logradouro;
			$fornecedor->cpf_cnpj = $request->cpf_cnpj;
			$fornecedor->telefone = $request->telefone;
			$fornecedor->celular = $request->celular;
			$fornecedor->email = $request->email;
			$fornecedor->cep = $request->cep;
			$fornecedor->ie_rg = $request->ie_rg;
			$fornecedor->cidade_id = $request->cidade;
			$res = $fornecedor->save();
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
				'email' => $request->email,
				'cep' => $request->cep,
				'ie_rg' => $request->ie_rg,
				'cidade_id' => $request->cidade,
				'empresa_id' => $request->empresa_id
			];
			$res = Fornecedor::create($data);
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
		->get();
		$arrTemp = [];
		foreach($ufs as $u){
			array_push($arrTemp, $u->uf);
		}
		return response()->json($arrTemp, 200);
	}

	public function delete(Request $request){
		$fornecedor = Fornecedor::find($request->id);
		$delete = $fornecedor->delete();
		return response()->json($delete, 200);
	}
}