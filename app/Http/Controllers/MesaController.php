<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class MesaController extends Controller
{
    public function index()
    {
        $data = Mesa::where('empresa_id', request()->empresa_id)->get();

        return view('mesas.index', compact('data'));
    }

    public function create()
    {
        return view('mesas.create');
    }

    public function store(Request $request)
    {
        $mesa = new Mesa();
        $this->__validate($request);
        try {
            $request->merge([
                'token' => Str::random(25)
            ]);
            $mesa->create($request->all());
            session()->flash('flash_sucesso', 'Cadastro com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('mesas.index');
    }

    public function gerarToken($id)
    {
        $item = Mesa::findOrFail($id);
        if (valida_objeto($item)) {
            $item->token = Str::random(25);
            $item->save();
            session()->flash("flash_sucesso", "Token gerado.");
            return redirect()->back();
        } else {
            return redirect('/403');
        }
    }

    public function edit($id)
    {
        $item = Mesa::findOrFail($id);
        return view('mesas.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Mesa::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Aterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('mesas.index');
    }

    public function delete($id)
    {
        $item = Mesa::findOrFail($id);
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('mesas.index');
    }

    private function __validate(Request $request)
    {
        $rules = [
            'nome' => 'required|max:50',
        ];

        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.',

        ];
        $this->validate($request, $rules, $messages);
    }

    public function gerarQrCode(Request $request)
    {
        $url = $request->url;
        $mesas = Mesa::where('empresa_id', request()->empresa_id)->get();
        return view('mesas.gerar_qrcode', compact('mesas', 'url'));
    }

    public function imprimirQrCode(Request $request)
    {
        $url = $request->url;
        return view('mesas.verQrCode', compact('url'));
    }
}
