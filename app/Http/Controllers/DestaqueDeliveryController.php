<?php

namespace App\Http\Controllers;

use App\Models\DeliveryConfig;
use App\Models\DestaqueDelivery;
use App\Models\DestaqueDeliVeryMaster;
use Illuminate\Http\Request;

class DestaqueDeliveryController extends Controller
{
    public function index()
    {
        $data = DestaqueDelivery::where('empresa_id', request()->empresa_id)->get();
        return view('destaque_delivery.index', compact('data'));
    }

    public function create()
    {
        $lojas = DeliveryConfig::orderBy('nome')->get();
        return view('destaque_delivery.create', compact('lojas'));
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'produto_id' => $request->produto_id,
                'empresa_id' => $request->empresa_id
            ]);
            DestaqueDelivery::create($request->all());
            session()->flash('flash_sucesso', 'Cadastro com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('destaquesDelivery.index');
    }

    public function edit($id)
    {
        $item = DestaqueDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $lojas = DeliveryConfig::orderBy('nome')->get();
        return view('destaque_delivery.edit', compact('item', 'lojas'));
    }

    public function update(Request $request, $id)
    {
        $item = DestaqueDelivery::findOrFail($id);
        try {
            $request->merge([
                'produto_id' => $request->produto_id,
                'empresa_id' => $request->empresa_id
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('destaquesDelivery.index');
    }

    public function destroy($id)
    {
        $item = DestaqueDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('destaquesDelivery.index');
    }
}
