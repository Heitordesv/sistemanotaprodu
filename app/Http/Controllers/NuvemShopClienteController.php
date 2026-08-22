<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class NuvemShopClienteController extends Controller
{
    public function index(Request $request){
        $data = Cliente::
        where('empresa_id', $request->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('razao_social', 'LIKE', "%$request->nome%");
        })
        ->where('nuvemshop_id', '!=', '')
        ->paginate(40);

        return view('nuvemshop_clientes/index', compact('data'));
    }
}
