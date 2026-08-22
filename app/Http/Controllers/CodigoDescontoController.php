<?php

namespace App\Http\Controllers;

use App\Models\ClienteDelivery;
use App\Models\CodigoDesconto;
use Illuminate\Http\Request;

class CodigoDescontoController extends Controller
{
    public function index()
    {
        $data = CodigoDesconto::where('empresa_id', request()->empresa_id)->get();
        return view('codigo_desconto.index', compact('data'));
    }

    public function create()
    {
        $clientes = ClienteDelivery::where('empresa_id', request()->empresa_id)->get();
        return view('codigo_desconto.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'valor' => __convert_value_bd($request->valor),
                'valor_minimo_pedido' => __convert_value_bd($request->valor_minimo_pedido),
                'expiracao' => $request->expiracao ?? 0,
                'cliente_id' => $request->cliente
            ]);
            CodigoDesconto::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('codigoDesconto.index');
    }

    public function edit($id)
    {
        $item = CodigoDesconto::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $clientes = ClienteDelivery::where('empresa_id', request()->empresa_id)->get();
        return view('codigo_desconto.edit', compact('item', 'clientes'));
    }

    public function update(Request $request, $id)
    {
        $item = CodigoDesconto::findOrFail($id);
        try {
            $request->merge([
                'valor' => __convert_value_bd($request->valor),
                'valor_minimo_pedido' => __convert_value_bd($request->valor_minimo_pedido),
                'expiracao' => $request->expiracao ?? 0,
                'cliente_id' => $request->cliente

            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('codigoDesconto.index');
    }

    public function destroy($id)
    {
        $item = CodigoDesconto::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }   
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('codigoDesconto.index');
    }
}
