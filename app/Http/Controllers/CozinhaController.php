<?php

namespace App\Http\Controllers;

use App\Models\TelaPedido;
use Illuminate\Http\Request;

class CozinhaController extends Controller
{
    public function index($id = NULL)
    {
        $tela = 'Todos';
        if ($id != null) {
            $tela = TelaPedido::find($id)->nome;
        }
        return view('controle_cozinha.index', compact('id', 'tela'));
    }

    public function selecionar(){
        $telas = TelaPedido::
        where('empresa_id', request()->empresa_id)
        ->get();
        if(sizeof($telas) > 0){
            return view('controle_cozinha.selecionar', compact('telas'));
        }else{
            return redirect()->route('controleCozinha.controle');
        }
    }
}
