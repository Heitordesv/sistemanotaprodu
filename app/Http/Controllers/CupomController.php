<?php

namespace App\Http\Controllers;

use App\Models\CupomDesconto;
use App\Models\ConfigNota;
use Illuminate\Http\Request;

class CupomController extends Controller
{
    // Exibe a lista de cupons cadastrados
    public function index(Request $request)
    {
        // Recupera a configuração da empresa
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        // Verifica se a configuração existe
        if (!$config) {
            return redirect()->back()->with('error', 'Configuração não encontrada para esta empresa.');
        }

        // Verifica se o token de autenticação está configurado
        if (!$config->user_id) {
            return redirect()->back()->with('error', 'Token de autenticação não configurado.');
        }

        // Filtra os cupons com base no user_id da configuração
        $cupons = CupomDesconto::where('user_id', $config->user_id)->get(); // Filtra pelos cupons associados ao user_id

        return view('cupom.index', compact('cupons'));
    }

    // Exibe o formulário de criação de cupom
    public function create()
    {
        return view('cupom.create');
    }

    // Armazena um novo cupom de desconto
    public function store(Request $request)
    {
        // Recupera a configuração da empresa
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        // Verifica se a configuração existe
        if (!$config) {
            return redirect()->back()->with('error', 'Configuração não encontrada para esta empresa.');
        }

        // Verifica se o token de autenticação está configurado
        if (!$config->user_id) {
            return redirect()->back()->with('error', 'Token de autenticação não configurado.');
        }

        // Validação dos dados do formulário
        $validatedData = $request->validate([
            'ativacao' => 'required|string|max:20',
            'porcentagem' => 'required|numeric|min:0|max:100',
            'total_vezes' => 'required|integer|min:1',
            'mostrar_site' => 'required|boolean',
            'data_validade' => 'required|date',
            'vip' => 'required|boolean',
        ]);

        try {
            // Criação do cupom de desconto
            CupomDesconto::create([
                'user_id' => $config->user_id,
                'ativacao' => $validatedData['ativacao'],
                'porcentagem' => $validatedData['porcentagem'],
                'total_vezes' => $validatedData['total_vezes'],
                'mostrar_site' => $validatedData['mostrar_site'],
                'data_validade' => $validatedData['data_validade'],
                'vip' => $validatedData['vip'],
            ]);

            // Redireciona com sucesso
            return redirect()->route('cupom.create')->with('success', 'Cupom cadastrado com sucesso!');
        } catch (\Exception $e) {
            // Retorna erro caso ocorra alguma exceção
        return view('cupom.index', compact('cupons'));
        }
    }

    // Exibe o formulário de edição do cupom
    public function edit($id)
    {
        $cupom = CupomDesconto::findOrFail($id); // Busca o cupom pelo ID
        return view('cupom.edit', compact('cupom'));
    }

    public function update(Request $request, $id)
    {
        $cupom = CupomDesconto::findOrFail($id); // Busca o cupom pelo ID

        // Validação dos dados do formulário
        $validatedData = $request->validate([
            'ativacao' => 'required|string|max:20',
            'porcentagem' => 'required|numeric|min:0|max:100',
            'total_vezes' => 'required|integer|min:1',
            'mostrar_site' => 'required|boolean',
            'data_validade' => 'required|date',
            'vip' => 'required|boolean',
        ]);

        try {
            // Atualiza o cupom de desconto
            $cupom->update($validatedData);

            // Redireciona com sucesso
            return redirect()->route('cupom.index')->with('success', 'Cupom atualizado com sucesso!');
        } catch (\Exception $e) {
            // Retorna erro caso ocorra alguma exceção
        return view('cupom.index', compact('cupons'));
        }
    }

    // Exclui o cupom
    public function destroy($id)
    {
        try {
            $cupom = CupomDesconto::findOrFail($id); // Busca o cupom pelo ID
            $cupom->delete(); // Deleta o cupom

            // Redireciona com sucesso
            return redirect()->route('cupom.index')->with('success', 'Cupom excluído com sucesso!');
        } catch (\Exception $e) {
            // Retorna erro caso ocorra alguma exceção
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir o cupom: ' . $e->getMessage());
        }
    }
}
