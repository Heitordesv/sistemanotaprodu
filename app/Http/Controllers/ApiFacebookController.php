<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FacebookApi;
use App\Models\ConfigNota;

class ApiFacebookController extends Controller
{
    public function index(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->back()->with('error', 'Nenhuma configuração encontrada para esta empresa.');
        }

        if (!$config->user_id) {
            return redirect()->back()->with('error', 'Token de autenticação não configurado para esta empresa.');
        }

        $request->merge([
            'user_id' => $config->user_id,
        ]);

        $data = FacebookApi::where('empresa_id', $request->empresa_id)->paginate(30);

        return view('api_facebook.index', compact('data'));
    }

    public function create()
    {
        return view('api_facebook.create');
    }

    public function edit($id)
    {
        $item = FacebookApi::findOrFail($id);
        return view('api_facebook.edit', compact('item'));
    }

    public function store(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return redirect()->back()->with('error', 'Nenhuma configuração encontrada para esta empresa.');
        }

        if (!$config->user_id) {
            return redirect()->back()->with('error', 'Token de autenticação não configurado para esta empresa.');
        }

        $request->merge([
            'user_id' => $config->user_id,
        ]);

        $validatedData = $request->validate([
            'empresa_id' => 'required|integer',
            'user_id' => 'required|integer',
            'nome_empresa' => 'required|string|max:255',
            'pixel_id' => 'required|string|max:100',
            'access_token' => 'required|string|max:1000',
        ]);

        try {
            FacebookApi::create($validatedData);
            session()->flash('flash_sucesso', 'Configuração da API do Facebook realizada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Ocorreu um erro ao salvar a configuração: ' . $e->getMessage());
        }

        return redirect()->route('apiFacebook.index');
    }

    public function update(Request $request, $id)
    {
        $item = FacebookApi::findOrFail($id);

        try {
            $validatedData = $request->validate([
                'nome_empresa' => 'required|string|max:255',
                'pixel_id' => 'required|string|max:100',
                'access_token' => 'required|string',
            ]);

            $item->update($validatedData);
            session()->flash('flash_sucesso', 'Configuração atualizada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao atualizar a configuração: ' . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }

        return redirect()->route('apiFacebook.index');
    }

    public function destroy($id)
    {
        $item = FacebookApi::findOrFail($id);

        if (!__valida_objeto($item)) {
            abort(403);
        }

        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Configuração excluída com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Erro ao excluir: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('apiFacebook.index');
    }
}
