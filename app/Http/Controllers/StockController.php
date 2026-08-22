<?php

namespace App\Http\Controllers;

use App\Helpers\StockMove;
use App\Models\AlteracaoEstoque;
use App\Models\Apontamento;
use App\Models\ConfigNota;
use App\Models\Estoque;
use App\Models\Filial;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    protected $empresa_id;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $session = session('user_logged');
            if (!$session || empty($session['empresa'])) {
                return redirect('/login');
            }

            $this->empresa_id = (int) $session['empresa'];
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $estoqueTotal = Estoque::select('estoques.*')
        ->orderBy('estoques.updated_at', 'desc')
        ->where('estoques.empresa_id', $this->empresa_id)
        ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
        ->get();

        $data = Estoque::orderBy('estoques.updated_at', 'desc')
        ->where('produtos.empresa_id', $this->empresa_id)
        ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
        ->select('estoques.*')
                ->paginate(env("PAGINACAO"));

        $config = ConfigNota::where('empresa_id', $this->empresa_id)
        ->first();

        $totalProdutosEmEstoque = Estoque::select('estoques.*')
        ->where('produtos.empresa_id', $this->empresa_id)
        ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
        ->count();

        $somaEstoque = $this->somaEstoque($estoqueTotal);
        return view('estoque.index', compact('data', 'estoqueTotal', 'totalProdutosEmEstoque', 'somaEstoque'));
    }

public function zerarEstoque(Request $request)
{
    $empresa_id = $request->input('empresa_id');

    try {
        // Atualiza todas as quantidades para zero
        Estoque::where('empresa_id', $empresa_id)
            ->update(['quantidade' => 0]);

        // Opcional: criar log de alteraÃ§Ã£o
        AlteracaoEstoque::create([
            'empresa_id' => $empresa_id,
            'usuario_id' => get_id_user(),
            'quantidade' => 0,
            'observacao' => 'Estoque zerado manualmente',
            'tipo' => 2 // SaÃ­da total
        ]);

        session()->flash('flash_sucesso', 'Estoque da empresa zerado com sucesso!');
    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Erro ao zerar estoque: ' . $e->getMessage());
        __saveLogError($e, $empresa_id);
    }

    return redirect()->back();
}

    private function somaEstoque($estoque)
    {
        $somaVenda = 0;
        $somaCompra = 0;
        foreach ($estoque as $e) {
            if ($e->produto) {
                $somaVenda += $e->produto->valor_venda * $e->quantidade;
                $somaCompra += $e->valorCompra() * $e->quantidade;
            }
        }
        return [
            'compra' => $somaCompra,
            'venda' => $somaVenda
        ];
    }

    public function manual(Request $request)
    {
        return $this->create($request);
    }

  public function create(Request $request)
    {
        $estoqueTotal = Estoque::select('estoques.*')
            ->orderBy('estoques.updated_at', 'desc')
            ->where('estoques.empresa_id', $this->empresa_id)
            ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
            ->get();

        $data = Estoque::orderBy('estoques.updated_at', 'desc')
            ->where('produtos.empresa_id', $this->empresa_id)
            ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
            ->select('estoques.*')
                        ->paginate(env("PAGINACAO"));

        $config = ConfigNota::where('empresa_id', $this->empresa_id)
            ->first();

        $totalProdutosEmEstoque = Estoque::select('estoques.*')
            ->where('produtos.empresa_id', $this->empresa_id)
            ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
            ->count();

        $somaEstoque = $this->somaEstoque($estoqueTotal);

        return view('estoque.createApontamento', compact('data', 'estoqueTotal', 'totalProdutosEmEstoque', 'somaEstoque'));
    }
public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:0,1',
            'produto_id' => 'nullable|integer|required_without:codBarras',
            'codBarras' => 'nullable|string|max:60|required_without:produto_id',
            'quantidade' => 'nullable',
            'filial_id' => 'nullable|integer',
            'observacao' => 'nullable|string|max:200',
        ], [
            'produto_id.required_without' => 'Pesquise um produto pelo nome ou informe o código de barras.',
            'codBarras.required_without' => 'Pesquise um produto pelo nome ou informe o código de barras.',
        ]);

        $quantidade = $request->filled('quantidade')
            ? (float) __convert_value_bd($request->quantidade)
            : 1;

        if ($quantidade <= 0) {
            throw ValidationException::withMessages([
                'quantidade' => 'A quantidade deve ser maior que zero.',
            ]);
        }

        $produtoQuery = Produto::where('empresa_id', $this->empresa_id);

        if ($request->filled('produto_id')) {
            $produtoQuery->where('id', $request->produto_id);
        } else {
            $codigo = trim((string) $request->codBarras);
            $codigos = array_values(array_unique([
                $codigo,
                str_pad($codigo, 13, '0', STR_PAD_LEFT),
                str_pad($codigo, 14, '0', STR_PAD_LEFT),
                str_pad($codigo, 15, '0', STR_PAD_LEFT),
            ]));
            $produtoQuery->whereIn('codBarras', $codigos);
        }

        $produto = $produtoQuery->first();

        if (!$produto) {
            throw ValidationException::withMessages([
                'produto_id' => 'Produto não encontrado nesta empresa.',
            ]);
        }

        $filialId = $request->filled('filial_id') ? (int) $request->filial_id : null;

        if ($filialId && !Filial::where('id', $filialId)->where('empresa_id', $this->empresa_id)->exists()) {
            throw ValidationException::withMessages([
                'filial_id' => 'Local de estoque inválido para esta empresa.',
            ]);
        }

        DB::transaction(function () use ($request, $produto, $quantidade, $filialId) {
            $stockMove = new StockMove();
            $movimentou = (int) $request->tipo === 1
                ? $stockMove->pluStock($produto->id, $quantidade, -1, $filialId)
                : $stockMove->downStock($produto->id, $quantidade, $filialId, true);

            if (!$movimentou) {
                throw ValidationException::withMessages([
                    'quantidade' => 'Estoque insuficiente para realizar esta redução.',
                ]);
            }

            Apontamento::create([
                'empresa_id' => $this->empresa_id,
                'usuario_id' => get_id_user(),
                'produto_id' => $produto->id,
                'quantidade' => $quantidade,
            ]);

            AlteracaoEstoque::create([
                'empresa_id' => $this->empresa_id,
                'usuario_id' => get_id_user(),
                'produto_id' => $produto->id,
                'quantidade' => $quantidade,
                'observacao' => $request->observacao ?: '',
                'tipo' => (int) $request->tipo,
            ]);
        }, 3);

        session(['ultimo_tipo_apontamento' => (int) $request->tipo]);

        return redirect()
            ->route('estoque.create')
            ->with('flash_sucesso', 'Estoque atualizado com sucesso.');
    }
public function storeApontamento(Request $request)
{
    $this->_validateApontamento($request);

    $produtoId = $request->produto_id;

    $prod = Produto::find($produtoId);

    if (!$prod) {
        session()->flash('flash_erro', 'Produto não encontrado!');
        return redirect()->back();
    }

    $quantidade = str_replace(",", ".", $request->quantidade);

    // ==============================
    // 1. VALIDA ESTOQUE
    // ==============================
    $erroEstoque = $this->validaEstoqueDisponivel($prod, $quantidade);

    if ($erroEstoque != "") {
        session()->flash('flash_erro', $erroEstoque);
        return redirect()->back();
    }

    // ==============================
    // 2. CRIA APONTAMENTO
    // ==============================
    $result = Apontamento::create([
        'quantidade' => __convert_value_bd($request->quantidade),
        'usuario_id' => get_id_user(),
        'produto_id' => $produtoId,
        'empresa_id' => $this->empresa_id
    ]);

    // ==============================
    // 3. MOVIMENTAÇÃO ESTOQUE
    // ==============================
    $stockMove = new StockMove();

    $stockMove->pluStock(
        $produtoId,
        __convert_value_bd($request->quantidade),
        $prod->valor_venda
    );

    $this->downEstoquePorReceita($produtoId, $quantidade);

    // ==============================
    // 4. RETORNO
    // ==============================
    if ($result) {
        session()->flash("flash_sucesso", "Apontamento cadastrado com sucesso!");
    } else {
        session()->flash('flash_erro', 'Erro ao cadastrar apontamento!');
    }

    return redirect()->route('estoque.apontamentoProducao');
}

    private function validaEstoqueDisponivel($produto, $quantidade)
    {
        $msg = "";
        if ($produto->receita) {
            foreach ($produto->receita->itens as $i) {
                $qtd = $i->quantidade * $quantidade;
                if ($i->produto->estoqueAtual() < $qtd) {
                    $msg = "Estoque insuficiente do produto " . $i->produto->nome;
                }
            }
        }
        return $msg;
    }


    private function downEstoquePorReceita($idProduto, $quantidade)
    {
        $produto = Produto::where('id', $idProduto)
        ->first();
        if (valida_objeto($produto)) {
            $stockMove = new StockMove();
            if ($produto->receita) {
                foreach ($produto->receita->itens as $i) {
                    $stockMove->downStock($i->produto->id, $i->quantidade * $quantidade);
                }
            }
        } else {
            return redirect('/403');
        }
    }


    public function listaApontamento(Request $request)
    {
        $data = AlteracaoEstoque::where('empresa_id', $this->empresa_id)->get();
        return view('estoque.listaApontamento', compact('data'));
    }

    public function apontamentoProducao(Request $request)
    {
        $data = Apontamento::where('empresa_id', $this->empresa_id)->get();
        return view('estoque.apontamento_producao', compact('data'));
    }

    public function todosApontamentos(Request $request)
    {
        $data = Apontamento::where('empresa_id', $this->empresa_id)->get();
        return view('estoque.apontamento_todos', compact('data'));
    }

    private function _validateApontamento(Request $request)
    {
        $rules = [
            'produto_id' => 'required',
            'quantidade' => 'required',
        ];
        $messages = [
            'produto_id.required' => 'O campo produto Ã© obrigatÃ³rio.',
            'produto_id.min' => 'Clique sobre o produto desejado.',
            'quantidade.required' => 'O campo quantidade Ã© obrigatÃ³rio.',
            'quantidade.min' => 'Informe o valor do campo em casas decimais, ex: 1,000.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function setEstoqueLocais($produto_id)
    {
        $item = Produto::findOrFail($produto_id);
        $grade = Produto::produtosDaGrade($item->referencia_grade);
        $temp = json_decode($item->locais);
        $locais = [];
        foreach ($temp as $l) {
            if ($l == -1) {
                $locais[$l] = 'Matriz';
            } else {
                $filial = Filial::findOrFail($l);
                if ($filial != null) {
                    $locais[$l] = $filial->descricao;
                }
            }
        }
        return view('estoque.set_estoque_locais', compact('item', 'locais', 'grade'));
    }

    public function setEstoqueStore(Request $request)
    {
        $stockMove = new StockMove();
        try {
            $produto = Produto::findOrFail($request->produto_id);
            for ($i = 0; $i < sizeof($request->quantidade); $i++) {
                if (isset($request->produto_grade_id)) {
                    $produto = Produto::findOrFail($request->produto_grade_id[$i]);
                }
                $stockMove->pluStock(
                    $produto->id,
                    __convert_value_bd($request->quantidade[$i]),
                    -1,
                    $request->filial_id[$i]
                );
            }
            session()->flash('flash_sucesso', 'AÃ§Ã£o de estoque realizada!');
            if ($produto->composto == true) {
                return redirect()->route('produtosComposto.create', [$produto->id]);
            }
            return redirect()->route('estoque.index');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu  errado: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
