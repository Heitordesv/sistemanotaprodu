<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use Illuminate\Http\Request;

class CidadeController extends Controller
{
    public function index(Request $request)
    {
        $nome = $request->nome;
        $count = Cidade::count();
        $data = Cidade::when(!empty($request->nome), function ($q) use ($request) {
            return $q->where(function ($quer) use ($request) {
                return $quer->where('nome', 'LIKE', "%$request->nome%");
            });
        })
        ->paginate(env("PAGINACAO"));
        return view('cidades.index', compact('data', 'count'));
    }

    public function create()
    {
        return view('cidades.create');
    }

    public function edit($id)
    {
        $item = Cidade::findOrFail($id);
        return view('cidades.edit', compact('item'));
    }
}
