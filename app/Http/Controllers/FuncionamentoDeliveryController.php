<?php

namespace App\Http\Controllers;

use App\Models\FuncionamentoDelivery;
use Illuminate\Http\Request;

class FuncionamentoDeliveryController extends Controller
{
    public function index()
    {
        $data = FuncionamentoDelivery::where('empresa_id', request()->empresa_id)->get();
        return view('funcionamento_delivery.create', compact('data'));
    }

    public function store(Request $request)
    {
        $item = FuncionamentoDelivery::where('empresa_id', request()->empresa_id)
            ->where('dia', $request->dia)->first();
        if ($item == null) {
            try {
                $request->merge([
                    'ativo' => 1
                ]);
                FuncionamentoDelivery::create($request->all());
                session()->flash('flash_sucesso', 'Cadastro com sucesso!');
            } catch (\Exception $e) {
                session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
                __saveLogError($e, request()->empresa_id);
            }
            return redirect()->back();
        } else {
            session()->flash('flash_erro', 'Dia já cadastrado');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $item = FuncionamentoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('funcionamento_delivery.edit', compact('item'));
    }

    public function destroy($id)
    {
        $item = FuncionamentoDelivery::findOrFail($id);
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
        return redirect()->back();
    }

    public function alterarStatus($id)
    {
        $item = FuncionamentoDelivery::where('id', $id)->first();
        $item->ativo = !$item->ativo;
        $item->save();
        return redirect()->route('funcionamentoDelivery.index');
    }
}
