<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link; // Assume que este modelo representa sua tabela de empresas/links
use App\Models\ConfiguracaoNota; // Se ConfiguracaoNota tem a logo, deve ser relacionado ao Link
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\ContaReceber;
use App\Models\Cliente;
use App\Models\Empresa;

class LinkController extends Controller
{
    /**
     * Exibe o catálogo principal de links e todos os produtos.
     * Acessado via rota '/catalogo'.
     */
    public function catalogo()
    {
        // Busca todos os links para a seção superior (seção "Nosso Catálogo de Links")
        $links = Link::with('configuracaoNota')->orderBy('nome_link')->get();
        
        // Busca TODOS os produtos ativos (sem filtro por empresa_id aqui para o catálogo geral)
        $produtos = Produto::with('categoria')
                           ->where('inativo', 0)
                           ->orderBy('nome')
                           ->get();

        // Para o catálogo geral, buscamos todas as categorias (já que não há filtro de empresa)
        $categorias = Categoria::orderBy('nome')->get();

        // currentNomeLink é null, indicando que não estamos em um catálogo de link específico
        return view('link.index', compact('links', 'produtos', 'categorias'))
               ->with('currentNomeLink', null)
               ->with('selectedCategoryId', null); // Nenhuma categoria selecionada por padrão
    }

    /**
     * Exibe o catálogo de links e produtos filtrados por um 'nome_link' específico.
     * Esta é a função chamada pela rota '/catalogo-links/{nomeLink}'.
     */
    public function catalogoPorNomeLink($nomeLink)
    {
        // 1. Encontra o link (empresa) pelo nome_link. Se não encontrar, retorna 404.
        $link = Link::where('nome_link', $nomeLink)
                    ->with('configuracaoNota')
                    ->firstOrFail(); 

        // 2. Para a seção de links da view, exibimos APENAS o link encontrado.
        $links = collect([$link]);

        // 3. BUSCA OS PRODUTOS APENAS DA EMPRESA_ID ASSOCIADA A ESTE LINK.
        $produtos = Produto::with('categoria')
                           ->where('empresa_id', $link->id) 
                           ->where('inativo', 0)
                           ->orderBy('nome')
                           ->get();

        // 4. NOVO: Busca as categorias que estão relacionadas aos PRODUTOS DESTA EMPRESA.
        // Isso garante que o dropdown só mostre categorias relevantes.
        $categorias = Categoria::whereHas('produtos', function ($query) use ($link) {
                                $query->where('empresa_id', $link->id)
                                      ->where('inativo', 0); // Opcional: só categorias de produtos ativos
                            })
                            ->orderBy('nome')
                            ->get();

        // 5. Passa os dados para a view.
        return view('link.index', compact('links', 'produtos', 'categorias'))
               ->with('currentNomeLink', $nomeLink)
               ->with('selectedCategoryId', null); // Nenhuma categoria pré-selecionada
    }

    /**
     * Exibe produtos filtrados por categoria, referentes a um 'nome_link' específico.
     * Chamada pela rota '/catalogo-links/{nomeLink}/categoria/{categoriaId}'.
     */
    public function catalogoPorCategoria($nomeLink, $categoriaId)
    {
        // 1. Encontra o link (empresa) pelo nome_link para obter o empresa_id necessário.
        $link = Link::where('nome_link', $nomeLink)->firstOrFail();

        // 2. Para a seção de links, exibimos apenas o link correspondente.
        $links = collect([$link]);

        // 3. Inicia a consulta de produtos, filtrando primeiro pelo 'empresa_id' do link.
        $queryProdutos = Produto::where('empresa_id', $link->id) 
                                ->where('inativo', 0);

        // 4. Aplica o filtro de categoria APENAS SE um ID válido for fornecido.
        if ($categoriaId != 'todos' && $categoriaId != 0) {
            $queryProdutos->where('categoria_id', $categoriaId);
        }

        // 5. Obtém os produtos com a categoria relacionada e ordena.
        $produtos = $queryProdutos->with('categoria')->orderBy('nome')->get();

        // 6. NOVO: Busca as categorias que estão relacionadas aos PRODUTOS DESTA EMPRESA,
        // mesmo quando já há um filtro de categoria aplicado.
        $categorias = Categoria::whereHas('produtos', function ($query) use ($link) {
                                $query->where('empresa_id', $link->id)
                                      ->where('inativo', 0); // Opcional: só categorias de produtos ativos
                            })
                            ->orderBy('nome')
                            ->get();

        // 7. Passa os dados para a view.
        return view('link.index', compact('links', 'produtos', 'categorias'))
               ->with('currentNomeLink', $nomeLink)
               ->with('selectedCategoryId', $categoriaId); // Mantém a categoria selecionada no dropdown
    }
public function minhasFaturas(Request $request, $nomeLink)
{
    // 1. Busca empresa pelo nome_link (nome da empresa passado na URL)
    $empresa = Empresa::where('nome_link', $nomeLink)->firstOrFail();

    $faturas = collect();
    $cliente = null;
    $consultaRealizada = false;

    // 2. Verifica se foi passado CPF/CNPJ ou telefone para buscar cliente
    if ($request->filled('cpf_cnpj') || $request->filled('telefone')) {
        $consultaRealizada = true;

        // Inicia query para buscar cliente da empresa encontrada
        $queryCliente = Cliente::where('empresa_id', $empresa->id);

        // Se passou telefone, filtra por telefone (usando like para buscas parciais)
        if ($request->filled('telefone')) {
            $telefone = $request->input('telefone');
            $queryCliente->where('telefone', 'like', "%$telefone%");
        }

        // Se passou CPF/CNPJ, filtra removendo caracteres especiais
        if ($request->filled('cpf_cnpj')) {
            $cpfCnpj = preg_replace('/\D/', '', $request->input('cpf_cnpj'));
            $queryCliente->whereRaw("REPLACE(REPLACE(REPLACE(cpf_cnpj, '.', ''), '-', ''), '/', '') = ?", [$cpfCnpj]);
        }

        // Busca o primeiro cliente que bate com os filtros
        $cliente = $queryCliente->first();

        // Se achou cliente, busca todas as faturas relacionadas a ele na empresa
        if ($cliente) {
            $faturas = ContaReceber::where('empresa_id', $empresa->id)
                ->where('cliente_id', $cliente->id)
                ->orderBy('data_vencimento', 'desc')
                ->get();
        }
    }

    // Retorna a view com os dados
    return view('link.minhasfaturas', compact('faturas', 'empresa', 'cliente', 'consultaRealizada'))
        ->with('currentNomeLink', $nomeLink);
}
public function minhasFaturasempresa(Request $request, $nomeLink)
{
    // 1. Busca a empresa pelo nome_link (nome da empresa passado na URL)
    $empresa = Empresa::where('nome_link', $nomeLink)->firstOrFail();

    // 2. Consulta as faturas da empresa com empresa_id = 1
    $faturas = ContaReceber::where('empresa_id', 1)
        ->where('empresa_id_emp', $empresa->id)
        ->orderBy('data_vencimento', 'desc')
        ->get();

    // Retorna a view com os dados
    return view('link.minhasfaturasempresa', compact('faturas', 'empresa'))
        ->with('currentNomeLink', $nomeLink);
}


    public function xmlFeed($nomeLink)
    {
        // 1. Encontra o link (empresa) pelo nome_link.
        $link = Link::where('nome_link', $nomeLink)
                     ->firstOrFail();

        // 2. Busca os produtos ativos APENAS da empresa associada a este link.
        $produtos = Produto::where('empresa_id', $link->id)
                           ->where('inativo', 0)
                           ->get();

        // 3. Retorne a view do feed XML, passando a coleção de produtos.
        // O nome da view deve ser 'link.feedinstagram', correspondendo ao caminho do arquivo.
        return response()
            ->view('link.feedinstagram', ['produtos' => $produtos, 'link' => $link])
            ->header('Content-Type', 'text/xml');
    }
}
