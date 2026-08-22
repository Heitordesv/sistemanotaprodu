<?php

namespace App\Http\Controllers;

use App\Models\Motoboy;
use App\Models\ConfigNota;
use Illuminate\Http\Request;

class MotoboyController extends Controller
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

        $motoboys = Motoboy::where('user_id', $config->user_id)->get();

        return view('motoboy.index', compact('motoboys'));
    }

    public function create()
    {
        return view('motoboy.create');
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

        $request->validate([
            'user_id' => 'required|integer',
            'deliveryman_name' => 'required|string|max:255',
            'deliveryman_phone_number' => 'required|string|max:20',
        ]);

        Motoboy::create($request->all());

        return redirect()->route('motoboy.index')->with('success', 'Motoboy cadastrado com sucesso!');
    }

    public function show($id)
    {
        $motoboy = Motoboy::findOrFail($id);
        return view('motoboy.show', compact('motoboy'));
    }

    public function edit($id)
    {
        $motoboy = Motoboy::findOrFail($id);
        return view('motoboy.edit', compact('motoboy'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'deliveryman_name' => 'required|string|max:255',
            'deliveryman_phone_number' => 'required|string|max:20',
        ]);

        $motoboy = Motoboy::findOrFail($id);
        $motoboy->update($request->all());

        return redirect()->route('motoboy.index')->with('success', 'Motoboy atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $motoboy = Motoboy::findOrFail($id);
        $motoboy->delete();

        return redirect()->route('motoboy.index')->with('success', 'Motoboy excluído com sucesso!');
    }
}
