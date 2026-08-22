<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\Produto;

class CategoriaController extends Controller
{
	public function all(Request $request){
		$categorias = Categoria::
		where('empresa_id', $request->empresa_id)
		->get();
		foreach($categorias as $c){
			$prods = Produto::where('categoria_id', $c->id)->get();
			$c->total_de_produtos = sizeof($prods);
		}
		return response()->json($categorias, 200);
	}

	public function isDelivery(){
		$deivery = env("DELIVERY");
		return response()->json($deivery, 200);
	}

	public function salvar(Request $request){
		
		if($request->id > 0){
			$categoria = Categoria::find($request->id);
			$categoria->nome = $request->nome;
			$res = $categoria->save();
		}else{
			$data = [
				'nome' => $request->nome,
				'empresa_id' => $request->empresa_id
			];
			$res = Categoria::create($data);
		}

		return response()->json($res, 200);
	}

	public function delete(Request $request){
		$categoria = Categoria::find($request->id);
		$delete = $categoria->delete();
		return response()->json($delete, 200);
	}
}