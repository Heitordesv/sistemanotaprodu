<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\AutorizacaoDevolucao;
use App\Models\Acessor;
use App\Models\Categoria;
use App\Models\CategoriaConta;
use App\Models\Certificado;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ComissaoVenda;
use App\Models\ConfigCaixa;
use App\Models\ConfigNota;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\CreditoVenda;
use App\Models\Empresa;
use App\Models\FaturaFrenteCaixa;
use App\Models\Funcionario;
use App\Models\GrupoCliente;
use App\Models\ItemVendaCaixa;
use App\Models\ListaPreco;
use App\Models\NaturezaOperacao;
use App\Models\Pais;
use App\Models\Produto;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Tributacao;
use App\Models\TrocaVenda;
use App\Models\TrocaVendaCaixa;
use App\Models\Usuario;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Models\VendaCaixaPreVenda;
use App\Services\AutorizacaoDevolucaoService;
use App\Services\DevolucaoEstoqueService;
use App\Services\FiscalTenantGuardService;
use App\Services\PdvReceiptPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NFePHP\DA\NFe\Cupom;
use NFePHP\DA\NFe\CupomPedido;
use Svg\Tag\Rect;
use App\Models\Contigencia;
use App\Models\Filial;

use function Ramsey\Uuid\v1;

class FrontBoxController extends Controller
{
    protected $empresa_id = null;
    public function __construct()
    {

        if (!is_dir(public_path('xml_nfce'))) {
            mkdir(public_path('xml_nfce'), 0777, true);
        }
        if (!is_dir(public_path('xml_nfce_cancelada'))) {
            mkdir(public_path('xml_nfce_cancelada'), 0777, true);
        }

        $this->middleware(function ($request, $next) {
            request()->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if (!$value) {
                return redirect("/login");
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {   
        $view = $this->pdvAssincrono($request->prevenda_id);
        return $view;
    }

    /**
     * As consultas de produto feitas pela tela web do PDV usam a empresa da
     * sessão, resolvida pelo ResolveCashTenantContext. O controller da API é
     * reutilizado apenas para manter o mesmo formato de resposta das telas
     * legadas; empresa_id recebido do navegador nunca é a fonte de identidade.
     */
    public function produtosPesquisa(Request $request)
    {
        return app(\App\Http\Controllers\API\ProdutoController::class)
            ->pesquisa($request);
    }

    public function produtosFind(Request $request, $id)
    {
        app(FiscalTenantGuardService::class)->produto(
            (int) $request->empresa_id,
            (int) $id
        );

        return app(\App\Http\Controllers\API\ProdutoController::class)
            ->find($id);
    }

    public function produtosFindByBarcode(Request $request)
    {
        return app(\App\Http\Controllers\API\ProdutoController::class)
            ->findByBarcode($request);
    }

    public function produtosFindByBarcodeReference(Request $request)
    {
        return app(\App\Http\Controllers\API\ProdutoController::class)
            ->findByBarcodeReference($request);
    }

    private function validaCaixaAberto($funcionarios)
    {
        $temp = [];

        foreach ($funcionarios as $f) {
            $abertura = AberturaCaixa::query()
                ->where('empresa_id', request()->empresa_id)
                ->where('usuario_id', $f->usuario_id)
                ->where('status', 0)
                ->orderBy('id', 'desc')
                ->first();

            if ($abertura) {
                $temp[] = $f;
            }
        }

        return $temp;
    }

    private function produtosMaisVendidos()
    {
        $itens = ItemVendaCaixa::selectRaw('item_venda_caixas.*, count(quantidade) as qtd')
        ->join('venda_caixas', 'venda_caixas.id', '=', 'item_venda_caixas.venda_caixa_id')
        ->where('venda_caixas.empresa_id', request()->empresa_id)
        ->groupBy('item_venda_caixas.produto_id')
        ->orderBy('qtd')
        ->limit(20)
        ->get();

        $produtos = [];
        foreach ($itens as $i) {
            $p = Produto::findOrFail($i->produto_id);
            if (!$p->inativo) {
                array_push($produtos, $p);
            }
        }
        return $produtos;
    }

    protected function pdvAssincrono($prevenda_id = null)
    {
        $item = null;
        if ($prevenda_id != null) {
            $item = VendaCaixaPreVenda::findOrFail($prevenda_id);
        }
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        if($config == null){
            session()->flash("flash_warning", "Configurar o emitente primeiramente!");
            return redirect()->route('configNF.index');
        }
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
        ->get();
        $categorias = Categoria::where('empresa_id', request()->empresa_id)
        ->get();
        $produtos = Produto::where('empresa_id', request()->empresa_id)
        ->get();
        $tributacao = Tributacao::where('empresa_id', request()->empresa_id)
        ->get();
        $tiposPagamento = VendaCaixa::tiposPagamento();
        
        if ($config->nat_op_padrao == null) {
            session()->flash("flash_warning", "Informe a natureza de operação primeiramente!");
            return redirect()->route('configNF.index');
        }
        $certificado = Certificado::where('empresa_id', request()->empresa_id)
        ->first();
        $usuario = Usuario::findOrFail(get_id_user());
        if (count($naturezas) == 0 || $config == null || count($categorias) == 0  || count($produtos) == 0 || $tributacao == null) {
            $p = view("frontBox.alerta", compact(
                'produtos',
                'categorias',
                'naturezas',
                'config',
                'tributacao'
            ));
            return $p;
        } else {
            $tiposPagamentoMulti = VendaCaixa::tiposPagamentoMulti();
            $categorias = Categoria::where('empresa_id', request()->empresa_id)
            ->orderBy('nome')->get();
            $clientes = Cliente::orderBy('razao_social')
            ->where('empresa_id', request()->empresa_id)
            ->get();
            foreach ($clientes as $c) {
                $c->totalEmAberto = 0;
                $soma = $this->getTotalContaCredito($c);
                if ($soma->total != null) {
                    $c->totalEmAberto = $soma->total;
                }
            }
            $atalhos = ConfigCaixa::where('usuario_id', get_id_user())
            ->first();
            $lista = ListaPreco::where('empresa_id', request()->empresa_id)->get();
            $produtosMaisVendidos = $this->produtosMaisVendidos();
            $rascunhos = $this->getRascunhos();
            $preVendas = VendaCaixaPreVenda::where('empresa_id', request()->empresa_id)
            ->where('status', 0)
            ->limit(20)
            ->orderBy('id', 'desc')
            ->get();
            $funcionarios = Funcionario::where('funcionarios.empresa_id', request()->empresa_id)
            ->select('funcionarios.*')
            ->join('usuarios', 'usuarios.id', '=', 'funcionarios.usuario_id')
            ->get();

            $funcionarios = $this->validaCaixaAberto($funcionarios);
            if (sizeof($funcionarios) == 0 && $usuario->caixa_livre) {
                session()->flash("flash_erro", "Usuário definido para caixa livre, cadastre ao menos um funcionário!");
                return redirect('/funcionarios');
            }

            $usuarios = Usuario::where('empresa_id', request()->empresa_id)
            ->where('ativo', 1)
            ->orderBy('nome', 'asc')
            ->get();
            $vendedor = Funcionario::where('empresa_id', request()->empresa_id)->get();
            $estados = Cliente::estados();
            $cidades = Cidade::all();
            $pais = Pais::all();
            $grupos = GrupoCliente::get();
            $acessores = Acessor::where('empresa_id', request()->empresa_id)->get();
            $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
            $filial = __get_local_padrao();

            $filial = Filial::find($filial);
            $abertura = AberturaCaixa::where('empresa_id', request()->empresa_id)
            ->where('usuario_id', get_id_user())
            ->where('status', 0)
            ->orderBy('id', 'desc')
            ->first();
            $sangrias = [];
            $suprimentos = [];
            $vendas = [];
            if ($abertura != null) {
                $sangrias = SangriaCaixa::where('empresa_id', request()->empresa_id)
                ->where('usuario_id', get_id_user())
                ->whereBetween('created_at', [
                    $abertura->created_at,
                    date('Y-m-d H:i:s')
                ])
                ->get();
                $suprimentos = SuprimentoCaixa::where('empresa_id', request()->empresa_id)
                ->where('usuario_id', get_id_user())
                ->whereBetween('created_at', [
                    $abertura->created_at,
                    date('Y-m-d H:i:s')
                ])
                ->get();
                $vendas = VendaCaixa::where('empresa_id', request()->empresa_id)
                ->where('usuario_id', get_id_user())
                ->whereBetween('created_at', [
                    $abertura->created_at,
                    date('Y-m-d H:i:s')
                ])->get();
            }
            return view('frontBox.index', compact(
                'tiposPagamento',
                'config',
                'item',
                'abertura',
                'certificado',
                'rascunhos',
                'preVendas',
                'estados',
                'sangrias',
                'vendas',
                'suprimentos',
                'cidades',
                'pais',
                'grupos',
                'acessores',
                'vendedor',
                'usuarios',
                'funcionarios',
                'lista',
                'produtosMaisVendidos',
                'atalhos',
                'usuario',
                'clientes',
                'categorias',
                'tiposPagamentoMulti',
                'filial'
            ));
        }
    }

    private function getTotalContaCredito($cliente)
    {
        return CreditoVenda::selectRaw('sum(vendas.valor_total) as total')
        ->join('vendas', 'vendas.id', '=', 'credito_vendas.venda_id')
        ->where('credito_vendas.cliente_id', $cliente->id)
        ->where('status', 0)
        ->first();
    }

    private function getRascunhos()
    {
        return VendaCaixa::where('rascunho', 1)
        ->where('empresa_id', request()->empresa_id)
        ->limit(20)
        ->orderBy('id', 'desc')
        ->get();
    }

    // private function preVendas()
    // {
    //     return PreVenda::where('empresa_id', request()->empresa_id)
    //         ->limit(20)
    //         ->orderBy('id', 'desc')
    //         ->get();
    // }


    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $valor_total = $this->somaItens($request);
                $empresa = Empresa::findOrFail($request->empresa_id);
                $request->merge([
                    'usuario_id' => get_id_user(),
                    'observacao' => $request->observacao ?? '',
                    'qtd_volumes' => $request->qtd_volumes ?? 0,
                    'peso_liquido' => $request->peso_liquido ?? 0,
                    'peso_bruto' => $request->peso_bruto ?? 0,
                    'desconto' => $request->desconto ?? 0,
                    'valor_total' => $valor_total,
                    'estado_emissao' => 'novo',
                    'sequencia_cce' => $request->sequencia_cce ?? 0,
                    'chave' => $request->chave ?? 0,
                    'acrescimo' => $request->acrescimo ?? 0,
                    'natureza_id' => $empresa->configNota->nat_op_padrao,
                    'dinheiro_recebido' => $request->valor_recebido ? __convert_value_bd($request->valor_recebido) : 0,
                    'troco' => 0,
                    'forma_pagamento' => '',
                    'tipo_pagamento' => $request->tipo_pagamento_row ? '99' : $request->tipo_pagamento,
                    'estado' => 'novo',
                    'nome' => $request->nome,
                    'cpf' => $request->cpf ?? '',
                    'pedido_delivery_id' => 0,
                    'qr_code_base64' => 0,
                ]);
                $vendaCaixa = VendaCaixa::create($request->all());
                $stockMove = new StockMove();
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    $cfop = 0;
                    if ($empresa->configNota->natureza->sobrescreve_cfop) {
                        $cfop = $empresa->configNota->natureza->CFOP_saida_estadual;
                    } else {
                        $cfop = $product->CFOP_saida_estadual;
                    }
                    ItemVendaCaixa::create([
                        'venda_caixa_id' => $vendaCaixa->id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor' => __convert_value_bd($request->valor_unitario[$i]),
                        'valor_custo' => $product->valor_compra,
                        'cfop' => $cfop,
                        'observacao' => $request->observacao ?? '',
                        'item_pedido_id' => null
                    ]);
                    $stockMove->downStock(
                        $product->id,
                        __convert_value_bd($request->quantidade[$i]),
                    );
                }
                if ($request->vendedor_id != null) {
                    $vendedor = Funcionario::where('empresa_id', $request->empresa_id)->first();
                    $vendedor->percentual_comissao;
                    $percentual_comissao = $vendedor->percentual_comissao;
                    $valorRetorno = $this->calcularComissaoVenda($vendaCaixa, $percentual_comissao);
                    ComissaoVenda::create([
                        'funcionario_id' => $request->vendedor_id,
                        'venda_id' => $vendaCaixa->id,
                        'tabela' => 'venda_caixas',
                        'valor' => $valorRetorno,
                        'status' => 0,
                        'empresa_id' => $request->empresa_id
                    ]);
                }
                if ($request->tipo_pagamento == '06') {
                    ContaReceber::create([
                        'venda_caixa_id' => $vendaCaixa->id,
                        'cliente_id' => $request->cliente_id,
                        'data_vencimento' => $request->data_vencimento,
                        'data_recebimento' => $request->data_vencimento,
                        'valor_integral' => __convert_value_bd($request->valor_total),
                        'valor_recebido' => 0,
                        'status' => 0,
                        'referencia' => "Parcela $i+1 da Compra código $vendaCaixa->id",
                        'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->first()->id,
                        'empresa_id' => $request->empresa_id,
                        'juros' => 0,
                        'multa' => 0,
                        'observacao' => $request->obs ?? '',
                        'tipo_pagamento' => $request->tipo_pagamento
                    ]);
                }
                if ($request->tipo_pagamento_row) {
                    for ($i = 0; $i < sizeof($request->tipo_pagamento_row); $i++) {
                        if ($request->tipo_pagamento_row[$i] == '06') {
                            ContaReceber::create([
                                'venda_caixa_id' => $vendaCaixa->id,
                                'cliente_id' => $request->cliente_id,
                                'data_vencimento' => $request->data_vencimento_row[$i],
                                'data_recebimento' => $request->data_vencimento_row[$i],
                                'valor_integral' => __convert_value_bd($request->valor_integral_row[$i]),
                                'valor_recebido' => 0,
                                'status' => 0,
                                'referencia' => "Parcela $i+1 da Compra código $vendaCaixa->id",
                                'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->first()->id,
                                'empresa_id' => $request->empresa_id,
                                'juros' => 0,
                                'multa' => 0,
                                'observacao' => $request->obs_row[$i] ?? '',
                                'tipo_pagamento' => $request->tipo_pagamento_row[$i]
                            ]);
                        }
                        FaturaFrenteCaixa::create([
                            'valor' => __convert_value_bd($request->valor_integral_row[$i]),
                            'forma_pagamento' => $request->tipo_pagamento_row[$i],
                            'venda_caixa_id' => $vendaCaixa->id
                        ]);
                    }
                }
                return true;
            });
session()->flash("flash_sucesso", "Venda realizada com sucesso!");
} catch (\Exception $e) {
    echo $e->getMessage() . '<br>' . $e->getLine();
    die;
    session()->flash("flash_erro", "Algo deu errado por aqui: " . $e->getMessage());
    __saveLogError($e, $empresaId);
}
return redirect()->route('frenteCaixa.index');
}

private function somaItens($request)
{
    $valor_total = 0;
    for ($i = 0; $i < sizeof($request->produto_id); $i++) {
        $valor_total += __convert_value_bd($request->subtotal_item[$i]);
    }
    return $valor_total;
}


private function calcularComissaoVenda($vendaCaixa, $percentual_comissao)
{
    $valorRetorno = 0;
    foreach ($vendaCaixa->itens as $i) {
        if ($i->produto->perc_comissao > 0) {
            $valorRetorno += (($i->valor * $i->quantidade) * $i->produto->perc_comissao) / 100;
        } else {
            $valorRetorno += (($i->valor * $i->quantidade) * $percentual_comissao) / 100;
        }
    }
    return $valorRetorno;
}

public function devolucao(Request $request)
{
    $usuarioLogado = Usuario::query()
    ->where('id', (int) get_id_user())
    ->where('ativo', true)
    ->firstOrFail();
    $empresaId = (int) $usuarioLogado->empresa_id;

    $data = VendaCaixa::where('empresa_id', $empresaId)
    ->orderBy('id', 'desc')
    ->get();
    $caixa = AberturaCaixa::where('status', 1)->where('empresa_id', $empresaId)
    ->orderBy('id', 'desc')->first();
    if ($caixa != null) {
        foreach ($data as $v) {
            if (strtotime($v->created_at) < strtotime($caixa->updated_at)) {
                $v->impedeDelete = true;
            }
        }
    }

    $administradores = Usuario::where('empresa_id', $empresaId)
    ->where('adm', true)
    ->where('ativo', true)
    ->orderBy('nome')
    ->get(['id', 'nome']);

    $historicoDevolucoes = AutorizacaoDevolucao::where('empresa_id', $empresaId)
    ->orderBy('id', 'desc')
    ->limit(100)
    ->get();

    return view('frontBox.devolucao', compact(
        'data',
        'administradores',
        'historicoDevolucoes'
    ));
}


public function troca()
{
    $data = TrocaVenda::where('empresa_id', request()->empresa_id)
    ->paginate(20);
    return view('frontBox.troca', compact('data'));
}

public function fecharCaixa()
{
    $empresaId = (int) request()->empresa_id;
    $usuarioId = (int) get_id_user();

    $config = ConfigNota::where('empresa_id', $empresaId)->first();
    if (!$config) {
        session()->flash('flash_erro', 'Configure o emitente antes de fechar o caixa.');
        return redirect()->route('configNF.index');
    }

    // Uma abertura operacional sempre pertence a um único operador.
    // A configuração caixa_por_usuario não pode fazer dois caixas compartilharem a mesma sessão.
    $abertura = AberturaCaixa::query()
        ->where('empresa_id', $empresaId)
        ->where('usuario_id', $usuarioId)
        ->where('status', 0)
        ->orderBy('id', 'desc')
        ->first();

    if (!$abertura) {
        session()->flash('flash_warning', 'Não existe caixa aberto para este usuário.');
        return redirect()->route('caixa.index');
    }

    $ultimaVendaCaixaId = (int) (VendaCaixa::query()
        ->where('empresa_id', $empresaId)
        ->where('usuario_id', $usuarioId)
        ->max('id') ?? 0);

    $ultimaVendaId = (int) (Venda::query()
        ->where('empresa_id', $empresaId)
        ->where('usuario_id', $usuarioId)
        ->max('id') ?? 0);

    $vendasPdv = collect();
    if ($ultimaVendaCaixaId > (int) $abertura->primeira_venda_nfce) {
        $vendasPdv = VendaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where('id', '>', (int) $abertura->primeira_venda_nfce)
            ->where('id', '<=', $ultimaVendaCaixaId)
            ->get();
    }

    $vendasNfe = collect();
    if ($ultimaVendaId > (int) $abertura->primeira_venda_nfe) {
        $vendasNfe = Venda::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where('id', '>', (int) $abertura->primeira_venda_nfe)
            ->where('id', '<=', $ultimaVendaId)
            ->get();
    }

    $vendas = $this->agrupaVendas($vendasNfe, $vendasPdv);
    $somaTiposPagamento = $this->somaTiposPagamento($vendas);

    $movimentacaoFinanceira = $this->consultarMovimentacaoFinanceira(
        $empresaId,
        $abertura->created_at,
        now(),
        $abertura->filial_id ? (int) $abertura->filial_id : null
    );

    return view('frontBox.fechamento', compact(
        'vendas',
        'abertura',
        'somaTiposPagamento',
        'movimentacaoFinanceira'
    ));
}


private function consultarMovimentacaoFinanceira(
    int $empresaId,
    $inicio,
    $fim,
    ?int $filialId = null
): array {
    $dataInicial = \Carbon\Carbon::parse($inicio)->toDateString();
    $dataFinal = \Carbon\Carbon::parse($fim)->toDateString();

    $contasPagar = ContaPagar::query()
        ->with(['fornecedor', 'compra.fornecedor', 'categoria'])
        ->where('empresa_id', $empresaId)
        ->where('status', true)
        ->where('valor_pago', '>', 0)
        ->whereNotNull('data_pagamento')
        ->whereBetween('data_pagamento', [$dataInicial, $dataFinal])
        ->when($filialId !== null, function ($query) use ($filialId) {
            return $query->where('filial_id', $filialId);
        })
        ->orderBy('data_pagamento')
        ->orderBy('id')
        ->get();

    $contasReceber = ContaReceber::query()
        ->with([
            'cliente',
            'venda.cliente',
            'vendaCaixa.cliente',
            'empresa_id_emp_rel',
            'categoria',
        ])
        ->where('empresa_id', $empresaId)
        ->where('status', true)
        ->where('valor_recebido', '>', 0)
        ->whereNotNull('data_recebimento')
        ->whereBetween('data_recebimento', [$dataInicial, $dataFinal])
        ->when($filialId !== null, function ($query) use ($filialId) {
            return $query->where('filial_id', $filialId);
        })
        ->orderBy('data_recebimento')
        ->orderBy('id')
        ->get();

    $totalPagar = round((float) $contasPagar->sum('valor_pago'), 2);
    $totalReceber = round((float) $contasReceber->sum('valor_recebido'), 2);

    return [
        'contas_pagar' => $contasPagar,
        'contas_receber' => $contasReceber,
        'total_pagar' => $totalPagar,
        'total_receber' => $totalReceber,
        'saldo' => round($totalReceber - $totalPagar, 2),
    ];
}

private function somaTiposPagamento($vendas, $start_date = null, $end_date = null)
{
    $tipos = $this->preparaTipos();

    foreach ($vendas as $v) {
        // Filtrar por data
        if (!empty($start_date) || !empty($end_date)) {
            $dataVenda = new \DateTime($v->created_at);

            if (!empty($start_date)) {
                $dataInicio = new \DateTime($start_date);
                if ($dataVenda < $dataInicio) {
                    continue;
                }
            }

            if (!empty($end_date)) {
                $dataFim = new \DateTime($end_date);
                if ($dataVenda > $dataFim) {
                    continue;
                }
            }
        }

        // Apenas estados "APROVADO"
        if ($v->estado_emissao == 'aprovado') {
            if (isset($tipos[$v->tipo_pagamento])) {
                if ($v->tipo_pagamento != 99) {
                    if (isset($v->NFcNumero)) {
                        if (!$v->rascunho && !$v->consignado) {
                            $tipos[$v->tipo_pagamento] += $v->valor_total;
                        }
                    } else {
                        if ($v->duplicatas && sizeof($v->duplicatas) > 0) {
                            foreach ($v->duplicatas as $d) {
                                $tipos[Venda::getTipoPagamentoNFe($d->tipo_pagamento)] += $d->valor_integral;
                            }
                        } else {
                            $tipos[$v->tipo_pagamento] += $v->valor_total - $v->desconto;
                        }
                    }
                } else {
                    if (isset($v->duplicatas)) {
                        foreach ($v->duplicatas as $d) {
                            $tipos[Venda::getTipoPagamentoNFe($d->tipo_pagamento)] += $d->valor_integral;
                        }
                    } else {
                        $tipos[$v->tipo_pagamento] += $v->valor_total - $v->desconto;
                    }
                }
            } else {
                if ($v->fatura) {
                    foreach ($v->fatura as $f) {
                        $tipos[$f->forma_pagamento] += $f->valor;
                    }
                }
            }
        }
    }

    return $tipos;
}

private function preparaTipos()
{
    $temp = [];
    foreach (VendaCaixa::tiposPagamento() as $key => $tp) {
        $temp[$key] = 0;
    }
    return $temp;
}

public function configuracao()
{
    $item = ConfigCaixa::where('usuario_id', get_id_user())
    ->first();
    if ($item != null) {
        $item->tipos_pagamento = json_decode($item->tipos_pagamento) ? json_decode($item->tipos_pagamento) : [];
    }
    return view('frontBox.create', compact('item'));
}

public function storeConfig(Request $request)
{
    $item = ConfigCaixa::where('usuario_id', get_id_user())
    ->first();
    try {
        if ($item == null) {
            $request->merge([
                'finalizar' => $request->finalizar ?? '',
                'reiniciar' => $request->reiniciar ?? '',
                'editar_desconto' => $request->editar_desconto ?? '',
                'editar_acrescimo' => $request->editar_acrescimo ?? '',
                'editar_observacao' => $request->editar_observacao ?? '',
                'setar_valor_recebido' => $request->setar_valor_recebido ?? '',
                'forma_pagamento_dinheiro' => $request->forma_pagamento_dinheiro ?? '',
                'forma_pagamento_debito' => $request->forma_pagamento_debito ?? '',
                'forma_pagamento_credito' => $request->forma_pagamento_credito ?? '',
                'setar_quantidade' => $request->setar_quantidade ?? '',
                'forma_pagamento_pix' => $request->forma_pagamento_pix ?? '',
                'setar_leitor' => $request->setar_leitor ?? '',
                'finalizar_fiscal' => $request->finalizar_fiscal ?? '',
                'finalizar_nao_fiscal' => $request->finalizar_nao_fiscal ?? '',
                'valor_recebido_automatico' => 0,
                // 'modelo_pdv' => $request->modelo_pdv,
                'balanca_valor_peso' => $request->balanca_valor_peso,
                'balanca_digito_verificador' => $request->balanca_digito_verificador ?? 5,
                'valor_recebido_automatico' => 0,
                'impressora_modelo' => $request->impressora_modelo ?? 80,
                'usuario_id' => get_id_user(),
                'mercadopago_public_key' => $request->mercadopago_public_key ?? '',
                'mercadopago_access_token' => $request->mercadopago_access_token ?? '',
                'tipos_pagamento' => json_encode($request->tipos_pagamento),
                'pagamento_padrao' => $request->pagamento_padrao ?? ''
            ]);
            ConfigCaixa::create($request->all());
            session()->flash("flash_sucesso", "Cadastrado com sucesso");
        } else {
            $request->merge([
                'finalizar' => $request->finalizar ?? '',
                'reiniciar' => $request->reiniciar ?? '',
                'editar_desconto' => $request->editar_desconto ?? '',
                'editar_acrescimo' => $request->editar_acrescimo ?? '',
                'editar_observacao' => $request->editar_observacao ?? '',
                'setar_valor_recebido' => $request->setar_valor_recebido ?? '',
                'forma_pagamento_dinheiro' => $request->forma_pagamento_dinheiro ?? '',
                'forma_pagamento_debito' => $request->forma_pagamento_debito ?? '',
                'forma_pagamento_credito' => $request->forma_pagamento_credito ?? '',
                'setar_quantidade' => $request->setar_quantidade ?? '',
                'forma_pagamento_pix' => $request->forma_pagamento_pix ?? '',
                'setar_leitor' => $request->setar_leitor ?? '',
                'finalizar_fiscal' => $request->finalizar_fiscal ?? '',
                'finalizar_nao_fiscal' => $request->finalizar_nao_fiscal ?? '',
                'valor_recebido_automatico' => 0,
                // 'modelo_pdv' => $request->modelo_pdv,
                'balanca_valor_peso' => $request->balanca_valor_peso,
                'balanca_digito_verificador' => $request->balanca_digito_verificador ?? 5,
                'valor_recebido_automatico' => 0,
                'impressora_modelo' => $request->impressora_modelo ?? 80,
                'usuario_id' => get_id_user(),
                'mercadopago_public_key' => $request->mercadopago_public_key ?? '',
                'mercadopago_access_token' => $request->mercadopago_access_token ?? '',
                'tipos_pagamento' => json_encode($request->tipos_pagamento),
                'pagamento_padrao' => $request->pagamento_padrao ?? ''
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Cadastro atualizado!");
        }
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }
    return redirect()->route('frenteCaixa.configuracao');
}


private function agrupaVendas($vendas, $vendasPdv)
{
    $temp = [];
    foreach ($vendas as $v) {
        $v->tipo = 'VENDA';
        array_push($temp, $v);
    }
    foreach ($vendasPdv as $v) {
        $v->tipo = 'PDV';
        array_push($temp, $v);
    }
    return $temp;
}

public function detalhes($id)
{
    $item = VendaCaixa::findOrFail($id);

    return view('caixa.detalhes', compact('item'));
}

public function fecharPost(Request $request)
{
    $empresaId = (int) request()->empresa_id;
    $usuarioId = (int) get_id_user();
    $aberturaId = (int) $request->abertura_id;

    try {
        DB::transaction(function () use ($request, $empresaId, $usuarioId, $aberturaId) {
            // Lock da abertura evita dois fechamentos concorrentes da mesma sessão.
            $abertura = AberturaCaixa::query()
                ->where('id', $aberturaId)
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('status', 0)
                ->lockForUpdate()
                ->first();

            if (!$abertura) {
                throw new \DomainException(
                    'Este caixa não está aberto para o usuário atual ou já foi fechado.'
                );
            }

            $ultimaVendaNfceId = (int) (VendaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->max('id') ?? 0);

            $ultimaVendaNfeId = (int) (Venda::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->max('id') ?? 0);

            $quantidadeVendasPdv = VendaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('id', '>', (int) $abertura->primeira_venda_nfce)
                ->count();

            $quantidadeVendasNfe = Venda::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('id', '>', (int) $abertura->primeira_venda_nfe)
                ->count();

            if (($quantidadeVendasPdv + $quantidadeVendasNfe) === 0) {
                throw new \DomainException('Não é possível fechar o caixa sem nenhuma venda.');
            }

            $abertura->ultima_venda_nfce = $ultimaVendaNfceId > (int) $abertura->primeira_venda_nfce
                ? $ultimaVendaNfceId
                : (int) $abertura->primeira_venda_nfce;
            $abertura->ultima_venda_nfe = $ultimaVendaNfeId > (int) $abertura->primeira_venda_nfe
                ? $ultimaVendaNfeId
                : (int) $abertura->primeira_venda_nfe;
            $abertura->status = true;
            $abertura->valor_dinheiro_caixa = $request->filled('valor_dinheiro_caixa')
                ? __convert_value_bd($request->valor_dinheiro_caixa)
                : 0;
            $abertura->save();
        });

        session()->flash('flash_sucesso', 'Caixa fechado com sucesso!');
    } catch (\DomainException $e) {
        session()->flash('flash_warning', $e->getMessage());
    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Não foi possível fechar o caixa: ' . $e->getMessage());
        __saveLogError($e, $empresaId);
    }

    if (isset($request->redirect)) {
        return redirect($request->redirect);
    }

    return redirect()->route('frenteCaixa.list');
}

public function listaFechamento($id)
{
    $aberturas = AberturaCaixa::where('empresa_id', request()->empresa_id)
    ->get();
    $abertura = null;
    $inicio = 0;
    $fim = 0;
    for ($i = 0; $i < sizeof($aberturas); $i++) {
        if ($aberturas[$i]->id == $id) {
            $abertura = $aberturas[$i];
            if ($i > 0) {
                $inicio = $aberturas[$i - 1]->ultima_venda + 1;
            }
            $fim = $aberturas[$i]->ultima_venda;
        }
    }
    $vendas = [];
    $somaTiposPagamento = [];
    $vendas = VendaCaixa
    ::whereBetween('id', [
        $inicio,
        $fim
    ])
    ->get();
    $somaTiposPagamento = $this->somaTiposPagamento($vendas);
    return view('frontBox.lista_fecha_caixa', compact('vendas', 'abertura', 'somaTiposPagamento'));
}

public function list(Request $request)
{
    $start_date = $request->start_date;
    $end_date = $request->end_date;
    $valor = $request->valor;
    $estado = $request->estado;
    $numero_nfe = $request->numero_nfe;

    $item = VendaCaixa::where('empresa_id', request()->empresa_id)->get();
    $somaTiposPagamento = $this->somaTiposPagamento($item);

    $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
    $data = VendaCaixa::where('empresa_id', $request->empresa_id)
    ->when(!empty($start_date), function ($query) use ($start_date) {
        return $query->whereDate('created_at', '>=', $start_date);
    })
    ->when(!empty($end_date), function ($query) use ($end_date) {
        return $query->whereDate('created_at', '<=', $end_date);
    })
    ->when(!empty($valor), function ($query) use ($valor) {
        return $query->where('valor', $valor);
    })
    ->when($estado != "", function ($query) use ($estado) {
        return $query->where('estado_emissao', $estado);
    })
    ->when($numero_nfe != "", function ($query) use ($numero_nfe) {
        return $query->where('numero_nfce', $numero_nfe);
    })
    ->orderBy('created_at', 'desc')
    ->paginate(env("PAGINACAO"));

    $contigencia =  $this->getContigencia();
    return view('frontBox.list', compact('data', 'config', 'contigencia', 'somaTiposPagamento'));
}

private function getContigencia()
{
    $active = Contigencia::where('empresa_id', request()->empresa_id)
    ->where('status', 1)
    ->where('documento', 'NFCe')
    ->first();
    return $active;
}

public function imprimirNaoFiscal($id, PdvReceiptPdfService $receiptService)
{
    $item = VendaCaixa::findOrFail($id);
    if (valida_objeto($item)) {
        $config = ConfigNota::where('empresa_id', $item->empresa_id)
        ->first();
        if ($config->logo) {
            $logoPath = public_path('uploads/configEmitente/') . $config->logo;
            $logoMime = mime_content_type($logoPath) ?: 'image/jpeg';
            $logo = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
        } else {
            $logo = null;
        }
        $usuario = Usuario::find(get_id_user());
        $paperWidth = $usuario->config
            ? (int) $usuario->config->impressora_modelo
            : 80;
        $pdf = $receiptService->render($item, $config, $logo, $paperWidth);
        return response($pdf)
        ->header('Content-Type', 'application/pdf');
    } else {
        return redirect('/403');
    }
}

public function destroy(
    Request $request,
    $id,
    AutorizacaoDevolucaoService $autorizacaoService,
    DevolucaoEstoqueService $devolucaoEstoqueService
) {
    $usuarioLogado = Usuario::query()
        ->where('id', (int) get_id_user())
        ->where('ativo', true)
        ->firstOrFail();

    $empresaId = (int) $usuarioLogado->empresa_id;
    $item = VendaCaixa::with('itens')
        ->where('id', (int) $id)
        ->where('empresa_id', $empresaId)
        ->firstOrFail();

    if (
        $item->estado_emissao === 'cancelado' ||
        (bool) $item->retorno_estoque
    ) {
        if ($request->ajax()) {
            return response()->json([
                'message' => 'Esta venda já foi cancelada e devolvida.',
            ], 409);
        }

        abort(409, 'Esta venda já foi cancelada e devolvida.');
    }

    $autorizacao = $autorizacaoService->autorizar(
        $empresaId,
        $request->admin_id,
        $request->admin_senha
    );

    try {
        DB::transaction(function () use ($item, $autorizacao, $autorizacaoService, $devolucaoEstoqueService) {
            $autorizacaoService->registrar(
                $item,
                $autorizacao['solicitante'],
                $autorizacao['autorizador'],
                'devolucao_nao_fiscal'
            );

            $devolucaoEstoqueService->devolver(
                $item,
                $autorizacao['solicitante'],
                $autorizacao['autorizador']
            );

            $item->estado_emissao = 'cancelado';
            $item->retorno_estoque = 1;
            $item->save();
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Venda cancelada, estoque devolvido e movimentação registrada.',
            ]);
        }

        session()->flash("flash_sucesso", "Venda cancelada, estoque devolvido e movimentação registrada!");
    } catch (\Exception $e) {
        __saveLogError($e, $empresaId);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Não foi possível concluir a devolução.',
            ], 500);
        }

        session()->flash("flash_erro", "Não foi possível concluir a devolução.");
    }
    return redirect()->back();
}
}
