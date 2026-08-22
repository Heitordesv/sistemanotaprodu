<?php

namespace App\Utils;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class Util
{
	public function validateEntry($data, $business_id){
		$result = null;

		foreach($data as $tbl){
			$item = DB::table($tbl)->where('empresa_id', $business_id)->first();
			if($item == null){
				$result = $this->valid($tbl);
			}
		}

		return $result;
	}

	private function valid($tbl){
		$dataValid = [
			'produtos' => [
				'route' => route('produtos.create'),
				'message' => 'Cadastre ao menos um produto!'
			],
			'clientes' => [
				'route' => route('clientes.create'),
				'message' => 'Cadastre ao menos um cliente!'
			],
			'categorias' => [
				'route' => route('categorias.create'),
				'message' => 'Cadastre ao menos uma categoria!'
			],
			'fornecedors' => [
				'route' => route('fornecedores.create'),
				'message' => 'Cadastre ao menos um fornecedor!'
			],
			'tributacaos' => [
				'route' => route('tributos.index'),
				'message' => 'Defina a tributação!'
			],
			'natureza_operacaos' => [
				'route' => route('naturezas.create'),
				'message' => 'Cadastre ao menos uma natureza de operação!'
			],
            'tributacaos' => [
				'route' => route('tributos.index'),
				'message' => 'Cadastre ao menos um ncm padrão!'
			],
			'categoria_produto_deliveries' => [
				'route' => route('categoriaDelivery.index'),
				'message' => 'Cadastre ao menos uma categoria delivery!'
			],
			'categoria_produto_ecommerces' => [
				'route' => route('categoriaEcommerce.index'),
				'message' => 'Cadastre ao menos uma categoria ecommerce!'
			],

		];

		if(isset($dataValid[$tbl])){
			return $dataValid[$tbl];
		}
		return [];
	}
}
