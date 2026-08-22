<?php

namespace App\Http\Controllers;

use App\Models\ConfigEcommerce;
use App\Models\ProdutoEcommerce;
use App\Services\EcommerceStockService;
use Illuminate\Http\Request;

class EcommerceStorefrontController extends Controller
{
    public function __construct(private EcommerceStockService $stockService)
    {
    }

    private function legacy(): EcommerceController
    {
        return app(EcommerceController::class);
    }

    private function config(string $link): ConfigEcommerce
    {
        return ConfigEcommerce::where('link', strtolower($link))->firstOrFail();
    }

    public function index(string $link)
    {
        $this->config($link);
        $view = $this->legacy()->index($link);
        return $this->anotar($view, 'produtosEmDestaque');
    }

    public function categorias(string $link)
    {
        $this->config($link);
        return $this->legacy()->categorias($link);
    }

    public function blog(string $link)
    {
        $this->config($link);
        return $this->legacy()->blog($link);
    }

    public function contato(string $link)
    {
        $this->config($link);
        return $this->legacy()->contato($link);
    }

    public function curtidas(string $link)
    {
        $config = $this->config($link);
        $sessao = session('user_ecommerce');

        if (!$sessao || (int) ($sessao['empresa_id'] ?? 0) !== (int) $config->empresa_id) {
            return redirect('/loja/' . strtolower($config->link) . '/login')
                ->with('flash_erro', 'Entre na sua conta para acessar os favoritos.');
        }

        return $this->legacy()->curtidas($link);
    }

    public function pesquisa(Request $request, string $link)
    {
        $config = $this->config($link);
        $termo = trim((string) $request->query('pesquisa', ''));
        $categoriaId = $request->filled('categoria') ? (int) $request->query('categoria') : null;

        if (mb_strlen($termo) < 2) {
            return redirect('/loja/' . strtolower($config->link))
                ->with('flash_erro', 'Digite pelo menos 2 caracteres para pesquisar.');
        }

        $produtos = $this->consultaProdutos($config, $termo, $categoriaId)
            ->paginate(24)
            ->withQueryString();

        $this->aplicarPrecos($produtos, $config);
        $this->anotarProdutos($produtos);

        $baseView = $this->legacy()->categorias($link);
        $baseData = method_exists($baseView, 'getData') ? $baseView->getData() : [];
        $default = $baseData['default'] ?? [];
        $rota = $baseData['rota'] ?? '/loja/' . strtolower($config->link);
        $template = $default['template'] ?? $config->tema_ecommerce ?? 'ecommerce';

        return view($template . '/produtos_categoria')
            ->with('default', $default)
            ->with('produtos', $produtos)
            ->with('pesquisa', $termo)
            ->with('categoria', null)
            ->with('categoriaBuscaId', $categoriaId)
            ->with('shop', true)
            ->with('rota', $rota)
            ->with('title', 'Pesquisa: ' . $termo);
    }

    public function sugestoesPesquisa(Request $request, string $link)
    {
        $config = $this->config($link);
        $termo = trim((string) $request->query('pesquisa', ''));
        $categoriaId = $request->filled('categoria') ? (int) $request->query('categoria') : null;

        if (mb_strlen($termo) < 2) {
            return response()->json(['items' => []]);
        }

        $produtos = $this->consultaProdutos($config, $termo, $categoriaId)
            ->limit(8)
            ->get();

        $this->aplicarPrecos($produtos, $config);
        $rota = '/loja/' . strtolower($config->link);

        return response()->json([
            'items' => $produtos->map(function (ProdutoEcommerce $produto) use ($rota) {
                $fiscal = $produto->produto;
                $imagem = optional($produto->galeria->first())->img ?: $produto->img;

                return [
                    'id' => $produto->id,
                    'nome' => (string) ($fiscal->nome ?? 'Produto'),
                    'preco' => 'R$ ' . number_format((float) $produto->valor, 2, ',', '.'),
                    'categoria' => (string) optional($produto->categoria)->nome,
                    'codigo' => (string) (($fiscal->referencia ?? '') ?: ($fiscal->codBarras ?? '')),
                    'imagem' => $imagem,
                    'url' => $rota . '/' . $produto->id . '/verProduto',
                ];
            })->values(),
        ]);
    }

    private function consultaProdutos(ConfigEcommerce $config, string $termo, ?int $categoriaId = null)
    {
        $like = '%' . $termo . '%';
        $termoLower = mb_strtolower($termo);

        return ProdutoEcommerce::query()
            ->with(['produto', 'galeria', 'categoria', 'subCategoria'])
            ->select('produto_ecommerces.*')
            ->join('produtos', 'produtos.id', '=', 'produto_ecommerces.produto_id')
            ->where('produto_ecommerces.empresa_id', $config->empresa_id)
            ->where('produto_ecommerces.status', 1)
            ->where(function ($q) {
                $q->whereNull('produtos.inativo')->orWhere('produtos.inativo', 0);
            })
            ->when($categoriaId, fn ($q) => $q->where('produto_ecommerces.categoria_id', $categoriaId))
            ->where(function ($q) use ($like) {
                $q->where('produtos.nome', 'like', $like)
                    ->orWhere('produtos.codBarras', 'like', $like)
                    ->orWhere('produtos.referencia', 'like', $like)
                    ->orWhere('produto_ecommerces.descricao', 'like', $like)
                    ->orWhereHas('categoria', fn ($categoria) => $categoria->where('nome', 'like', $like))
                    ->orWhereHas('subCategoria', fn ($subcategoria) => $subcategoria->where('nome', 'like', $like));
            })
            ->orderByRaw(
                'CASE WHEN LOWER(produtos.nome) = ? THEN 0 WHEN LOWER(produtos.nome) LIKE ? THEN 1 WHEN produtos.codBarras = ? THEN 0 WHEN produtos.referencia = ? THEN 0 ELSE 2 END',
                [$termoLower, $termoLower . '%', $termo, $termo]
            )
            ->orderBy('produtos.nome');
    }

    private function aplicarPrecos($produtos, ConfigEcommerce $config): void
    {
        foreach ($produtos as $produto) {
            $valor = (float) $produto->valor;
            $produto->valor_pix = (float) $config->desconto_padrao_pix > 0
                ? $valor - (($valor * (float) $config->desconto_padrao_pix) / 100)
                : 0;
            $produto->valor_cartao = (float) $config->desconto_padrao_cartao > 0
                ? $valor - (($valor * (float) $config->desconto_padrao_cartao) / 100)
                : 0;
            $produto->valor_boleto = (float) $config->desconto_padrao_boleto > 0
                ? $valor - (($valor * (float) $config->desconto_padrao_boleto) / 100)
                : 0;
        }
    }

    private function anotarProdutos($produtos): void
    {
        $lista = method_exists($produtos, 'items') ? collect($produtos->items()) : collect($produtos);
        if ($lista->isEmpty()) {
            return;
        }

        $disponibilidades = $this->stockService->disponibilidadeEmLote($lista);
        foreach ($lista as $produto) {
            $produto->estoque_disponivel = $disponibilidades[$produto->id] ?? null;
        }
    }

    public function checkout(Request $request, string $link)
    {
        $config = $this->config($link);
        $tipoFrete = strtolower(trim((string) ($request->query('tipo_frete') ?: $request->query('tp_frete') ?: '')));

        if (!in_array($tipoFrete, ['pac', 'sedex', 'gratis', 'retirada'], true)) {
            $tipoFrete = '';
        }

        $sessao = session('user_ecommerce');
        if ($sessao && (int) ($sessao['empresa_id'] ?? 0) === (int) $config->empresa_id) {
            $url = '/loja/' . strtolower($config->link) . '/endereco';
            if ($tipoFrete !== '') {
                $url .= '?tipo_frete=' . urlencode($tipoFrete);
            }
            return redirect($url);
        }

        $request->merge([
            'tp_frete' => $tipoFrete,
            'tipo_frete' => $tipoFrete,
        ]);

        return $this->legacy()->checkout($request, $link);
    }

    public function pagamentoDireto(string $link)
    {
        $config = $this->config($link);
        return redirect('/loja/' . strtolower($config->link) . '/endereco')
            ->with('flash_erro', 'Selecione o endereço e a forma de entrega antes de continuar para o pagamento.');
    }

    public function carrinho(string $link)
    {
        $config = $this->config($link);
        $view = $this->legacy()->carrinho($link);

        if (method_exists($view, 'getData')) {
            $data = $view->getData();
            $carrinho = $data['default']['carrinho'] ?? null;
            if ($carrinho && (int) $carrinho->empresa_id !== (int) $config->empresa_id) {
                abort(403, 'Carrinho inválido para esta loja.');
            }

            if ($carrinho) {
                $produtos = $carrinho->itens->pluck('produto')->filter();
                $disponibilidades = $this->stockService->disponibilidadeEmLote($produtos);
                foreach ($carrinho->itens as $item) {
                    if ($item->produto) {
                        $item->produto->estoque_disponivel = $disponibilidades[$item->produto->id] ?? null;
                    }
                }
            }
        }

        return $view;
    }

    private function anotar($view, string $key)
    {
        if (!method_exists($view, 'getData')) return $view;
        $data = $view->getData();
        $produtos = $data[$key] ?? null;
        if (!$produtos) return $view;

        $disponibilidades = $this->stockService->disponibilidadeEmLote($produtos);
        foreach ($produtos as $produto) {
            $produto->estoque_disponivel = $disponibilidades[$produto->id] ?? null;
        }

        return $view;
    }
}