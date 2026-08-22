<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProdutoDelivery;
use App\Models\ComplementoDelivery;
use Illuminate\Http\Request;

class DeliveryComplementoController extends Controller
{
    public function index()
    {
        $data = ComplementoDelivery::where('empresa_id', request()->empresa_id)->get();
        return view('complemento_delivery.index', compact('data'));
    }

    public function create()
    {
        $categorias = CategoriaProdutoDelivery::where('empresa_id', request()->empresa_id)->get();
        return view('complemento_delivery.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {
            $request->merge([
                'tipo' => $request->tipo ?? ''
            ]);
            ComplementoDelivery::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('deliveryComplemento.index');
    }

    public function edit($id)
    {
        $item = ComplementoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $categorias = CategoriaProdutoDelivery::where('empresa_id', request()->empresa_id)->get();
        if ($item != null) {
            $item->categorias = $item->categoria ? json_decode($item->categoria) : [];
        }
        return view('complemento_delivery.edit', compact('item', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        $this->__validate($request);
        $item = ComplementoDelivery::findOrFail($id);
        try {
            $request->merge([
                'tipo' => $request->tipo ?? ''
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('deliveryComplemento.index');
    }

    private function __validate(Request $request)
    {
        $rules = [
            'nome' => 'required',
            'valor' => 'required',
            'categoria' => 'required'
        ];
        $messages = [
            'nome.required' => 'Campo obrigatório',
            'valor.required' => 'Campo obrigatório',
            'categoria.required' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = ComplementoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try{
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        }catch(\Exception $e){
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('deliveryComplemento.index');
    }
}
