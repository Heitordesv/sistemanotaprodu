<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormaPagamentos;
use App\Models\ConfigNota;

class FormaPagamentosController extends Controller
{
    public function index(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return response()->json(['data' => []]);
        }

        $userId = $config->user_id;

        $query = FormaPagamentos::where('user_id', $userId);

        if ($request->has('nome') && $request->nome) {
            $query->where('f_pagamento', 'LIKE', '%' . $request->nome . '%');
        }

        $data = $query->paginate(env("PAGINACAO", 10));

        return view('cadastro_pagamentos.index', compact('data'));
    }

    public function create(Request $request)
    {
        return view('cadastro_pagamentos.create');
    }

    public function store(Request $request)
    {
        try {
            $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

            if (!$config) {
                return response()->json(['data' => []]);
            }

            $data = $request->validate([
                'f_pagamento' => 'required|string|max:100'
            ]);

            $data['user_id'] = $config->user_id;

            FormaPagamentos::create($data);

            session()->flash('flash_sucesso', 'Forma de pagamento cadastrada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao cadastrar: ' . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }

        return redirect()->route('cadastro_pagamentos.index');
    }

    public function edit($id, Request $request)
    {
        $item = FormaPagamentos::findOrFail($id);
        return view('cadastro_pagamentos.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        try {
            $item = FormaPagamentos::findOrFail($id);

            $data = $request->validate([
                'f_pagamento' => 'required|string|max:100'
            ]);

            $item->update($data);

            session()->flash('flash_sucesso', 'Forma de pagamento atualizada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao atualizar: ' . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }

        return redirect()->route('cadastro_pagamentos.index');
    }

    public function destroy($id, Request $request)
    {
        try {
            $item = FormaPagamentos::findOrFail($id);
            $item->delete();

            session()->flash('flash_sucesso', 'Forma de pagamento deletada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao deletar: ' . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }

        return redirect()->route('cadastro_pagamentos.index');
    }
}
