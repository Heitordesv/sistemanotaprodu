<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\DivisaoGrade;
use App\Models\ImagemProdutoEcommerce;
use App\Models\Marca;
use App\Models\NaturezaOperacao;
use App\Models\ProdutoEcommerce;
use App\Models\Tributacao;
use App\Utils\UploadUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProdutoEcommerceController extends Controller
{
    public function __construct(protected UploadUtil $util)
    {
    }

    private function empresaId(): int
    {
        $authEmpresa = auth()->user()->empresa_id ?? null;
        if ($authEmpresa) return (int) $authEmpresa;

        $sessao = session('user_logged');
        if (is_array($sessao)) return (int) ($sessao['empresa_id'] ?? $sessao['empresa'] ?? 0);
        if (is_object($sessao)) return (int) ($sessao->empresa_id ?? $sessao->empresa ?? 0);

        abort(403, 'Empresa não identificada.');
    }

    public function index(Request $request)
    {
        $empresaId = $this->empresaId();
        $validateEntry = (new Util())->validateEntry(['tributacaos', 'natureza_operacaos'], $empresaId);
        if ($validateEntry) return redirect($validateEntry['route'])->with('flash_erro', $validateEntry['message']);

        $data = ProdutoEcommerce::with(['produto', 'galeria', 'categoria'])
            ->where('empresa_id', $empresaId)
            ->when($request->filled('nome'), function ($q) use ($request) {
                $nome = trim($request->nome);
                $q->where(fn ($query) => $query->where('descricao', 'LIKE', "%{$nome}%")
                    ->orWhereHas('produto', fn ($produto) => $produto->where('nome', 'LIKE', "%{$nome}%")));
            })
            ->orderByDesc('id')
            ->paginate((int) env('PAGINACAO', 20));

        return view('produtos_ecommerce.index', compact('data'));
    }

    public function create(Request $request)
    {
        $empresaId = $this->empresaId();
        $validateEntry = (new Util())->validateEntry(['categoria_produto_ecommerces', 'tributacaos', 'natureza_operacaos'], $empresaId);
        if ($validateEntry) return redirect($validateEntry['route'])->with('flash_erro', $validateEntry['message']);

        $marcas = Marca::where('empresa_id', $empresaId)->get();
        $categorias = Categoria::where('empresa_id', $empresaId)->get();
        $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', $empresaId)->get();
        $naturezaPadrao = NaturezaOperacao::where('empresa_id', $empresaId)->first();
        $tributacao = Tributacao::where('empresa_id', $empresaId)->first();
        $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', $empresaId)->where('sub_divisao', true)->get();

        return view('produtos_ecommerce.create', compact('categorias', 'marcas', 'categoriasEcommerce', 'naturezaPadrao', 'tributacao', 'divisoes', 'subDivisoes'));
    }

    public function store(Request $request)
    {
        $empresaId = $this->empresaId();
        $this->_validate($request, $empresaId);

        if (ProdutoEcommerce::where('empresa_id', $empresaId)->where('produto_id', $request->produto_id)->exists()) {
            return back()->withInput()->with('flash_erro', 'Este produto já está cadastrado na loja.');
        }

        try {
            $data = $this->dadosProduto($request);
            $data['empresa_id'] = $empresaId;
            $item = ProdutoEcommerce::create($data);
            $this->salvarImagem($request, $item);
            return redirect()->route('produtoEcommerce.index')->with('flash_sucesso', 'Produto cadastrado!');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash_erro', 'Não foi possível cadastrar o produto.');
        }
    }

    public function edit($id)
    {
        $item = $this->produtoDaEmpresa($id);
        $empresaId = $this->empresaId();
        $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', $empresaId)->get();
        $categorias = Categoria::where('empresa_id', $empresaId)->get();
        $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', $empresaId)->where('sub_divisao', true)->get();
        return view('produtos_ecommerce.edit', compact('item', 'categoriasEcommerce', 'categorias', 'divisoes', 'subDivisoes'));
    }

    public function update(Request $request, $id)
    {
        $empresaId = $this->empresaId();
        $item = $this->produtoDaEmpresa($id);
        $this->_validate($request, $empresaId);

        $duplicado = ProdutoEcommerce::where('empresa_id', $empresaId)
            ->where('produto_id', $request->produto_id)
            ->where('id', '<>', $item->id)
            ->exists();
        if ($duplicado) return back()->withInput()->with('flash_erro', 'Este produto já está cadastrado na loja.');

        try {
            $item->fill($this->dadosProduto($request))->save();
            $this->salvarImagem($request, $item);
            return redirect()->route('produtoEcommerce.index')->with('flash_sucesso', 'Produto alterado com sucesso!');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash_erro', 'Não foi possível alterar o produto.');
        }
    }

    public function galeria($id)
    {
        $item = $this->produtoDaEmpresa($id);
        return view('produtos_ecommerce.galery', compact('item'));
    }

    public function saveImagem(Request $request)
    {
        $produto = $this->produtoDaEmpresa($request->produto_id);
        $request->validate(['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);

        if ($produto->galeria()->count() >= 10) {
            return back()->with('flash_erro', 'A galeria aceita no máximo 10 imagens por produto.');
        }

        $this->salvarImagem($request, $produto);
        return redirect()->route('produtoEcommerce.galeria', [$produto->id])->with('flash_sucesso', 'Imagem cadastrada com sucesso!');
    }

    public function deleteImagem($id)
    {
        $empresaId = $this->empresaId();
        $item = ImagemProdutoEcommerce::where('id', $id)
            ->whereHas('produto', fn ($q) => $q->where('empresa_id', $empresaId))
            ->firstOrFail();

        try {
            $this->util->unlinkImage($item, '/produtoEcommerce', 'path');
            $item->delete();
            return back()->with('flash_sucesso', 'Imagem removida com sucesso!');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('flash_erro', 'Não foi possível remover a imagem.');
        }
    }

    public function destroy(Request $request, $id)
    {
        $item = $this->produtoDaEmpresa($id);
        try {
            foreach ($item->galeria as $imagem) {
                try { $this->util->unlinkImage($imagem, '/produtoEcommerce', 'path'); } catch (\Throwable $e) { report($e); }
            }
            $item->delete();
            return redirect()->route('produtoEcommerce.index')->with('flash_sucesso', 'Produto removido com sucesso!');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('flash_erro', 'Não foi possível remover o produto.');
        }
    }

    private function produtoDaEmpresa($id): ProdutoEcommerce
    {
        return ProdutoEcommerce::where('id', $id)->where('empresa_id', $this->empresaId())->firstOrFail();
    }

    private function salvarImagem(Request $request, ProdutoEcommerce $produto): void
    {
        if (!$request->hasFile('image')) return;
        $fileName = $this->util->uploadImage($request, '/produtoEcommerce');
        ImagemProdutoEcommerce::create(['produto_id' => $produto->id, 'path' => $fileName]);
    }

    private function dadosProduto(Request $request): array
    {
        return [
            'produto_id' => (int) $request->produto_id,
            'valor' => __convert_value_bd($request->valor),
            'categoria_id' => (int) $request->categoriaEcommerce_id,
            'percentual_desconto_view' => min(100, max(0, (float) ($request->percentual_desconto_view ?? 0))),
            'status' => $request->boolean('status'),
            'sub_categoria_id' => (int) ($request->sub_categoriaEcommerce_id ?? 0),
            'controlar_estoque' => $request->boolean('controlar_estoque'),
            'destaque' => $request->boolean('destaque'),
            'descricao' => trim((string) ($request->descricao ?? '')),
        ];
    }

    private function _validate(Request $request, int $empresaId): void
    {
        $this->validate($request, [
            'valor' => ['required'],
            'categoriaEcommerce_id' => ['required', Rule::exists('categoria_produto_ecommerces', 'id')->where(fn ($q) => $q->where('empresa_id', $empresaId))],
            'produto_id' => ['required', Rule::exists('produtos', 'id')->where(fn ($q) => $q->where('empresa_id', $empresaId)->where('inativo', 0))],
            'sub_categoriaEcommerce_id' => ['nullable', 'integer'],
            'percentual_desconto_view' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'descricao' => ['required', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'categoriaEcommerce_id.exists' => 'Categoria inválida para esta empresa.',
            'produto_id.exists' => 'Produto inválido, inativo ou pertencente a outra empresa.',
            'percentual_desconto_view.max' => 'O desconto visual não pode ultrapassar 100%.',
            'image.max' => 'A imagem deve possuir no máximo 5 MB.',
        ]);
    }
}