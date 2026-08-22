<?php

namespace App\Http\Controllers;

use App\Models\Pesquisa;
use Illuminate\Http\Request;

class PesquisaController extends Controller
{
    public function index()
    {
        $data = Pesquisa::orderBy('id', 'desc')->get();
        return view('pesquisa.index', compact('data'));
    }

    public function create()
    {
        return view('pesquisa.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'status' => $request->status ?? 0,
                'maximo_acessos' => $request->maximo_acessos ?? 0,
            ]);
            Pesquisa::create($request->all());
            session()->flash('flash_sucesso', 'Cadastro com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('pesquisa.index');
    }

    public function edit($id)
    {
        $item = Pesquisa::findOrFail($id);
        return view('pesquisa.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        try {
            $item = Pesquisa::findOrFail($id);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Pesquisa atualizada!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo de errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('pesquisa.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'titulo' => 'required|max:50',
            'texto' => 'required',
        ];
        $messages = [
            'titulo.required' => 'Campo obrigatório.',
            'titulo.max' => '50 caracteres maximos permitidos.',
            'texto.required' => 'Campo obrigatório.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = Pesquisa::findOrFail($id);
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('pesquisa.index');
    }
}
