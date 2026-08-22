<?php

namespace App\Http\Controllers;

use App\Models\BairroDelivery;
use App\Models\BairroDeliveryLoja;
use App\Models\CidadeDelivery;
use App\Models\DeliveryConfig;
use Illuminate\Http\Request;

class BairroDeliveryLojaController extends Controller
{
    public function index(Request $request)
    {
        $config = DeliveryConfig::where('empresa_id', request()->empresa_id)
            ->first();
        if ($config == null) {
            session()->flash('flash_erro', 'Configure o delivery primeiro!');
            return redirect('/configDelivery');
        }
        $bairros = BairroDeliveryLoja::where('empresa_id', request()->empresa_id)
            ->orderBy('nome', 'asc')
            ->paginate(20);
        $bairrosDoSuper = BairroDelivery::where('cidade_id', $config->cidade_id)
            ->get();

        $cidades = CidadeDelivery::all();
        $data = BairroDeliveryLoja::when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
            ->when(!empty($request->cidade), function ($q) use ($request) {
                return $q->where('cidade_id', $request->cidade);
            })
            ->paginate(env("PAGINACAO"));
        return view('bairros_delivery_loja.index', compact('bairros', 'bairrosDoSuper', 'cidades', 'data'));
    }

    public function create()
    {
        $cidades = CidadeDelivery::all();
        return view('bairros_delivery_loja.create', compact('cidades'));
    }

    public function store(request $request)
    {
        try {
            $request->merge([
                'cidade_id' => $request->cidade
            ]);
            BairroDeliveryLoja::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('bairrosDeliveryLoja.index');
    }

    public function herdar()
    {
        $config = DeliveryConfig::where('empresa_id', request()->empresa_id)
            ->first();
        $bairrosDoSuper = BairroDelivery::where('cidade_id', $config->cidade_id)
            ->get();
        foreach ($bairrosDoSuper as $b) {
            $item = [
                'empresa_id' => request()->empresa_id,
                'nome' => $b->nome,
                'valor_entrega' => $b->valor_entrega
            ];
            BairroDeliveryLoja::create($item);
        }
        session()->flash('flash_sucesso', 'Bairros cadastrados para sua configuração de delivery!');
        return redirect()->route('bairrosDeliveryLoja.index');
    }

    public function edit($id)
    {
        $item = BairroDeliveryLoja::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $cidades = CidadeDelivery::all();
        return view('bairros_delivery_loja.edit', compact('cidades', 'item'));
    }

    public function update(Request $request, $id)
    {
        $item = BairroDeliveryLoja::findOrFail($id);
        try {
            $request->merge([
                'cidade_id' => $request->cidade
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('bairrosDeliveryLoja.index');
    }

    public function destroy($id)
    {
        $item = BairroDeliveryLoja::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Apagado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('bairrosDeliveryLoja.index');
    }
}
