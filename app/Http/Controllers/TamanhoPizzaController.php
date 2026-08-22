<?php

namespace App\Http\Controllers;

use App\Models\TamanhoPizza;
use Illuminate\Http\Request;

class TamanhoPizzaController extends Controller
{
    public function index(Request $request)
    {
        $data = TamanhoPizza::where('empresa_id', $request->empresa_id)->get();
        return view('tamanho_pizza.index', compact('data'));
    }

    public function create()
    {
        return view('tamanho_pizza.create');
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {
            TamanhoPizza::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('tamanhosPizza.index');
    }

    public function edit($id)
    {
        $item = TamanhoPizza::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('tamanho_pizza.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->__validate($request);
        $item = TamanhoPizza::FindOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('tamanhosPizza.index');
    }

    private function __validate(Request $request)
    {
        $rules = [
            'nome' => 'required',
            'pedacos' => 'required',
            'maximo_sabores' => 'required'
        ];
        $messages = [
            'nome.required' => 'Campo obrigatório',
            'pedacos.required' => 'Campo obrigatório',
            'maximo_sabores' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = TamanhoPizza::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('tamanhosPizza.index');
    }
}
