<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MensagemPersonalizada;
use App\Models\ConfigNota;

class MensagemPersonalizadaController extends Controller
{
    // Lista todas as mensagens
    public function index(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
    
        if (!$config) {
            return redirect()->back()->with('error', 'Configuração não encontrada para esta empresa.');
        }
    
        if (!$config->user_id) {
            return redirect()->back()->with('error', 'Token de autenticação não configurado.');
        }
    
        $mensagens = MensagemPersonalizada::where('user_id', $config->user_id)->get();
    
        return view('mensagem_personalizada.index', compact('mensagens'));
    }
    

    public function create()
    {
        return view('mensagem_personalizada.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'empresa_id' => 'required|integer'
    ]);

    $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

    if (!$config || !$config->user_id) {
        return redirect()->back()->with('error', 'Não foi possível identificar o usuário.');
    }

    $request->validate([
        'mensagem' => 'required|string',
        'status' => 'required|string',
        'tipo' => 'required|string',
    ]);

    MensagemPersonalizada::create([
        'user_id' => $config->user_id,
        'mensagem' => $request->mensagem,
        'status' => $request->status,
        'tipo' => $request->tipo,
    ]);

    return redirect()->route('mensagem_personalizada.index', ['empresa_id' => $request->empresa_id])
                     ->with('success', 'Mensagem cadastrada com sucesso!');
}


// Mostra o formulário de edição
public function edit($id)
{
    $mensagem = MensagemPersonalizada::findOrFail($id);
    return view('mensagem_personalizada.edit', compact('mensagem'));
}

// Atualiza a mensagem
public function update(Request $request, $id)
{
    // Validação para garantir que os dados da mensagem sejam válidos
    $request->validate([
        'mensagem' => 'required|string',
        'status' => 'required|string',
        'tipo' => 'required|string',
    ]);

    // Localiza a mensagem para atualização
    $mensagem = MensagemPersonalizada::findOrFail($id);
    $mensagem->update([
        'mensagem' => $request->mensagem,
        'status' => $request->status,
        'tipo' => $request->tipo,
    ]);

    return redirect()->route('mensagem_personalizada.index')
                     ->with('success', 'Mensagem atualizada com sucesso!');
}

// Exclui uma mensagem
public function destroy($id)
{
    $mensagem = MensagemPersonalizada::findOrFail($id);
    $mensagem->delete();

    return redirect()->route('mensagem_personalizada.index')
                     ->with('success', 'Mensagem excluída com sucesso!');
}
}
