<?php

namespace App\Http\Controllers;

use App\Models\AlteracaoEstoque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\ConfigNota;
use App\Models\CategoriaProdutoEcommerce;
use App\Services\Gemini\ProductTaxAuditService;
use App\Models\Cidade;
use App\Models\DivisaoGrade;
use App\Models\Estoque;
use App\Models\Marca;
use App\Models\NaturezaOperacao;
use App\Models\ProdutoEcommerce;
use App\Models\SubCategoria;
use App\Models\Tributacao;
use App\Utils\UploadUtil;
use App\Utils\Util;
use Illuminate\Support\Str;
use App\Helpers\ProdutoGrade;
use App\Helpers\StockMove;
use App\Models\Etiqueta;
use App\Models\SubCategoriaEcommerce;
use App\Models\TelaPedido;
use Picqer;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProdutoImport;
use App\Http\Middleware\LimiteProdutos;
use App\Models\CategoriaProdutoDelivery;
use App\Models\ImagemProdutoEcommerce;
use App\Models\ImagensProdutoDelivery;
use App\Models\ProdutoDelivery;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Dompdf\Dompdf;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
class ProductController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->middleware(LimiteProdutos::class)->only('create');
        $this->util = $util;
        if (!is_dir(public_path('barcode'))) {
            mkdir(public_path('barcode'), 0777, true);
        }
    }

    public function index(Request $request)
    {
        $permissaoAcesso = __getLocaisUsarioLogado();
        $estoque = $request->estoque;
        $filial_id = $request->filial_id;
        $nome = $request->nome;
        $tipo = $request->tipo;
        $local_padrao = __get_local_padrao();
        if (!$filial_id && $local_padrao) {
            $filial_id = $local_padrao;
        }

        if (!$filial_id) {
            $filial_id = 'todos';
        }
        $categorias = Categoria::where('empresa_id', $request->empresa_id)->get();
        $marcas = Marca::where('empresa_id', $request->empresa_id)->get();
        $data = Produto::where('produtos.empresa_id', request()->empresa_id)
        ->when(!empty($nome), function ($query) use ($nome, $tipo) {
            return $query->where($tipo, 'LIKE', "%$nome%");
        })
        ->when(!empty($request->categoria_id), function ($q) use ($request) {
            return  $q->where(function ($quer) use ($request) {
                return $quer->where('categoria_id', 'LIKE', "%$request->categoria_id%");
            });
        })
        ->when(!empty($request->marca_id), function ($q) use ($request) {
            return  $q->where(function ($quer) use ($request) {
                return $quer->where('marca_id', 'LIKE', "%$request->marca_id%");
            });
        })
        ->paginate(env("PAGINACAO"));

        $isPaginate = 0;

        if ($estoque != 0) {
            $temp = [];
            foreach ($data as $p) {
                if ($estoque == 1) {
                    if ($p->estoque && $p->estoque->quantidade > 0) {
                        array_push($temp, $p);
                    }
                } else {
                    if (!$p->estoque || $p->estoque->quantidade < 0) {
                        array_push($temp, $p);
                    }
                }
            }
            $data = $temp;
            $isPaginate = 1;
        }
        $produtos = [];
        if ($filial_id != 'todos') {

            foreach ($data as $p) {
                $l = json_decode($p->locais);
                if (is_array($l)) {
                    if (in_array($filial_id, $l)) {
                        array_push($produtos, $p);
                    }
                }
            }
            $data = $produtos;
            $isPaginate = 1;
        }

        if ($isPaginate) {
            $data = $this->paginate($data);
        }

        return view('produtos.index', compact('data', 'categorias', 'marcas', 'filial_id'));
    }
    
      public function auditoriaTributaria(Request $request)
    {
        $data = Produto::with('categoria')
            ->where('empresa_id', $request->empresa_id)
            ->when($request->nome, fn ($query, $nome) => $query->where('nome', 'like', "%{$nome}%"))
            ->orderBy('nome')
            ->paginate(20);

        return view('produtos.auditoria_tributaria', compact('data'));
    }

    public function analisarTributacao(Request $request, ProductTaxAuditService $auditService)
    {
        $validated = $request->validate([
            'produto_ids' => ['required', 'array', 'min:1', 'max:20'],
            'produto_ids.*' => ['integer'],
        ], [
            'produto_ids.required' => 'Selecione pelo menos um produto.',
            'produto_ids.max' => 'Analise no máximo 20 produtos por vez.',
        ]);

        $products = Produto::with('categoria')
            ->where('empresa_id', $request->empresa_id)
            ->whereIn('id', $validated['produto_ids'])
            ->get();

        abort_if($products->count() !== count(array_unique($validated['produto_ids'])), 403);

        $results = [];
        foreach ($products as $product) {
            try {
                $results[] = ['produto' => $product, 'analise' => $auditService->audit($product), 'erro' => null];
            } catch (\Throwable $e) {
                report($e);
                $results[] = [
                    'produto' => $product,
                    'analise' => null,
                    'erro' => 'Serviço de auditoria Gemini indisponível. Verifique a GEMINI_API_KEY e tente novamente.',
                ];
            }
        }

        return view('produtos.auditoria_tributaria_resultado', compact('results'));
    }
public function alertasvencimento(Request $request)
{
    $empresa_id = $request->empresa_id ?? auth()->user()->empresa_id;
    $hoje = \Carbon\Carbon::today();
    $filial_id = $request->filial_id;
    $limite_maximo = $hoje->copy()->addDays(90);

    // Query Base: Apenas produtos com vencimento preenchido
    $query = Produto::where('empresa_id', $empresa_id)
        ->whereNotNull('vencimento')
        ->whereNotIn('vencimento', ['0000-00-00', '', 'null']);

    // Se NÃO houver filtro de status específico, mostra apenas Vencidos + Próximos 90 dias
    if (!$request->status_vencimento) {
        $query->whereDate('vencimento', '<=', $limite_maximo);
    }

    // Filtros de busca (Nome, Lote, Categoria)
    $query->when($request->nome, function ($q) use ($request) {
        $tipo = $request->tipo ?? 'nome';
        return $q->where($tipo, 'LIKE', "%{$request->nome}%");
    })->when($request->categoria_id, function ($q) use ($request) {
        return $q->where('categoria_id', $request->categoria_id);
    });

    // Filtros de Status Específicos
    if ($request->status_vencimento == 'vencido') {
        $query->whereDate('vencimento', '<', $hoje);
    } elseif ($request->status_vencimento == 'critico') {
        $query->whereDate('vencimento', '>=', $hoje)->whereDate('vencimento', '<=', $hoje->copy()->addDays(30));
    } elseif ($request->status_vencimento == 'atencao') {
        $query->whereDate('vencimento', '>', $hoje->copy()->addDays(30))->whereDate('vencimento', '<=', $limite_maximo);
    }

    // Totais corrigidos para os Cards (sempre baseados na regra de 90 dias)
    $contagem = [
        'vencidos' => Produto::where('empresa_id', $empresa_id)->whereDate('vencimento', '<', $hoje)->count(),
        'critico'  => Produto::where('empresa_id', $empresa_id)->whereDate('vencimento', '>=', $hoje)->whereDate('vencimento', '<=', $hoje->copy()->addDays(30))->count(),
        'atencao'  => Produto::where('empresa_id', $empresa_id)->whereDate('vencimento', '>', $hoje->copy()->addDays(30))->whereDate('vencimento', '<=', $limite_maximo)->count(),
    ];

    $data = $query->orderBy('vencimento', 'asc')->paginate(env("PAGINACAO", 20));
    $categorias = Categoria::where('empresa_id', $empresa_id)->get();
    $marcas = Marca::where('empresa_id', $empresa_id)->get();

    return view('produtos.alertasvencimento', compact('data', 'categorias', 'marcas', 'filial_id', 'contagem'));
}
    public function paginate($items, $perPage = 30, $page = null, $options = [])
    {
        $perPage = env("PAGINACAO");
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

public function etiquetaPersonalizadaAjax(Request $request, $id)
{
    $produto = Produto::findOrFail($id);
    $empresa = ConfigNota::where('empresa_id', $request->empresa_id)->first();
    $etiquetaConfig = Etiqueta::where('empresa_id', $request->empresa_id)->first();

    // 1. Definições de Cores
    $corFundo = $etiquetaConfig->cor_fundo ?? '#FFFFFF';
    $corFonte = $etiquetaConfig->cor_fonte ?? '#000000';
    
    // VARIÁVEIS DE DIMENSÃO FIXAS:
    $tamanhoFonteBase = 10;
    $alturaCodigoBarras = 30; 

    // 2. Geração do Código de Barras
    $generator = new BarcodeGeneratorPNG();
    $barcodeBase64 = base64_encode(
        $generator->getBarcode(
            $produto->codBarras ?? $produto->id, 
            $generator::TYPE_CODE_128,
            1, 
            $alturaCodigoBarras 
        )
    );

    // 3. Lógica da Logo
    $logoPath = public_path('uploads/configEmitente/' . ($empresa->logo ?? ''));
    if (!file_exists($logoPath) || empty($empresa->logo)) {
        $logoPath = public_path('imgs/mixk.png');
    }
    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

    // 4. HTML Reestruturado com Foco no Espaço e Logo Grande
    $html = '
        <div class="etiqueta-container" style="
            background-color: ' . $corFundo . '; 
            color: ' . $corFonte . '; 
            font-family: Arial, sans-serif; 
            font-size: ' . $tamanhoFonteBase . 'px; 
            padding: 1px; 
            box-sizing: border-box; 
            width: 100%; 
            height: 100%;
            overflow: hidden; 
            line-height: 1.1;
        ">
            
            <div style="clear: both; margin-bottom: 2px;">
                <img src="'.$logoBase64.'" 
                    alt="Logo da Empresa"
                    style="
                        float: left; /* Logo à esquerda */
                        max-width: 55%; /* AUMENTADO para 55% */
                        max-height: 32px; /* AUMENTADO para 32px */
                        object-fit: contain;
                        margin-right: 2px;
                    ">

                <div class="etiqueta-produto" style="
                    font-weight: bold; 
                    font-size: ' . ($tamanhoFonteBase + 2) . 'px; /* 12px (Levemente maior) */
                    text-align: right; 
                    line-height: 1.1;
                    overflow: hidden;
                    max-height: 3.5em; /* Permite um pouco mais de texto */
                ">
                    '.mb_strtoupper($produto->nome).'
                </div>
            </div>
            
            <div style="clear: both;"></div> 

            <div class="etiqueta-price-box" style="
                text-align: center;
                background-color: ' . $corFonte . '; 
                color: ' . $corFundo . '; 
                padding: 1px 0; 
                margin: 2px 0; 
                border-radius: 2px;
            "> 
                <div style="font-size: ' . ($tamanhoFonteBase * 0.7) . 'px; font-weight: 300; line-height: 1;">PREÇO EXCLUSIVO</div>
                <div class="etiqueta-preco" style="
                    font-size: ' . ($tamanhoFonteBase * 2.5) . 'px; /* 25px */
                    font-weight: 900; 
                    line-height: 1;
                ">
                    R$ '.number_format($produto->valor_venda, 2, ',', '.').'
                </div>
            </div>

            <div class="etiqueta-barcode" style="text-align: center; margin-top: 1px;">
                <img src="data:image/png;base64,'.$barcodeBase64.'" 
                    class="etiqueta-barcode-img" 
                    alt="Código de Barras"
                    style="max-width: 95%; height: auto; display: block; margin: 0 auto; ">
                    
                <div class="etiqueta-barcode-text" style="
                    font-size: ' . ($tamanhoFonteBase * 0.7) . 'px; 
                    margin-top: 0px;
                ">
                    '.($produto->codBarras ?? $produto->id).'
                </div>
            </div>
            
            <div class="etiqueta-rodape" style="
                text-align: center; 
                font-size: ' . ($tamanhoFonteBase * 0.6) . 'px; 
                margin-top: 1px; 
                color: ' . $corFonte . '; 
                opacity: 0.7; 
                line-height: 1;
            ">
                '.($empresa->razao_social ?? 'EMPRESA').' | CNPJ: '.($empresa->cnpj ?? '00.000.000/0000-00').'
            </div>
        </div>
    ';

    return response($html);
}
    public function create(Request $request)
    {
        $dataValidate = [
            'categorias', 'tributacaos', 'natureza_operacaos'
        ];
        $util = new Util();
        $validateEntry = $util->validateEntry($dataValidate, $request->empresa_id);
        if ($validateEntry != null) {
            session()->flash("flash_warning", $validateEntry['message']);
            return redirect($validateEntry['route']);
        }
        $cidades = Cidade::all();
        $marcas = Marca::where('empresa_id', request()->empresa_id)->get();
        $categorias = Categoria::where('empresa_id', $request->empresa_id)
        ->get();
        $naturezaPadrao = NaturezaOperacao::where('empresa_id', $request->empresa_id)
        ->first();
        $tributacao = Tributacao::where('empresa_id', request()->empresa_id)
        ->first();
        $configNf = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        if ($configNf == null) {
            session()->flash('flash_warning', 'Defina a configuração do emitente');
            return redirect()->route('configNF.index');
        }

        $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', $request->empresa_id)->get();
        // if ($categoriasEcommerce == null) {
        //     session()->flash('flash_warning', 'Cadastrar categoria de Ecommerce antes de continuar');
        //     return redirect()->route('categoriaEcommerce.index');
        // }
        $subs = SubCategoria::select('sub_categorias.*')
        ->join('categorias', 'categorias.id', '=', 'sub_categorias.categoria_id')
        ->where('empresa_id', $request->empresa_id)
        ->get();

        $subDivisoes = DivisaoGrade::where('empresa_id', $request->empresa_id)
        ->where('sub_divisao', true)
        ->get();

        $telasPedido = TelaPedido::where('empresa_id', request()->empresa_id)->get();
        $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
        $natureza = Produto::firstNatureza($request->empresa_id);
        return view('produtos.create', compact(
            'cidades',
            'categorias',
            'marcas',
            'naturezaPadrao',
            'tributacao',
            'categoriasEcommerce',
            'divisoes',
            'configNf',
            'subs',
            'subDivisoes',
            'telasPedido'
        ));
    }

    public function edit($id)
    {
        $item = Produto::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $categorias = Categoria::where('empresa_id', request()->empresa_id)->get();

        $subcategorias = SubCategoria::select('sub_categorias.*')
        ->join('categorias', 'categorias.id', '=', 'sub_categorias.categoria_id')
        ->where('empresa_id', request()->empresa_id)
        ->get();

        $marcas = Marca::where('empresa_id', request()->empresa_id)->get();
        $naturezaPadrao = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        $tributacaos = Tributacao::where('empresa_id', request()->empresa_id)->get();
        $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', request()->empresa_id)->get();

        $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
        ->where('sub_divisao', true)
        ->get();
        $telasPedido = TelaPedido::where('empresa_id', request()->empresa_id)->get();
        return view('produtos.edit', compact(
            'item',
            'categorias',
            'subcategorias',
            'marcas',
            'naturezaPadrao',
            'tributacaos',
            'categoriasEcommerce',
            'divisoes',
            'subDivisoes',

            'telasPedido'
        ));
    }

    public function store(Request $request)
    {
        $this->_validate($request);

        if ($request->tamanho_grade) {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/products');
            }
            $produtoGrade = new ProdutoGrade();
            $res = $produtoGrade->store($request, $file_name);
            if ($res == "ok") {
                session()->flash("flash_sucesso", "Produto cadastrado como grade!");
            } else {
                session()->flash('flash_erro', 'Erro ao cadastrar produto, confira a grade!');
            }

            $locais = isset($request->local) ? $request->local : [];

            if (sizeof($locais) > 0) {
                $lastProduto = Produto::where('empresa_id', request()->empresa_id)
                ->orderBy('id', 'desc')
                ->first();
                session()->flash("flash_sucesso", "Produto cadastrado com sucesso, informe o estoque");
                return redirect()->route('produtos.set-estoque', [$lastProduto->id]);
            }
            return redirect()->route('produtos.index');
        } else {
            try {
                $locais = json_encode($request->local);
                if ($request->local == null) {
                    $locais = "[-1]";
                }
                $file_name = '';
                if ($request->hasFile('image')) {
                    $file_name = $this->util->uploadImage($request, '/products');
                }
                $request->merge([
                    'valor_compra' =>  __convert_value_bd($request->valor_compra),
                    'valor_venda' => __convert_value_bd($request->valor_venda),
                    'referencia' => $request->referencia ?? '',
                    'estoque_inicial' => $request->estoque_inicial ?? 0,
                    'estoque_minimo' => $request->estoque_minimo ?? 0,
                    'cor' => $request->cor ?? 0,
                    'valor_livre' => $request->valor_livre ?? false,
                    'cListServ' => $request->cListServ ?? '',
                    'descricao_anp' => $request->descricao_anp ?? '',
                    'imagem' => $file_name,
                    'info_tecnica_composto' => $request->info_tecnica_composto ?? '',
                    'limite_maximo_desconto' => $request->limite_maximo_desconto ?? 0,
                    'alerta_vencimento' => $request->alerta_vencimento ?? 0,
                    'CEST' => $request->CEST ?? '',
                    'referencia_balanca' => $request->referencia_balanca ?? 0,
                    'perc_comissao' => $request->perc_comissao ?? 0,
                    'tipo_dimensao' => $request->tipo_dimensao ?? '',
                    'perc_glp' => $request->perc_glp ?? 0,
                    'perc_gnn' => $request->perc_gnn ?? 0,
                    'perc_gni' => $request->perc_gni ?? 0,
                    'conversao_unitaria' => $request->conversao_unitaria ?? 1,
                    'valor_partida' => $request->valor_partida ?? 0,
                    'unidade_tributavel' => $request->unidade_tributavel ?? '',
                    'quantidade_tributavel' => $request->quantidade_tributavel ?? 0,
                    'largura' => $request->largura ?? 0,
                    'altura' => $request->altura ?? 0,
                    'comprimento' => $request->comprimento ?? 0,
                    'peso_liquido' => $request->peso_liquido ?? 0,
                    'peso_bruto' => $request->peso_bruto ?? 0,
                    'lote' => $request->lote ?? 0,
                    'vencimento' => $request->vencimento ?? '',
                    'renavam' => $request->renavam ?? '',
                    'placa' => $request->placa ?? '',
                    'chassi' => $request->chassi ?? '',
                    'combustivel' => $request->combustivel ?? '',
                    'ano_modelo' => $request->ano_modelo ?? '',
                    'cor_veiculo' => $request->cor_veiculo ?? '',
                    'perc_ipi' => $request->perc_ipi ?? 0,
                    'codBarras' => $request->codBarras ?? 0,
                    'perc_iss' => $request->perc_iss ?? 0,
                    'cBenef' => $request->cBenef ?? 0,
                    'perc_icms_interestadual' => $request->perc_icms_interestadual ?? 0,
                    'perc_icms_interno' => $request->perc_icms_interno ?? 0,
                    'perc_fcp_interestadual' => $request->perc_fcp_interestadual ?? 0,
                    'alerta_vencimento' => $request->alerta_vencimento ?? 0,
                    'perc_reducao' => $request->perc_reducao ?? 0,
                    'grade' => false,
                    'referencia_grade' => Str::random(20),
                    'str_grade' => '',
                    'valor_locacao' => $request->valor_locacao ?? 0,
                    'tela_pedido_id' => $request->tela_pedido_id ?? 0,
                    'locais' => $locais
                ]);
                $prod = DB::transaction(function () use ($request) {
                    $item = Produto::create($request->all());
                    if ($request->estoque_inicial > 0) {
                        Estoque::create([
                            'empresa_id' => $request->empresa_id,
                            'produto_id' => $item->id,
                            'quantidade' => $request->estoque_inicial,
                            'valor_compra' => $request->valor_compra,
                        ]);
                    }
                    return $item;
                });

                if ($request->delivery) {
                    $this->salvarProdutoNoDelivery($request, $prod, $file_name);
                }

                if ($request->ecommerce) {
                    $this->salvarProdutoEcommerce($request, $prod, $file_name);
                }

                // return redirect('/produtos');
                $locais = isset($request->local) ? $request->local : [];

                session()->flash("flash_sucesso", "Produto cadastrado!");
                if (sizeof($locais) > 0) {
                    session()->flash("flash_sucesso", "Produto cadastrado com sucesso, informe o estoque");
                    return redirect()->route('produtos.set-estoque', [$prod->id]);
                } elseif ($request->composto) {
                    session()->flash("flash_sucesso", "Produto cadastrado com sucesso, Informe a composição!");
                    return redirect()->route('produtosComposto.create', [$prod->id]);
                } else {
                    return redirect()->route('produtos.index');
                }
            } catch (\Exception $e) {
                // echo $e->getMessage();
                // die;
                session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
         
        $previous = $request->input('previous_url', route('produtos.index'));
return redirect($previous);
        
}
        }
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required',
            'valor_venda' => 'required',
            'valor_compra' => 'required',
            'NCM' => 'required',
            'categoria_id' => 'required',
            'percentual_lucro' => 'required',
            'unidade_compra' => 'required',
            'unidade_venda' => 'required',
            'CFOP_saida_estadual' => 'required',
            'CFOP_saida_inter_estadual' => 'required',
            'CFOP_entrada_inter_estadual' => 'required',
            'CFOP_entrada_estadual' => 'required'
        ];
        $messages = [
            'nome.required' => 'Campo Obrigatório',
            'valor_venda.required' => 'Campo Obrigatório',
            'NCM.required' => 'Campo Obrigatório',
            'valor_compra.required' => 'Campo Obrigatório',
            'categoria_id.required' => 'Campo Obrigatório',
            'percentual_lucro.required' => 'Campo Obrigatório',
            'unidade_compra.required' => 'Campo Obrigatório',
            'unidade_venda.required' => 'Campo Obrigatório',
            'CFOP_saida_estadual.required' => 'Campo Obrigatório',
            'CFOP_saida_inter_estadual.required' => 'Campo Obrigatório',
            'CFOP_entrada_inter_estadual.required' => 'Campo Obrigatório',
            'CFOP_entrada_estadual.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }


    public function update(Request $request, $id)
{
    $this->_validate($request); // Valida os dados obrigatórios
    $item = Produto::findOrFail($id);

    try {
        // 1. Upload de imagem (substitui a anterior se houver)
        $file_name = $item->imagem;
        if ($request->hasFile('image')) {
            $this->util->unlinkImage($item, '/products');
            $file_name = $this->util->uploadImage($request, '/products');
        }

        // 2. Locais
        $locais = json_encode($request->local);
        if ($request->local == null) {
            $locais = "[-1]";
        }

        // 3. Prepara dados para salvar
        $request->merge([
            'valor_venda' => __convert_value_bd($request->valor_venda),
            'valor_compra' =>  __convert_value_bd($request->valor_compra),
            'referencia' => $request->referencia ?? '',
            'estoque_inicial' => $request->estoque_inicial ?? 0,
            'estoque_minimo' => $request->estoque_minimo ?? 0,
            'cor' => $request->cor ?? 0,
            'valor_livre' => $request->valor_livre ?? false,
            'cListServ' => $request->cListServ ?? '',
            'descricao_anp' => $request->descricao_anp ?? '',
            'imagem' => $file_name,
            'info_tecnica_composto' => $request->info_tecnica_composto ?? '',
            'limite_maximo_desconto' => $request->limite_maximo_desconto ?? 0,
            'alerta_vencimento' => $request->alerta_vencimento ?? 0,
            'CEST' => $request->CEST ?? '',
            'referencia_balanca' => $request->referencia_balanca ?? 0,
            'perc_comissao' => $request->perc_comissao ?? 0,
            'tipo_dimensao' => $request->tipo_dimensao ?? '',
            'perc_glp' => $request->perc_glp ?? 0,
            'perc_gnn' => $request->perc_gnn ?? 0,
            'perc_gni' => $request->perc_gni ?? 0,
            'valor_partida' => $request->valor_partida ?? 0,
            'unidade_tributavel' => $request->unidade_tributavel ?? '',
            'quantidade_tributavel' => $request->quantidade_tributavel ?? 0,
            'largura' => $request->largura ?? 0,
            'altura' => $request->altura ?? 0,
            'comprimento' => $request->comprimento ?? 0,
            'peso_liquido' => $request->peso_liquido ?? 0,
            'peso_bruto' => $request->peso_bruto ?? 0,
            'lote' => $request->lote ?? 0,
            'vencimento' => $request->vencimento ?? '',
            'renavam' => $request->renavam ?? '',
            'placa' => $request->placa ?? '',
            'chassi' => $request->chassi ?? '',
            'combustivel' => $request->combustivel ?? '',
            'ano_modelo' => $request->ano_modelo ?? '',
            'cor_veiculo' => $request->cor_veiculo ?? '',
            'perc_ipi' => $request->perc_ipi ?? 0,
            'codBarras' => $request->codBarras ?? 0,
            'perc_reducao' => $request->perc_reducao ?? 0,
            'perc_iss' => $request->perc_iss ?? 0,
            'cBenef' => $request->cBenef ?? 0,
            'perc_icms_interestadual' => $request->perc_icms_interestadual ?? 0,
            'perc_icms_interno' => $request->perc_icms_interno ?? 0,
            'perc_fcp_interestadual' => $request->perc_fcp_interestadual ?? 0,
            'locais' => $locais
        ]);

        // 4. Salva dados do produto
        $item->fill($request->all())->save();

        // 5. Atualiza ou cria estoque
        if ($request->estoque_inicial > 0) {
            $estoque = Estoque::where('empresa_id', $request->empresa_id)
                              ->where('produto_id', $item->id)
                              ->first();

            if ($estoque) {
                // Atualiza quantidade (sobrescrevendo)
                $estoque->quantidade = $request->estoque_inicial;
                $estoque->valor_compra = $request->valor_compra;
                $estoque->save();
            } else {
                // Cria novo estoque
                Estoque::create([
                    'empresa_id' => $request->empresa_id,
                    'produto_id' => $item->id,
                    'quantidade' => $request->estoque_inicial,
                    'valor_compra' => $request->valor_compra,
                ]);
            }
        }

        // 6. Integração Delivery
        if ($request->delivery) {
            $this->salvarProdutoNoDelivery($request, $item, $file_name);
        }

        // 7. Integração Ecommerce
        if ($request->ecommerce) {
            $this->salvarProdutoEcommerce($request, $item, $file_name);
        }

        // 8. Mensagem de sucesso
        session()->flash("flash_sucesso", "Produto editado e estoque atualizado!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }

    // 9. Redireciona para página anterior ou index
    $previous = $request->input('previous_url', route('produtos.index'));
    return redirect($previous);
}

public function destroy(Request $request, $id)
{
    $item = Produto::findOrFail($id);
    
    if (!__valida_objeto($item)) {
        abort(403);
    }

    // Verifica se existe algum item de venda associado
    $emUso = \App\Models\ItemVendaCaixa::where('produto_id', $id)->exists();

    if ($emUso) {
        session()->flash("flash_erro", "Este produto não pode ser excluído pois está vinculado a uma venda.");
        return redirect()->to($request->input('previous_url', route('produtos.index')));
    }

    try {
        $item->delete();
        session()->flash("flash_sucesso", "Produto deletado!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, $request->empresa_id ?? null);
    }

    // Redireciona de volta com filtros preservados
    return redirect()->to($request->input('previous_url', route('produtos.index')));
}


    public function movimentacao(Request $request, $id)
    {
        $item = Produto::findOrFail($id);
        $movimentacoes = $item->movimentacoes();

        return view('produtos.movimentacao', compact('item', 'movimentacoes'));
    }

    public function movimentacaoPrint($id){
        $produto = Produto::findOrFail($id);


        $movimentacoes = $produto->movimentacoes();

        $p = view('produtos.relatorio_movimentacoes')
        ->with('produto', $produto)
        ->with('title', 'Relatório de movimentações')
        ->with('movimentacoes', $movimentacoes);

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de movimentações.pdf", array("Attachment" => false));

    }

    public function getUnidadesMedida()
    {
        $unidades = Produto::unidadesMedida();
        echo json_encode($unidades);
    }

    public function duplicar($id)
    {
        $natureza = Produto::firstNatureza(request()->empresa_id);
        $anps = Produto::lista_ANP();
        if ($natureza == null) {
            session()->flash('flash_sucesso', 'Cadastre uma natureza de operação!');
            return redirect()->route('configNF.index');
        }
        $produto = new Produto();
        // $listaCSTCSOSN = Produto::listaCSTCSOSN();
        $listaCST_PIS_COFINS = Produto::listaCST_PIS_COFINS();
        $listaCST_IPI = Produto::listaCST_IPI();
        $categorias = Categoria::where('empresa_id', request()->empresa_id)
        ->get();
        $unidadesDeMedida = Produto::unidadesMedida();
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        $tributacao = Tributacao::where('empresa_id', request()->empresa_id)
        ->first();
        $item = $produto
        ->where('id', $id)->first();
        $categoriasDelivery = [];
        if ($tributacao->regime == 1) {
            $listaCSTCSOSN = Produto::listaCST();
        } else {
            $listaCSTCSOSN = Produto::listaCSOSN();
        }
        if ($tributacao == null) {
            session()->flash('flash_erro', 'Informe a tributação padrão!');
            return redirect('tributos');
        }
        $marcas = Marca::where('empresa_id', request()->empresa_id)
        ->get();
        $subs = SubCategoria::select('sub_categorias.*')
        ->join('categorias', 'categorias.id', '=', 'sub_categorias.categoria_id')
        ->where('empresa_id', request()->empresa_id)
        ->get();
        $subsEcommerce = SubCategoriaEcommerce::select('sub_categoria_ecommerces.*')
        ->join('categoria_produto_ecommerces', 'categoria_produto_ecommerces.id', '=', 'sub_categoria_ecommerces.categoria_id')
        ->where('empresa_id', request()->empresa_id)
        ->get();
        $divisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
        ->where('sub_divisao', false)
        ->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
        ->where('sub_divisao', true)
        ->get();
        $telasPedido = TelaPedido::where('empresa_id', request()->empresa_id)->get();
        return view('produtos.duplicar', compact(
            'item',
            'config',
            'marcas',
            'divisoes',
            'subDivisoes',
            'subs',
            'subsEcommerce',
            'tributacao',
            'natureza',
            'listaCSTCSOSN',
            'listaCST_PIS_COFINS',
            'listaCST_IPI',
            'categoriasDelivery',
            'anps',
            'unidadesDeMedida',
            'categorias',
            'telasPedido'
        ));
    }

    public function etiqueta($id)
    {
        try {
            $padrosEtiqueta = Etiqueta::where('empresa_id', null)
            ->orWhere('empresa_id', request()->empresa_id)
            ->get();
            $item = Produto::findOrFail($id);
            return view('produtos.etiqueta', compact('padrosEtiqueta', 'item'));
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function montaEtiqueta(Request $request)
    {
        $produto = Produto::findOrFail($request->produto_id);
        try {
            $files = glob(public_path("barcode/*"));
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            $nome = $produto->nome . " " . $produto->str_grade;
            $codigo = $produto->codBarras;
            $valor = $produto->valor_venda;
            $unidade = $produto->unidade_venda;
            if ($codigo == "" || $codigo == "SEM GTIN" || $codigo == "sem gtin") {
                session()->flash('flash_erro', 'Produto sem código de barras definido');
                return redirect()->back();
            }

            $data = [
                'nome_empresa' => $request->nome_empresa ? true : false,
                'nome_produto' => $request->nome_produto ? true : false,
                'valor_produto' => $request->valor_produto ? true : false,
                'cod_produto' => $request->cod_produto ? true : false,
                'codigo_barras_numerico' => $request->codigo_barras_numerico ? true : false,
                'nome' => $nome,
                'codigo' => $produto->id . ($produto->referencia != '' ? ' | REF' . $produto->referencia : ''),
                'valor' => $valor,
                'unidade' => $unidade,
                'empresa' => $produto->empresa->nome
            ];

            $rand = rand(1000, 9999);

            $generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();

            $bar_code = $generatorPNG->getBarcode($codigo, $generatorPNG::TYPE_EAN_13);

            file_put_contents(public_path("barcode") . "/$rand.png", $bar_code);
            $qtdLinhas = $request->etiquestas_por_linha;
            $qtdTotal = $request->quantidade_etiquetas;

            return view('produtos.print')
            ->with('altura', $request->altura)
            ->with('largura', $request->largura)
            ->with('rand', $rand)
            ->with('codigo', $codigo)
            ->with('quantidade', $qtdTotal)
            ->with('distancia_topo', $request->dist_topo)
            ->with('distancia_lateral', $request->dist_lateral)
            ->with('quantidade_por_linhas', $qtdLinhas)
            ->with('tamanho_fonte', $request->tamanho_fonte)
            ->with('tamanho_codigo', $request->tamanho_codigo)
            ->with('data', $data);
        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>' . $e->getLine();
            die;
            session()->flash('flash_erro', 'Erro: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    private function _validateEtiqueta(Request $request)
    {
        $rules = [
            'largura' => 'required',
            'altura' => 'required',
            'qtd_linhas' => 'required',
            'dist_lateral' => 'required',
            'dist_topo' => 'required',
            'qtd_etiquetas' => 'required',
            'tamanho_fonte' => 'required',
            'tamanho_codigo' => 'required',
        ];
        $messages = [
            'largura.required' => 'Campo obrigatório.',
            'altura.required' => 'Campo obrigatório.',
            'qtd_linhas.required' => 'Campo obrigatório.',
            'dist_lateral.required' => 'Campo obrigatório.',
            'dist_topo.required' => 'Campo obrigatório.',
            'qtd_etiquetas.required' => 'Campo obrigatório.',
            'tamanho_fonte.required' => 'Campo obrigatório.',
            'tamanho_codigo.required' => 'Campo obrigatório.',
        ];
        $this->validate($request, $rules, $messages);
    }


    public function import()
    {
        return view('produtos.import');
    }

    public function downloadModelo()
    {
        try {
            return response()->download(public_path('files/') . 'import_products_csv_template.xlsx');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function importStore(Request $request)
    {
        if ($request->hasFile('file')) {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);
            $locais = json_encode($request->local);
            if ($request->local == null) {
                $locais = "[-1]";
            }
            $rows = Excel::toArray(new ProdutoImport, $request->file);
            $retornoErro = $this->validaArquivo($rows);
            // $retornoErro = "";
            if ($retornoErro == "") {
                //armazenar no bd
                $teste = [];
                $tributacao = Tributacao::where('empresa_id', request()->empresa_id)->first();
                $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
                $categoria = Categoria::where('empresa_id', request()->empresa_id)->first();
                $cont = 0;
                foreach ($rows as $row) {
                    foreach ($row as $key => $r) {
                        if ($r[0] != 'NOME' && $r[0] != 'NOME*') {
                            try {
                                $objeto = $this->preparaObjeto($r, $tributacao, $config, $categoria->id, $locais);
                                if ($objeto != null) {
                                    if ($objeto['categoria'] != '') {
                                        $cat = Categoria::where('nome', $objeto['categoria'])
                                        ->where('empresa_id', request()->empresa_id)
                                        ->first();
                                        if ($cat == null) {
                                            $cat = Categoria::create(
                                                [
                                                    'nome' => $objeto['categoria'],
                                                    'empresa_id' => request()->empresa_id
                                                ]
                                            );
                                            $objeto['categoria_id'] = $cat->id;
                                        } else {
                                            $objeto['categoria_id'] = $cat->id;
                                        }
                                    } else {
                                        $objeto['categoria_id'] = $categoria->id;
                                    }
                                    $prod = Produto::create($objeto);
                                    if ($objeto['estoque'] > 0) {
                                        $stockMove = new StockMove();
                                        $result = $stockMove->pluStock(
                                            $prod->id,
                                            $objeto['estoque'],
                                            str_replace(",", ".", $prod->valor_venda)
                                        );
                                    }
                                    $cont++;
                                }
                            } catch (\Exception $e) {
                                // echo $e->getMessage() . ", linha: " . $e->getLine();
                                // die;
                                session()->flash('flash_erro', $e->getMessage());
                                return redirect()->back();
                            }
                        }
                    }
                }
                session()->flash('flash_sucesso', "Produtos inseridos: $cont!!");
                return redirect('/produtos');
            } else {
                session()->flash('flash_erro', $retornoErro);
                return redirect()->back();
            }
        } else {
            session()->flash('flash_erro', 'Nenhum Arquivo!!');
            return redirect()->back();
        }
    }


    private function validaNumero($numero)
    {
        if (strlen($numero) == 1) {
            return "0" . $numero;
        }
        return $numero;
    }

    private function preparaObjeto($r, $tributacao, $config, $categoria, $locais)
    {
        if (trim($r[0]) == "") {
            return null;
        }

        $arr = [
            'nome' => $r[0],
            'categoria' => $r[2],
            'cor' => $r[1] ?? '',
            'valor_venda' => __convert_value_bd($r[3]),
            'NCM' => $r[5] != "" ? $r[5] : $tributacao->ncm_padrao,
            'CEST' => $r[7] ?? '',
            'CST_CSOSN' => $r[8] != "" ? $this->validaNumero($r[8]) : $config->CST_CSOSN_padrao,
            'CST_PIS' => $r[9] != "" ? $this->validaNumero($r[9]) : $config->CST_PIS_padrao,
            'CST_COFINS' => $r[10] != "" ? $this->validaNumero($r[10]) : $config->CST_COFINS_padrao,
            'CST_IPI' => $r[11] != "" ? $r[11] : $config->CST_IPI_padrao,
            'unidade_compra' => $r[12] != "" ? $r[12] : 'UN',
            'unidade_venda' => $r[13] != "" ? $r[13] : 'UN',
            'composto' => $r[15] != "" ? $r[15] : 0,
            'codBarras' => $r[6] != "" ? $r[6] : 'SEM GTIN',
            'conversao_unitaria' => $r[14] != "" ? $r[14] : 1,
            'valor_livre' => $r[16] != "" ? $r[16] : 0,
            'perc_icms' => $r[17] != "" ? $r[17] : $tributacao->icms,
            'perc_pis' => $r[18] != "" ? $r[18] : $tributacao->pis,
            'perc_cofins' => $r[19] != "" ? $r[19] : $tributacao->cofins,
            'perc_ipi' => $r[20] != "" ? $r[20] : $tributacao->ipi,
            'CFOP_saida_estadual' => $r[22] != "" ? $r[22] : '5101',
            'CFOP_saida_inter_estadual' => $r[23] != "" ? $r[23] : '6101',
            'codigo_anp' => $r[24] ?? '',
            'descricao_anp' => $r[25] ?? '',
            'perc_iss' => $r[21] ?? 0,
            'cListServ' => '',
            'imagem' => '',
            'alerta_vencimento' => $r[26] != "" ? $r[26] : 0,
            'valor_compra' => __convert_value_bd($r[4]),
            'gerenciar_estoque' => $r[27] != "" ? $r[27] : 0,
            'estoque_minimo' => $r[28] != "" ? $r[28] : 0,
            'referencia' => $r[29] ?? '',
            'empresa_id' => request()->empresa_id,
            'largura' => $r[30] != "" ? $r[30] : 0,
            'comprimento' => $r[31] != "" ? $r[31] : 0,
            'altura' => $r[32] != "" ? $r[32] : 0,
            'peso_liquido' => $r[33] != "" ? $r[33] : 0,
            'peso_bruto' => $r[34] != "" ? $r[34] : 0,
            'limite_maximo_desconto' => $r[35] != "" ? $r[35] : 0,
            'perc_reducao' => $r[36] ?? '',
            'cBenef' => $r[37] ?? '',
            'percentual_lucro' => 0,
            'CST_CSOSN_EXP' => '',
            'referencia_grade' => Str::random(20),
            'grade' => 0,
            'str_grade' => '',
            'perc_glp' => 0,
            'perc_gnn' => 0,
            'perc_gni' => 0,
            'valor_partida' => 0,
            'unidade_tributavel' => '',
            'quantidade_tributavel' => 0,
            'perc_icms_interestadual' => 0,
            'perc_icms_interno' => 0,
            'perc_fcp_interestadual' => 0,
            'inativo' => 0,
            'estoque' => $r[38] != "" ? $r[38] : 0,
            'locais' => $locais
        ];
        return $arr;
    }

    private function validaArquivo($rows)
    {
        $cont = 0;
        $msgErro = "";
        foreach ($rows as $row) {
            foreach ($row as $key => $r) {

                $nome = $r[0];
                $valorVenda = $r[3];
                $valorCompra = $r[4];

                if (strlen($nome) == 0) {
                    $msgErro .= "Coluna nome em branco na linha: $cont | ";
                }

                if (strlen($valorVenda) == 0) {
                    $msgErro .= "Coluna valor venda em branco na linha: $cont | ";
                }

                if (strlen($valorCompra) == 0) {
                    $msgErro .= "Coluna valor compra em branco na linha: $cont";
                }

                if ($msgErro != "") {
                    return $msgErro;
                }
                $cont++;
            }
        }

        return $msgErro;
    }


    public function exportacaoBalanca()
    {
        $data = Produto::where('referencia_balanca', '!=', '')
        ->get();
        return view('produtos.balanca', compact('data'));
    }

    public function exportacaoBalancaFile(Request $request)
    {
        if (sizeof($request->produto_id) == 0) {
            session()->flash('flash_warning', "Selecione ao menos um produto!");
            return redirect()->back();
        }
        $fileStr = "";
        for ($i = 0; $i < sizeof($request->produto_id); $i++) {
            $produto = Produto::findOrFail($request->produto_id[$i]);
            $fileStr .= "0101";
            $referencia_balanca = $produto->referencia_balanca;
            for ($j = 0; $j < (7 - strlen($referencia_balanca)); $j++) {
                $fileStr .= "0";
            }
            $fileStr .= $produto->referencia_balanca;
            $vl = str_replace(".", "", number_format($produto->valor_venda, 2));
            for ($j = 0; $j < (6 - strlen($vl)); $j++) {
                $fileStr .= "0";
            }
            $fileStr .= $vl;
            $vencimento = \Carbon\Carbon::parse(str_replace("/", "-", $produto->vencimento))->format('Y-m-d');
            $dataHoje = date('Y-m-d');
            $dif = strtotime($vencimento) - strtotime($dataHoje);
            $dias = floor($dif / (60 * 60 * 24));
            $dias = $produto->alerta_vencimento;
            for ($j = 0; $j < (3 - strlen($dias)); $j++) {
                $fileStr .= "0";
            }
            $fileStr .= $dias;
            $fileStr .= $this->retiraAcentos($produto->nome);
            $fileStr .= "\n";
        }
        $modelo = $request->modelo;
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-disposition: attachment; filename=TXITENS.txt');
        echo $fileStr;
    }

    private function retiraAcentos($texto)
    {
        return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/"), explode(" ", "a A e E i I o O u U n N c"), $texto);
    }


    private function salvarProdutoNoDelivery($request, $produto, $file_name)
    {
        $categoria = CategoriaProdutoDelivery::where('empresa_id', $request->empresa_id)->first();
        $valor = __convert_value_bd($request->valor_venda);
        $produtoDelivery = [
            'status' => 1,
            'produto_id' => $produto->id,
            'descricao' => $request->nome ?? '',
            'ingredientes' => '',
            'limite_diario' => -1,
            'categoria_id' => $categoria->id,
            'valor' => $valor,
            'valor_anterior' => 0,
            'referencia' => '',
            'empresa_id' => $request->empresa_id
        ];
        $result = ProdutoDelivery::create($produtoDelivery);
        $produtoDelivery = ProdutoDelivery::find($result->id);

        if ($result) {
            $this->salveImagemProdutoDelivery($file_name, $produtoDelivery);
        }
    }

    private function salveImagemProdutoDelivery($file_name, $produtoDelivery)
    {
        if ($file_name != "") {
            copy(public_path('uploads/products/') . $file_name, public_path('uploads/produtoDelivery/') . $file_name);

            ImagensProdutoDelivery::create(
                [
                    'produto_id' => $produtoDelivery->id,
                    'path' => $file_name
                ]
            );
        } else {
        }
    }

    private function salvarProdutoEcommerce($request, $produto, $file_name)
    {
        $categoriaFirst = CategoriaProdutoEcommerce::where('empresa_id', $request->empresa_id)
        ->first();
        $produtoEcommerce = [
            'produto_id' => $produto->id,
            'categoria_id' => $request->ecommerce_categoria_id,
            'empresa_id' => $request->empresa_id,
            'descricao' => $request->descricao_ecommerce ?? '',
            'controlar_estoque' => $request->ecommerce_controlar_estoque,
            'destaque' => $request->ecommerce_destaque,
            'status' => $request->ecommerce_ativo,
            'valor' => $request->valor_ecommerce ? __convert_value_bd($request->valor_ecommerce) : __convert_value_bd($request->valor_venda),
        ];
        if ($produto->ecommerce) {
            $result = $produto->ecommerce;
            $result->fill($produtoEcommerce)->save();
        } else {
            $result = ProdutoEcommerce::create($produtoEcommerce);
        }
        $produtoEcommerce = ProdutoEcommerce::find($result->id);
        if ($result) {
            $this->salveImagemProdutoEcommerce($file_name, $produtoEcommerce);
        }
    }


    private function salveImagemProdutoEcommerce($file_name, $produtoEcommerce)
    {
        if ($file_name != "") {
            copy(public_path('uploads/products/') . $file_name, public_path('uploads/produtoEcommerce/') . $file_name);
            ImagemProdutoEcommerce::create(
                [
                    'produto_id' => $produtoEcommerce->id,
                    'path' => $file_name
                ]
            );
        } else {
        }
    }
}

