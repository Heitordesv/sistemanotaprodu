<?php

namespace App\Http\Controllers\AppFiscal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario;
use App\Models\ItemInventario;

class InventarioController extends Controller
{
	public function index(Request $request){
		$inventarios = Inventario::
		where('empresa_id', $request->empresa_id)
		->where('status', 1)
		->orderBy('id', 'desc')
		->get();
		return response()->json($inventarios, 200);
	}

	public function salvarItem(Request $request){
		try{
			$res = ItemInventario::create(
				[
					'inventario_id' => $request->inventario_id,
					'produto_id' => $request->produto_id,
					'quantidade' => $request->quantidade,
					'observacao' => $request->observacao ?? '',
					'estado' => $request->estado,
					'usuario_id' => $request->usuario_id					
				]
			);
			return response()->json($res, 200);

		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	public function getItens($id){
		try{
			$itens = ItemInventario::
			where('inventario_id', $id)
			->orderBy('id', 'desc')
			->get();
			foreach($itens as $i){
				$i->produto;
				$i->usuario;
			}
			return response()->json($itens, 200);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	public function removeItem(Request $request){
		try{
			$item = ItemInventario::find($request->id);
			if($item != null){
				$item->delete();
			}
			return response()->json("ok", 200);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	public function itemJaIncluso(Request $request){
		$produtoId = $request->produto_id;
		$inventarioId = $request->inventario_id;

		$item = ItemInventario::
		where('produto_id', $produtoId)
		->where('inventario_id', $inventarioId)
		->first();

		if($item == null) return response()->json('ok', 200);
		return response()->json('ja incluso', 403);
	}

	public function estados(Request $request){
		$estados = ItemInventario::estados();
		return response()->json($estados, 200);
	}
}
