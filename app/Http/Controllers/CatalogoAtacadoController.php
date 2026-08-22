<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\ListaPreco;
use App\Models\Produto;
use App\Models\ProdutoListaPreco;
use Illuminate\Http\Request;

class CatalogoAtacadoController extends Controller
{
    /**
     * Exibe o catálogo público de produtos de uma empresa.
     *
     * O catálogo não mostra imagens de produtos nem valores de custo. Quando
     * existir uma lista de preços contendo "atacado" no nome, seus valores têm
     * prioridade sobre o valor de venda padrão do produto.
     */
    public function index(Request $request, $empresaId)
    {
        $empresaId = (int) $empresaId;

        $empresa = Empresa::with(['configNota.cidade', 'cidade'])
            ->findOrFail($empresaId);

        $config = $empresa->configNota;
        $termo = trim((string) $request->input('q', ''));
        $categoriaId = $request->filled('categoria_id')
            ? (int) $request->input('categoria_id')
            : null;

        $listaAtacado = ListaPreco::where('empresa_id', $empresaId)
            ->whereRaw('LOWER(nome) LIKE ?', ['%atacado%'])
            ->orderBy('id')
            ->first();

        $query = Produto::with('categoria')
            ->where('empresa_id', $empresaId)
            ->where('inativo', 0);

        if ($termo !== '') {
            $query->where(function ($produtoQuery) use ($termo) {
                $produtoQuery->where('nome', 'like', '%' . $termo . '%')
                    ->orWhere('referencia', 'like', '%' . $termo . '%')
                    ->orWhere('codBarras', 'like', '%' . $termo . '%')
                    ->orWhereHas('categoria', function ($categoriaQuery) use ($termo) {
                        $categoriaQuery->where('nome', 'like', '%' . $termo . '%');
                    });
            });
        }

        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        $produtos = $query
            ->orderBy('nome')
            ->paginate(50)
            ->appends($request->query());

        $precosAtacado = collect();

        if ($listaAtacado && $produtos->count() > 0) {
            $precosAtacado = ProdutoListaPreco::where('lista_id', $listaAtacado->id)
                ->whereIn('produto_id', $produtos->pluck('id')->all())
                ->pluck('valor', 'produto_id');
        }

        foreach ($produtos as $produto) {
            $produto->catalogo_valor = $precosAtacado->has($produto->id)
                ? $precosAtacado->get($produto->id)
                : $produto->valor_venda;
        }

        $categorias = Categoria::where('empresa_id', $empresaId)
            ->whereHas('produtos', function ($produtoQuery) use ($empresaId) {
                $produtoQuery->where('empresa_id', $empresaId)
                    ->where('inativo', 0);
            })
            ->orderBy('nome')
            ->get();

        $identidade = $this->dadosDaEmpresa($empresa, $config);
        $casasDecimais = $config && $config->casas_decimais !== null
            ? (int) $config->casas_decimais
            : 2;

        return view('catalogo_atacado.index', compact(
            'empresa',
            'config',
            'identidade',
            'produtos',
            'categorias',
            'termo',
            'categoriaId',
            'listaAtacado',
            'casasDecimais'
        ));
    }

    private function dadosDaEmpresa($empresa, $config)
    {
        $nome = $config && $config->nome_fantasia
            ? $config->nome_fantasia
            : ($empresa->nome_fantasia ?: $empresa->razao_social);

        $razaoSocial = $config && $config->razao_social
            ? $config->razao_social
            : $empresa->razao_social;

        $telefone = $config && $config->fone
            ? $config->fone
            : $empresa->telefone;

        $email = $config && $config->email
            ? $config->email
            : $empresa->email;

        $cidade = null;
        $uf = null;

        if ($config && $config->cidade) {
            $cidade = $config->cidade->nome;
            $uf = $config->UF;
        } elseif ($empresa->cidade) {
            $cidade = $empresa->cidade->nome;
            $uf = $empresa->uf;
        }

        $logradouro = $config && $config->logradouro
            ? $config->logradouro
            : $empresa->rua;

        $numero = $config && $config->numero
            ? $config->numero
            : $empresa->numero;

        $bairro = $config && $config->bairro
            ? $config->bairro
            : $empresa->bairro;

        $enderecoPartes = array_filter([
            trim((string) $logradouro . ($numero ? ', ' . $numero : '')),
            $bairro,
            trim((string) $cidade . ($uf ? '/' . $uf : '')),
        ]);

        return [
            'nome' => $nome ?: 'Catálogo de produtos',
            'razao_social' => $razaoSocial,
            'telefone' => $telefone,
            'telefone_link' => preg_replace('/\D+/', '', (string) $telefone),
            'email' => $email,
            'endereco' => implode(' • ', $enderecoPartes),
            'logo_url' => $config && $config->logo
                ? asset('uploads/configEmitente/' . $config->logo)
                : null,
        ];
    }
}