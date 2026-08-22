<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function index(Request $request){
    	$clientes = Cliente::
    	where('empresa_id', $request->empresa_id)
    	->get();

    	return response()->json($clientes, 200);
    }
}
