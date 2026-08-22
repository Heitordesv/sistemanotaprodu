<?php

namespace App\Http\Controllers;

use App\Helpers\StockMove;
use App\Models\Acessor;
use App\Models\Categoria;
use App\Models\CategoriaConta;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ConfigNota;
use App\Models\ContaReceber;
use App\Models\DivisaoGrade;
use App\Models\FormaPagamento;
use App\Models\Funcionario;
use App\Models\GrupoCliente;
use App\Models\ItemRemessaNfe;
use App\Models\ListaPreco;
use App\Models\Marca;
use App\Models\NaturezaOperacao;
use App\Models\RemessaNfe;
use App\Models\RemessaNfeFatura;
use App\Models\Pais;
use App\Models\Produto;
use App\Models\RemessaReferenciaNfe;
use App\Models\TelaPedido;
use App\Models\Transportadora;
use App\Models\Tributacao;
use App\Services\NFeRemessaService;
use App\Utils\Util;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use NFePHP\DA\NFe\Daevento;
use NFePHP\DA\NFe\Danfe;

class NfeRemessaController extends Controller
{
    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $type_search = $request->get('type_search');
        $estado_emissao = $request->get('estado_emissao');
        $pesquisa_data = $request->get('pesquisa_data');
        $data_emissao = $request->get('data_emissao');
        $filial_id = $request->get('filial_id');
        $local_padrao = __get_local_padrao();
        if (!$filial_id && $local_padrao) {
            $filial_id = $local_padrao;
        }
        $data = RemessaNfe::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date, $pesquisa_data) {
            return $query->whereDate($pesquisa_data, '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date, $pesquisa_data) {
            return $query->whereDate($pesquisa_data, '<=', $end_date);
        })
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('cliente_id', $cliente_id);
        })
        ->when($estado_emissao != "", function ($query) use ($estado_emissao) {
            return $query->where('estado_emissao', $estado_emissao);
        })
        ->when(!empty($data_emissao), function ($query) use ($data_emissao) {
            return $query->whereDate('created_at', '<=', $data_emissao);
        })
        ->when($filial_id != 'todos', function ($query) use ($filial_id) {
            $filial_id = $filial_id == -1 ? null : $filial_id;
            return $query->where('filial_id', $filial_id);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('nfe_remessa.index', compact('data'));
    }

    public function create(Request $request)
    {
        $dataValidate = [
            'clientes', 'tributacaos', 'produtos', 'natureza_operacaos'
        ];
        $util = new Util();
        $validateEntry = $util->validateEntry($dataValidate, $request->empresa_id);
        if ($validateEntry != null) {
            session()->flash("flash_erro", $validateEntry['message']);
            return redirect($validateEntry['route']);
        }
        $paises = Pais::all();
        $clientes = Cliente::where('empresa_id', $request->empresa_id)->get();
        $grupos = GrupoCliente::where('empresa_id', $request->empresa_id)->get();
        $acessores = Acessor::where('empresa_id', $request->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)->get();
        $formaPagamento = FormaPagamento::where('empresa_id', $request->empresa_id)->get();
        $categorias = Categoria::where('empresa_id', $request->empresa_id)->get();
        $marcas = Marca::where('empresa_id', $request->empresa_id)->get();
        $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', request()->empresa_id)->get();
        $naturezaPadrao = NaturezaOperacao::where('empresa_id', $request->empresa_id)
        ->first();

        $naturezas = NaturezaOperacao::where('empresa_id', $request->empresa_id)
        ->get();

        $tributacao = Tributacao::where('empresa_id', $request->empresa_id)
        ->first();
        $listaPreco = ListaPreco::where('empresa_id', $request->empresa_id)->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', $request->empresa_id)
        ->where('sub_divisao', true)
        ->get();
        $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        $transportadoras = Transportadora::where('empresa_id', $request->empresa_id)->get();
        $produtos = Produto::where('empresa_id', $request->empresa_id)->get();
        $cidades = Cidade::all();
        $telasPedido = TelaPedido::where('empresa_id', $request->empresa_id)->get();

        return view('nfe_remessa.create', compact(
            'formaPagamento',
            'paises',
            'grupos',
            'acessores',
            'funcionarios',
            'categorias',
            'marcas',
            'categoriasEcommerce',
            'naturezaPadrao',
            'naturezas',
            'tributacao',
            'listaPreco',
            'cidades',
            'clientes',
            'config',
            'subDivisoes',
            'divisoes',
            'transportadoras',
            'produtos',
            'telasPedido'
        ));
    }

    public function store(Request $request)
    {
        $numero_sequencial = 0;
        $last = RemessaNfe::where('empresa_id', $request->empresa_id)
        ->orderBy('id', 'desc')
        ->first();
        $numero_sequencial = $last != null ? ($last->numero_sequencial + 1) : 1;
        try {
            $result = DB::transaction(function () use ($request, $numero_sequencial) {
                $dataNFe = [
                    'cliente_id' => $request->cliente_id,
                    'empresa_id' => $request->empresa_id,
                    'usuario_id' => get_id_user(),
                    'valor_total' => __convert_value_bd($request->valor_total),
                    'forma_pagamento' => $request->tipo_pagamento,
                    'numero_nfe' => 0,
                    'natureza_id' => $request->natureza_id,
                    'chave' => '',
                    'estado_emissao' => 'novo',
                    'observacao' => $request->obs ?? '',
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'transportadora_id' => $request->transportadora_id,
                    'sequencia_cce' => 0,
                    'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                    'data_entrega' => $request->data_entrega,
                    'nSerie' => 0,
                    'numero_sequencial' => $numero_sequencial,
                    'filial_id' => $request->filial_id != -1 ? $request->filial_id : null,
                    'baixa_estoque' => $request->baixa_estoque,
                    'gerar_conta_receber' => $request->gerar_conta_receber,
                    'tipo' => $request->tipo,
                    'placa' => $request->placa,
                    'uf' => $request->uf,
                    'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                    'tipo_frete' => $request->tipo_frete,
                    'qtd_volumes' => $request->qtd_volumes ? __convert_value_bd($request->qtd_volumes) : 0,
                    'numeracao_volumes' => $request->numeracao_volumes ? __convert_value_bd($request->numeracao_volumes) : 0,
                    'especie' => $request->especie,
                    'peso_liquido' => $request->peso_liquido ? __convert_value_bd($request->peso_liquido) : 0,
                    'peso_bruto' => $request->peso_bruto ? __convert_value_bd($request->peso_bruto) : 0,
                    'data_retroativa' => $request->data_retroativa,
                    'venda_caixa_id' => isset($request->venda_caixa_id) ? $request->venda_caixa_id : null
                ];
                $remessa = RemessaNfe::create($dataNFe);
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    // dd($product);
                    $dataItem = [
                        'remessa_id' => $remessa->id,
                        'produto_id' => $request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                        'sub_total' => __convert_value_bd($request->sub_total[$i]),
                        'cst_csosn' => $request->cst_csosn[$i],
                        'cst_pis' => $request->cst_pis[$i],
                        'cfop' => $request->cfop[$i],
                        'cst_cofins' => $request->cst_cofins[$i],
                        'cst_ipi' => $request->cst_ipi[$i],
                        'perc_icms' => $request->perc_icms[$i] ? __convert_value_bd($request->perc_icms[$i]) : 0,
                        'perc_pis' => $request->perc_pis[$i] ? __convert_value_bd($request->perc_pis[$i]) : 0,
                        'perc_cofins' => $request->perc_cofins[$i] ? __convert_value_bd($request->perc_cofins[$i]) : 0,
                        'perc_ipi' => $request->perc_ipi[$i] ? __convert_value_bd($request->perc_ipi[$i]) : 0,
                        'pRedBC' => $request->perc_red_bc[$i] ? __convert_value_bd($request->perc_red_bc[$i]) : 0,
                        'vbc_icms' => $request->vbc_icms[$i] ? __convert_value_bd($request->vbc_icms[$i]) : 0,
                        'vbc_pis' => $request->vbc_pis[$i] ? __convert_value_bd($request->vbc_pis[$i]) : 0,
                        'vbc_cofins' => $request->vbc_cofins[$i] ? __convert_value_bd($request->vbc_cofins[$i]) : 0,
                        'vbc_ipi' => $request->vbc_ipi[$i] ? __convert_value_bd($request->vbc_ipi[$i]) : 0,
                        'vBCSTRet' => $request->vBCSTRet[$i] ? __convert_value_bd($request->vBCSTRet[$i]) : 0,
                        'vFrete' => $request->vFrete[$i] ? __convert_value_bd($request->vFrete[$i]) : 0,
                        'modBCST' => $request->modBCST[$i] ? __convert_value_bd($request->modBCST[$i]) : 0,
                        'vBCST' => $request->vBCST[$i] ? __convert_value_bd($request->vBCST[$i]) : 0,
                        'pICMSST' => $request->pICMSST[$i] ? __convert_value_bd($request->pICMSST[$i]) : 0,
                        'vICMSST' => $request->vICMSST[$i] ? __convert_value_bd($request->vICMSST[$i]) : 0,
                        'pMVAST' => $request->pMVAST[$i] ? __convert_value_bd($request->pMVAST[$i]) : 0,
                        'x_pedido' => $request->x_pedido[$i] ?? '',
                        'num_item_pedido' => $request->num_item_pedido[$i] ?? '',
                        'cest' => $product->cest[$i] ?? '',
                        'valor_icms' => $request->valor_icms[$i] ? __convert_value_bd($request->valor_icms[$i]) : 0,
                        'valor_pis' => $request->valor_pis[$i] ? __convert_value_bd($request->valor_pis[$i]) : 0,
                        'valor_cofins' => $request->valor_cofins[$i] ? __convert_value_bd($request->valor_cofins[$i]) : 0,
                        'valor_ipi' => $request->valor_ipi[$i] ? __convert_value_bd($request->valor_ipi[$i]) : 0
                    ];
                    ItemRemessaNfe::create($dataItem);
                }
                if ($request->chave_nfe) {
                    for ($i = 0; $i < sizeof($request->chave_nfe); $i++) {
                        if ($request->chave_nfe[$i]) {
                            $chave = str_replace(" ", "", $request->chave_nfe[$i]);
                            RemessaReferenciaNfe::create([
                                'remessa_id' => $remessa->id,
                                'chave' => $chave
                            ]);
                        }
                    }
                }
                if ($request->valor_parcela) {
                    for ($i = 0; $i < sizeof($request->valor_parcela); $i++) {
                        $data_vencimento = $request->data_vencimento[$i];
                        if (!$data_vencimento) {
                            $data_vencimento = date('Y-m-d');
                        }
                        RemessaNfeFatura::create([
                            'remessa_id' => $remessa->id,
                            'tipo_pagamento' => $request->tipo_pagamento,
                            'valor' => __convert_value_bd($request->valor_parcela[$i]),
                            'data_vencimento' => $request->data_vencimento[$i]
                        ]);
                        if ($request->gerar_conta_receber == true) {
                            // $catVenda = $this->categoriaVenda();
                            ContaReceber::create([
                                'remessa_nfe_id' => $remessa->id,
                                'data_vencimento' => $request->data_vencimento[$i],
                                'data_recebimento' => $request->data_vencimento[$i],
                                'valor_integral' => __convert_value_bd($request->valor_parcela[$i]),
                                'cliente_id' => $request->cliente_id,
                                'valor_recebido' => 0,
                                'status' => false,
                                'tipo_pagamento' => $request->tipo_pagamento,
                                'referencia' => "Parcela $i+1, da NFe $remessa->id",
                                'empresa_id' => $request->empresa_id,
                                'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->where('tipo', 'receber')->first()->id,
                                'juros' => 0,
                                'multa' => 0,
                                'venda_caixa_id' => null,
                                'observacao' => '',
                                'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                            ]);
                        }
                    }
                }
            });
session()->flash("flash_sucesso", "NFe criada!");
} catch (\Exception $e) {
    echo $e->getMessage() . '<br>' . $e->getLine();
    die;
    session()->flash("flash_erro", "Algo deu errado");
    __saveLogError($e, request()->empresa_id);
}
return redirect()->route('nferemessa.index');
}

private function categoriaVenda()
{
    $cat = CategoriaConta::where('empresa_id', request()->empresa_id)
    ->where('nome', 'Vendas')
    ->first();
    if ($cat != null) return $cat->id;
    $cat = CategoriaConta::create([
        'nome' => 'Vendas',
        'empresa_id' => request()->empresa_id,
        'tipo' => 'receber'
    ]);
    return $cat->id;
}

public function edit($id)
{
    $item = RemessaNfe::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
    $grupos = GrupoCliente::where('empresa_id', request()->empresa_id)->get();
    $acessores = Acessor::where('empresa_id', request()->empresa_id)->get();
    $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
    $formaPagamento = FormaPagamento::where('empresa_id', request()->empresa_id)->get();
    $categorias = Categoria::where('empresa_id', request()->empresa_id)->get();
    $marcas = Marca::where('empresa_id', request()->empresa_id)->get();
    $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', request()->empresa_id)->get();
    $naturezaPadrao = NaturezaOperacao::where('empresa_id', request()->empresa_id)
    ->first();
    $tributacao = Tributacao::where('empresa_id', request()->empresa_id)
    ->first();
    $listaPreco = ListaPreco::where('empresa_id', request()->empresa_id)->get();
    $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
    ->where('sub_divisao', true)
    ->get();

    $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
    $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
    $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();
    $produtos = Produto::where('empresa_id', request()->empresa_id)->get();
    $cidades = Cidade::all();

    $telasPedido = TelaPedido::where('empresa_id', request()->empresa_id)->get();

    $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
    ->get();
    return view('nfe_remessa.edit', compact(
        'formaPagamento',
        'grupos',
        'acessores',
        'funcionarios',
        'categorias',
        'marcas',
        'categoriasEcommerce',
        'naturezaPadrao',
        'naturezas',
        'tributacao',
        'listaPreco',
        'cidades',
        'clientes',
        'config',
        'subDivisoes',
        'divisoes',
        'transportadoras',
        'produtos',
        'telasPedido',
        'item'
    ));
}

public function update(Request $request, $id)
{
        // dd($request);
    $numero_sequencial = 0;
    $last = RemessaNfe::where('empresa_id', $request->empresa_id)
    ->orderBy('id', 'desc')
    ->first();
    $numero_sequencial = $last != null ? ($last->numero_sequencial + 1) : 1;
    try {
        $result = DB::transaction(function () use ($request, $id) {
            $item = RemessaNfe::findOrFail($id);
            $dataNFe = [
                'cliente_id' => $request->cliente_id,
                'empresa_id' => $request->empresa_id,
                'usuario_id' => get_id_user(),
                'valor_total' => __convert_value_bd($request->valor_total),
                'forma_pagamento' => $request->tipo_pagamento,
                'numero_nfe' => 0,
                'natureza_id' => $request->natureza_id,
                'chave' => '',
                'estado_emissao' => 'novo',
                'observacao' => $request->obs ?? '',
                'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                'transportadora_id' => $request->transportadora_id,
                'sequencia_cce' => 0,
                'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                'data_entrega' => $request->data_entrega,
                'tipo_nfe' => $request->tipo_nfe,
                'nSerie' => 0,
                    // 'numero_sequencial' => $numero_sequencial,
                    // 'filial_id' => $request->filial_id != -1 ? $request->filial_id : null,
                'baixa_estoque' => $request->baixa_estoque,
                'gerar_conta_receber' => $request->gerar_conta_receber,
                'tipo' => $request->tipo,
                'placa' => $request->placa,
                'uf' => $request->uf,
                'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                'tipo_frete' => $request->tipo_frete,
                'qtd_volumes' => $request->qtd_volumes ? __convert_value_bd($request->qtd_volumes) : 0,
                'numeracao_volumes' => $request->numeracao_volumes ? __convert_value_bd($request->numeracao_volumes) : 0,
                'especie' => $request->especie,
                'peso_liquido' => $request->peso_liquido ? __convert_value_bd($request->peso_liquido) : 0,
                'peso_bruto' => $request->peso_bruto ? __convert_value_bd($request->peso_bruto) : 0,
                'data_retroativa' => $request->data_retroativa,
                'venda_caixa_id' => isset($request->venda_caixa_id) ? $request->venda_caixa_id : null
            ];
            $item->update($dataNFe);
            $item->itens()->delete();
            $item->referencias()->delete();
            for ($i = 0; $i < sizeof($request->quantidade); $i++) {
                $dataItem = [
                    'remessa_id' => $item->id,
                    'produto_id' => $request->produto_id[$i],
                    'quantidade' => __convert_value_bd($request->quantidade[$i]),
                    'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                    'sub_total' => __convert_value_bd($request->sub_total[$i]),
                    'cst_csosn' => $request->cst_csosn[$i],
                    'cst_pis' => $request->cst_pis[$i],
                    'cfop' => $request->cfop[$i],
                    'cst_cofins' => $request->cst_cofins[$i],
                    'cst_ipi' => $request->cst_ipi[$i],
                    'perc_icms' => $request->perc_icms[$i] ? __convert_value_bd($request->perc_icms[$i]) : 0,
                    'perc_pis' => $request->perc_pis[$i] ? __convert_value_bd($request->perc_pis[$i]) : 0,
                    'perc_cofins' => $request->perc_cofins[$i] ? __convert_value_bd($request->perc_cofins[$i]) : 0,
                    'perc_ipi' => $request->perc_ipi[$i] ? __convert_value_bd($request->perc_ipi[$i]) : 0,
                    'pRedBC' => $request->perc_red_bc[$i] ? __convert_value_bd($request->perc_red_bc[$i]) : 0,
                    'vbc_icms' => $request->vbc_icms[$i] ? __convert_value_bd($request->vbc_icms[$i]) : 0,
                    'vbc_pis' => $request->vbc_pis[$i] ? __convert_value_bd($request->vbc_pis[$i]) : 0,
                    'vbc_cofins' => $request->vbc_cofins[$i] ? __convert_value_bd($request->vbc_cofins[$i]) : 0,
                    'vbc_ipi' => $request->vbc_ipi[$i] ? __convert_value_bd($request->vbc_ipi[$i]) : 0,
                    'vBCSTRet' => $request->vBCSTRet[$i] ? __convert_value_bd($request->vBCSTRet[$i]) : 0,
                    'vFrete' => $request->vFrete[$i] ? __convert_value_bd($request->vFrete[$i]) : 0,
                    'modBCST' => $request->modBCST[$i] ? __convert_value_bd($request->modBCST[$i]) : 0,
                    'vBCST' => $request->vBCST[$i] ? __convert_value_bd($request->vBCST[$i]) : 0,
                    'pICMSST' => $request->pICMSST[$i] ? __convert_value_bd($request->pICMSST[$i]) : 0,
                    'vICMSST' => $request->vICMSST[$i] ? __convert_value_bd($request->vICMSST[$i]) : 0,
                    'pMVAST' => $request->pMVAST[$i] ? __convert_value_bd($request->pMVAST[$i]) : 0,
                    'x_pedido' => $request->x_pedido[$i] ?? '',
                    'num_item_pedido' => $request->num_item_pedido[$i] ?? '',
                    'cest' => $request->cest[$i] ?? '',
                    'valor_icms' => $request->valor_icms[$i] ? __convert_value_bd($request->valor_icms[$i]) : 0,
                    'valor_pis' => $request->valor_pis[$i] ? __convert_value_bd($request->valor_pis[$i]) : 0,
                    'valor_cofins' => $request->valor_cofins[$i] ? __convert_value_bd($request->valor_cofins[$i]) : 0,
                    'valor_ipi' => $request->valor_ipi[$i] ? __convert_value_bd($request->valor_ipi[$i]) : 0
                ];
                ItemRemessaNfe::create($dataItem);
            }
            for ($i = 0; $i < sizeof($request->chave_nfe); $i++) {
                if ($request->chave_nfe[$i]) {
                    $chave = str_replace(" ", "", $request->chave_nfe[$i]);
                    RemessaReferenciaNfe::create([
                        'remessa_id' => $item->id,
                        'chave' => $chave
                    ]);
                }
            }
            if ($request->valor_parcela) {
                for ($i = 0; $i < sizeof($request->valor_parcela); $i++) {
                    $fat = RemessaNfeFatura::create([
                        'remessa_id' => $item->id,
                        'tipo_pagamento' => $request->tipo_pagamento,
                        'valor' => __convert_value_bd($request->valor_parcela[$i]),
                        'data_vencimento' => $request->data_vencimento[$i]
                    ]);
                    if ($request->gerar_conta_receber) {
                        $item->fatura()->delete();
                        $item->duplicatas()->delete();
                        
                        $catVenda = $this->categoriaVenda();
                        ContaReceber::create([
                            'remessa_nfe_id' => $item->id,
                            'data_vencimento' => $request->data_vencimento[$i],
                            'data_recebimento' => $request->data_vencimento[$i],
                            'valor_integral' => __convert_value_bd($request->valor_parcela[$i]),
                            'cliente_id' => $request->cliente_id,
                            'valor_recebido' => 0,
                            'status' => false,
                            'tipo_pagamento' => $request->tipo_pagamento,
                            'referencia' => "Parcela $i+1, da NFe $item->id",
                            'categoria_id' => $catVenda,
                            'empresa_id' => $request->empresa_id
                        ]);
                    }
                }
            }
        });
session()->flash("flash_sucesso", "NFe atualizada!");
} catch (\Exception $e) {
    echo $e->getMessage() . '<br>' . $e->getLine();
    die;
    session()->flash("flash_erro", "Algo deu errado");
}
return redirect()->route('nferemessa.index');
}

public function destroy($id)
{
    $item = RemessaNfe::where('id', $id)
    ->first();
    if (valida_objeto($item)) {
        if ($item->baixa_estoque) {
            $this->reverteEstoque($item->itens);
        }
            // $this->removerDuplicadas($venda);
        $item->fatura()->delete();
        $item->itens()->delete();
        $item->referencias()->delete();
        $item->delete();
        session()->flash("flash_sucesso", "NFe removida!");

        return redirect()->route('nferemessa.index');
    } else {
        return redirect('/403');
    }
}


private function reverteEstoque($itens)
{
    $stockMove = new StockMove();
    foreach ($itens as $i) {
        if (!empty($i->produto->receita)) {
            $receita = $i->produto->receita;
            foreach ($receita->itens as $rec) {
                if (!empty($rec->produto->receita)) {
                    $receita2 = $rec->produto->receita;
                    foreach ($receita2->itens as $rec2) {
                        $stockMove->pluStock(
                            $rec2->produto_id,
                            (float) str_replace(",", ".", $i->quantidade) *
                            ($rec2->quantidade / $receita2->rendimento),
                            -1,
                            $itens[0]->venda->filial_id ?? null
                        );
                    }
                } else {
                    $stockMove->pluStock(
                        $rec->produto_id,
                        (float) str_replace(",", ".", $i->quantidade) *
                        ($rec->quantidade / $receita->rendimento),
                        -1,
                        $itens[0]->venda->filial_id ?? null
                    );
                }
            }
        } else {
            $stockMove->pluStock(
                $i->produto_id,
                (float) str_replace(",", ".", $i->quantidade),
                -1,
                $itens[0]->venda->filial_id ?? null
            );
        }
    }
}

public function estadoFiscal($id)
{
    $item = RemessaNfe::findOrFail($id);
    $value = session('user_logged');
    if ($value['adm'] == 0) return redirect()->back();

    if (valida_objeto($item)) {
        return view("nfe_remessa.state_fiscal", compact('item'));
    } else {
        return redirect('/403');
    }
}

public function updateState(Request $request, $id)
{
    try {
        $item = RemessaNfe::findOrFail($id);
        $estado_emissao = $request->estado_emissao;
        $item->estado_emissao = $estado_emissao;
        if ($request->hasFile('file')) {
            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            $xml = simplexml_load_file($request->file);
            $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
            $file = $request->file;
            $dhEmi = \Carbon\Carbon::parse($xml->NFe->infNFe->ide->dhEmi)->format('Y-m-d H:i');
            $file->move(public_path('xml_nfe'), $chave . '.xml');
            $item->chave = $chave;
            $item->data_emissao = $dhEmi;
            $item->numero_nfe = (int)$xml->NFe->infNFe->ide->nNF;

                // if ($item->filial_id != null) {
                //     $config = Filial::findOrFail($item->filial_id);
                //     $config->ultimo_numero_nfe = (int)$xml->NFe->infNFe->ide->nNF;
                //     $config->save();
                // }
        }
        $item->save();
        session()->flash("flash_sucesso", "Estado alterado");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Erro: " . $e->getMessage());
    }
    return redirect()->back();
}


public function imprimirCorrecao($id)
{
    $venda = RemessaNfe::findOrFail($id);
    if (!__valida_objeto($venda)) {
        abort(403);
    }
    if ($venda->sequencia_cce > 0) {
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        if (file_exists(public_path('xml_nfe_correcao/') . $venda->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe_correcao/') . $venda->chave . '.xml');
            if ($config->logo) {
                $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'logos/' . $config->logo));
            } else {
                $logo = null;
            }
            $dadosEmitente = $this->getEmitente();
            try {
                $daevento = new Daevento($xml, $dadosEmitente);
                $daevento->debugMode(true);
                $pdf = $daevento->render($logo);

                header("Content-Disposition: ; filename=CCe $venda->numero_nfe");
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            echo "Arquivo XML não encontrado!!";
        }
    } else {
        echo "<center><h1>Este documento não possui evento de correção!<h1></center>";
    }
}

private function getEmitente()
{
    $config = ConfigNota::where('empresa_id', request()->empresa_id)
    ->first();
    return [
        'razao' => $config->razao_social,
        'logradouro' => $config->logradouro,
        'numero' => $config->numero,
        'complemento' => '',
        'bairro' => $config->bairro,
        'CEP' => $config->cep,
        'municipio' => $config->municipio,
        'UF' => $config->UF,
        'telefone' => $config->telefone,
        'email' => ''
    ];
}

public function imprimirCancelamento($id)
{
    $venda = RemessaNfe::findOrFail($id);
    if (!__valida_objeto($venda)) {
        abort(403);
    }
    if ($venda->estado_emissao == 'cancelado') {
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        if (file_exists(public_path('xml_nfe_cancelada/') . $venda->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_nfe_cancelada/') . $venda->chave . '.xml');
            if ($config->logo) {
                $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'uploads/configEmitente' . $config->logo));
            } else {
                $logo = null;
            }
            $dadosEmitente = $this->getEmitente();
            try {
                $daevento = new Daevento($xml, $dadosEmitente);
                $daevento->debugMode(true);
                $pdf = $daevento->render($logo);
                header("Content-Disposition: ; filename=Cancelamento $venda->numero_nfe");
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            echo "Arquivo XML não encontrado!!";
        }
    } else {
        echo "<center><h1>Este documento não possui evento de cancelamento!<h1></center>";
    }
}

public function enviarXml(Request $request)
{
    $email = $request->email;
    $id = $request->venda_id;
    if (!is_dir(public_path('vendas_temp'))) {
        mkdir(public_path('vendas_temp'), 0777, true);
    }
    $venda = RemessaNfe::where('id', $id)
    ->where('empresa_id', request()->empresa_id)
    ->first();
    $config = ConfigNota::where('empresa_id', $request->empresa_id)
    ->first();
    $p = view('nfe_remessa.print' . compact('config', 'venda'));
    $domPdf = new Dompdf(["enable_remote" => true]);
    $domPdf->loadHtml($p);
    $pdf = ob_get_clean();
    $domPdf->setPaper("A4");
    $domPdf->render();

    file_put_contents(public_path('vendas_temp/') . 'PEDIDO_' . $venda->id . '.pdf', $domPdf->output());
    if ($venda->chave != "") {
        $this->criarPdfParaEnvio($venda);
    }
    $value = session('user_logged');
    if ($config->usar_email_proprio) {
        $send = $this->enviaEmailPHPMailer($venda, $email, $config);
        if (isset($send['erro'])) {
            return response()->json($send['erro'], 401);
        }
        return "ok";
    } else {
        Mail::send('mail.xml_send', [
            'emissao' => $venda->data_registro, 'nf' => $venda->NfNumero,
            'valor' => $venda->valor_total, 'usuario' => $value['nome'], 'venda' => $venda, 'config' => $config
        ], function ($m) use ($venda, $email) {
            $nomeEmpresa = env('MAIL_NAME');
            $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
            $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
            $emailEnvio = env('MAIL_USERNAME');
            $m->from($emailEnvio, $nomeEmpresa);
            $subject = "Envio de Pedido #$venda->id";
            if ($venda->NfNumero > 0) {
                $subject .= " | NFe: $venda->NfNumero";
            }
            $m->subject($subject);
            if ($venda->chave != "") {
                $m->attach(public_path('xml_nfe/') . $venda->chave . '.xml');
                $m->attach(public_path('pdf/') . 'DANFE.pdf');
            }
            $m->attach(public_path('vendas_temp/') . 'PEDIDO_' . $venda->id . '.pdf');
            $m->to($email);
        });
        return "ok";
    }
}

private function criarPdfParaEnvio($venda)
{
    $xml = file_get_contents(public_path('xml_nfe/') . $venda->chave . '.xml');
    $config = ConfigNota::where('empresa_id', request()->empresa_id)
    ->first();
    if ($config->logo) {
        $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'uploads/configEmitente' . $config->logo));
    } else {
        $logo = null;
    }
        // $docxml = FilesFolders::readFile($xml);
    try {
        $danfe = new Danfe($xml);
            // $id = $danfe->monta($logo);
        $pdf = $danfe->render($logo);
        header('Content-Type: application/pdf');
        file_put_contents(public_path('pdf/') . 'DANFE.pdf', $pdf);
    } catch (InvalidArgumentException $e) {
        echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
    }
}

private function enviaEmailPHPMailer($venda, $email, $config)
{
    $emailConfig = EmailConfig::where('empresa_id', request()->empresa_id)
    ->first();
    if ($emailConfig == null) {
        return [
            'erro' => 'Primeiramente configure seu email'
        ];
    }
    $public = env('SERVIDOR_WEB') ? 'public/' : '';
    $value = session('user_logged');
    $mail = new PHPMailer(true);
    try {
        if ($emailConfig->smtp_debug) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
        $mail->isSMTP();
        $mail->Host = $emailConfig->host;
        $mail->SMTPAuth = $emailConfig->smtp_auth;
        $mail->Username = $emailConfig->email;
        $mail->Password = $emailConfig->senha;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $emailConfig->porta;

        $mail->setFrom($emailConfig->email, $emailConfig->nome);
        $mail->addAddress($email);

        $mail->addAttachment($public . 'vendas_temp/PEDIDO_' . $venda->id . '.pdf');

        $mail->addAttachment($public . 'pdf/DANFE.pdf');

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $mail->Subject = "Envio de Pedido #$venda->id";
        $body = view('mail.xml_send', [
            'emissao' => $venda->data_registro, 'nf' => $venda->NfNumero,
            'valor' => $venda->valor_total, 'usuario' => $value['nome'], 'venda' => $venda, 'config' => $config
        ]);
        $mail->Body = $body;
        $mail->send();
        return [
            'sucesso' => true
        ];
    } catch (\Exception $e) {
        return [
            'erro' => $mail->ErrorInfo
        ];
            // echo "Message could; not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
}
