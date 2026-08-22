<?php

namespace App\Http\Controllers;

use App\Models\CidadeDelivery;
use Illuminate\Http\Request;

class CidadeDeliveryController extends Controller
{
    public function index()
    {
        $data = CidadeDelivery::all();
        return view('cidade_delivery.index', compact('data'));
    }

    public function create()
    {
        return view('cidade_delivery.create');
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {
            CidadeDelivery::create($request->all());
            session()->flash('flash_sucesso', 'Cadastro com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('cidadeDelivery.index');
    }

    public function edit($id)
    {
        $item = CidadeDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('cidade_delivery.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->__validate($request);
        $item = CidadeDelivery::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('cidadeDelivery.index');
    }

    public function destroy($id)
    {
        $item = CidadeDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('cidadeDelivery.index');
    }

    private function __validate(Request $request)
    {
        $rules = [
            'nome' => 'required',
            'cep' => 'required',
            'uf' => 'required'
        ];
        $messages = [
            'nome.required' => 'Campo obrigatório',
            'cep.required' => 'Campo obrigatório',
            'uf.required' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }
}
