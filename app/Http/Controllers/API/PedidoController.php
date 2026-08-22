<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ComplementoDelivery;
use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function findAdicional($id)
    {
        $item = ComplementoDelivery::where('id', $id)
        ->first();
        return response()->json($item, 200);
    }

    public function comanda($id)
    {
        $item = Pedido::where('comanda', $id)
        ->where('desativado', 0)
        ->with('itens')
        ->first();

        return response()->json($item, 200);
    }

    public function comandaHtml($id)
    {
        $item = Pedido::where('comanda', $id)
        ->with('itens')
        ->where('desativado', 0)
        ->first();
        if($item == null){
            return response()->json("", 200);
        }

        $itens = $item->itens;

        return view('pedidos.row_frontBox', compact('itens'));
    }
}
