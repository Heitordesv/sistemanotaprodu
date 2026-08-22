<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaDataController extends Controller
{
    /**
     * Exibe todas as empresas.
     */
    public function index()
    {
        // Recupera todas as empresas da tabela 'empresas'
        $empresas = Empresa::all();

        // Você pode passar $empresas para uma view:
        // return view('empresas.list', compact('empresas'));

        // Para fins de demonstração, vamos retornar como JSON
        return response()->json($empresas);
    }

    /**
     * Encontra uma empresa pelo seu ID.
     */
    public function show($id)
    {
        // Encontra uma empresa pelo ID. Se não encontrar, retorna 404.
        $empresa = Empresa::findOrFail($id);

        // Você pode passar $empresa para uma view:
        // return view('empresas.detail', compact('empresa'));

        // Para fins de demonstração, vamos retornar como JSON
        return response()->json($empresa);
    }

    /**
     * Encontra uma empresa pelo nome_link (link amigável).
     * Este é o mesmo método que usamos no exemplo anterior.
     */
    public function showByNomeLink($nome_link)
    {
        // Encontra uma empresa pelo nome_link. Se não encontrar, retorna 404.
        $empresa = Empresa::where('nome_link', $nome_link)->firstOrFail();

        // Para fins de demonstração, vamos retornar como JSON
        return response()->json($empresa);
    }

    /**
     * Filtra empresas por status.
     * Exemplo de uso: /empresas/filter?status=1
     */
    public function filterByStatus(Request $request)
    {
        $status = $request->query('status'); // Obtém o parâmetro 'status' da URL

        if (is_null($status)) {
            return response()->json(['error' => 'Parâmetro status é obrigatório.'], 400);
        }

        // Filtra empresas onde o status corresponde ao valor fornecido
        $empresas = Empresa::where('status', $status)->get();

        return response()->json($empresas);
    }

    /**
     * Filtra empresas por cidade_id.
     * Exemplo de uso: /empresas/filter-by-city?cidade_id=123
     */
    public function filterByCity(Request $request)
    {
        $cidadeId = $request->query('cidade_id');

        if (is_null($cidadeId)) {
            return response()->json(['error' => 'Parâmetro cidade_id é obrigatório.'], 400);
        }

        // Filtra empresas pela cidade_id
        $empresas = Empresa::where('cidade_id', $cidadeId)->get();

        // Se você tiver a relação 'cidade' definida no modelo Empresa, pode carregá-la:
        // $empresas = Empresa::with('cidade')->where('cidade_id', $cidadeId)->get();

        return response()->json($empresas);
    }

    /**
     * Busca empresas por parte da razão social ou nome fantasia.
     * Exemplo de uso: /empresas/search?query=minha
     */
    public function search(Request $request)
    {
        $query = $request->query('query');

        if (is_null($query)) {
            return response()->json(['error' => 'Parâmetro query é obrigatório.'], 400);
        }

        // Busca empresas onde a razão social OU nome fantasia contém a string da query
        $empresas = Empresa::where('razao_social', 'LIKE', '%' . $query . '%')
                           ->orWhere('nome_fantasia', 'LIKE', '%' . $query . '%')
                           ->get();

        return response()->json($empresas);
    }
}
