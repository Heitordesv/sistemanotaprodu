<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class MarketingController extends Controller
{
    public function __construct()
    {
        // Se necessário, adicione middleware aqui
    }

    public function index(Request $request)
    {
        // Buscar clientes e selecionar apenas os campos necessários
        $data = Cliente::where('empresa_id', request()->empresa_id)
            ->when($request->razao_social, function ($query) use ($request) {
                return $query->where('razao_social', 'LIKE', "%{$request->razao_social}%");
            })
            ->when($request->cpf_cnpj, function ($query) use ($request) {
                return $query->where('cpf_cnpj', 'LIKE', "%{$request->cpf_cnpj}%");
            })
            ->select('razao_social', 'celular', 'data_aniversario')
            ->paginate(env("PAGINACAO"));
        
        // Passar os dados para a view
        return view('Marketing.index', compact('data'));
    }
}
