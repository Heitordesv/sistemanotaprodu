<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ItemPedido;
use Illuminate\Http\Request;

class ControleCozinhaController extends Controller
{
    public function buscar(Request $request)
    {
        $tela = $request->tela;
        $itens = ItemPedido::select('item_pedidos.*')
            ->join('pedidos', 'pedidos.id', '=', 'item_pedidos.pedido_id')
            ->join('produtos', 'produtos.id', '=', 'item_pedidos.produto_id')
            ->where('item_pedidos.status', false)
            ->where('produtos.envia_controle_pedidos', true)
            ->where('pedidos.empresa_id', $request->empresa_id)
            ->orderBy('item_pedidos.created_at', 'desc')
            ->when($tela != "", function ($q) use ($tela) {
				return $q->where('produtos.tela_pedido_id', $tela);
			})
            ->get();

        // return response()->json($itens, 200);
        return view('controle_cozinha.itens', compact('itens'));
    }
}
