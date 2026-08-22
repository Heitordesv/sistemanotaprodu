<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Funcionario;

class VendedorController extends Controller
{
    public function index(Request $request){
        $funcionarios = Funcionario::
        where('funcionarios.empresa_id', $request->empresa_id)
        ->select('funcionarios.*')
        ->join('usuarios', 'usuarios.id', '=', 'funcionarios.usuario_id')
        ->get();

        return response()->json($funcionarios, 200);
    }
}
