<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresaws;
use Carbon\Carbon;

class EmpresawsController extends Controller
{
    // Listagem de empresas com paginação
    public function index()
    {
        $empresas = Empresaws::orderBy('id_empresa', 'desc')->paginate(10);
        return view('leads.index', compact('empresas'));
    }

    // Mostrar detalhes de uma empresa (opcional)
    public function show($id)
    {
        $empresa = Empresaws::findOrFail($id);
        return view('leads.show', compact('empresa'));
    }

    // Formulário de edição (opcional)
    public function edit($id)
    {
        $empresa = Empresaws::findOrFail($id);
        return view('leads.edit', compact('empresa'));
    }

    // Atualizar dados da empresa
    public function update(Request $request, $id)
    {
        $empresa = Empresaws::findOrFail($id);

        $empresa->update($request->only([
            'nome_empresa',
            'nome_empresa_link',
            'cidade_empresa',
            'telefone_empresa',
            'email_empresa',
            'empresa_data_renovacao'
        ]));

        return redirect()->route('leads.index')->with('success', 'Empresa atualizada com sucesso.');
    }

    // Excluir empresa
    public function destroy($id)
    {
        $empresa = Empresaws::findOrFail($id);
        $empresa->delete();

        return redirect()->route('leads.index')->with('success', 'Empresa excluída com sucesso.');
    }

  public function renovar(Request $request, $id)
{
    $request->validate([
        'empresa_data_renovacao' => 'required|date',
    ]);

    $empresa = Empresaws::findOrFail($id);
    $empresa->empresa_data_renovacao = $request->empresa_data_renovacao; // ISO date
    $empresa->save();

    return redirect()->route('leads.index')->with('success', 'Data de renovação atualizada.');
}
}