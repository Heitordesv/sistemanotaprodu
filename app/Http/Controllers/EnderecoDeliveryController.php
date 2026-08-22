<?php

namespace App\Http\Controllers;

use App\Models\ClienteDelivery;
use App\Models\DeliveryConfig;
use App\Models\EnderecoDelivery;
use App\Models\PedidoDelivery;
use Illuminate\Http\Request;

class EnderecoDeliveryController extends Controller
{
    public function store(Request $request)
    {
        $item = PedidoDelivery::findOrFail($request->pedido_id);
        $cid = DeliveryConfig::where('empresa_id', request()->empresa_id)->first();
        $cidade_id = $cid->id;
        try {
            $request->merge([
                'cidade_id' => $cidade_id,
                'principal' => $request->principal ?? '',
                'referencia' => $request->referencia ?? '',
            ]);
            $end = EnderecoDelivery::create($request->all());
            $item->endereco_id = $end->id;
            $item->save();

            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('pedidosDelivery.frenteComPedido', $item->id);
    }

    public function edit($id)
    {
        $item = EnderecoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('enderecos_delivery.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $cliente = $request->cliente_id;
        $item = EnderecoDelivery::findOrFail($id);
        $cid = DeliveryConfig::where('empresa_id', request()->empresa_id)->first();
        $cidade_id = $cid->id;
        try{
            $request->merge([
                'cidade_id' => $cidade_id,
                'principal' => $request->principal ?? ''
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        }catch(\Exception $e){
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('clientesDelivery.enderecos', $cliente);
    }
}
