<?php

namespace App\Http\Controllers;

use App\Models\ClienteDelivery;
use App\Models\EnderecoDelivery;
use App\Models\PedidoDelivery;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;
use Illuminate\Support\Facades\DB;


class ClienteDeliveryController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index()
    {
        $data = ClienteDelivery::where('empresa_id', request()->empresa_id)->get();
        return view('cliente_delivery.index', compact('data'));
    }

    public function create()
    {
        return view('cliente_delivery.create');
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $file_name = '';
                if ($request->hasFile('image')) {
                    $file_name = $this->util->uploadImage($request, '/clientesDelivery');
                }
                $request->merge([
                    'foto' => $file_name,
                    'senha' => md5($request->senha) ?? ''
                ]);
                ClienteDelivery::create($request->all());
            });
            session()->flash('flash_sucesso', 'Cadastro com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('clientesDelivery.index');
    }

    public function edit($id)
    {
        $item = ClienteDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('cliente_delivery.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ClienteDelivery::findOrFail($id);
        try {
            $request->merge([
                'senha' => md5($request->senha)
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('clientesDelivery.index');
    }

    public function show($id)
    {
        $item = ClienteDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('cliente_delivery.pedidos', compact('item'));
    }

    public function enderecos($id)
    {
        $item = ClienteDelivery::findOrFail($id);
        return view('cliente_delivery.list_enderecos', compact('item'));
    }
}
