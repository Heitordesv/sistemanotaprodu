<?php

namespace App\Http\Controllers;

use App\Helpers\StockMove;
use App\Models\Acessor;
use App\Models\Categoria;
use App\Models\CategoriaConta;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\Certificado;
use App\Models\Cidade;
use App\Models\Contigencia;
use App\Models\Cliente;
use App\Models\ConfigNota;
use App\Models\ContaReceber;
use App\Models\DivisaoGrade;
use App\Models\Empresa;
use App\Models\FormaPagamento;
use App\Models\Funcionario;
use App\Models\GrupoCliente;
use App\Models\ItemOrcamento;
use App\Models\ItemVenda;
use App\Models\ListaPreco;
use App\Models\Marca;
use App\Models\Frete;
use App\Models\NaturezaOperacao;
use App\Models\Orcamento;
use App\Models\Venda;
use App\Models\Pais;
use App\Models\Produto;
use App\Models\TelaPedido;
use App\Models\Transportadora;
use App\Models\Tributacao;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NFService;
use Faker\Core\File as CoreFile;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\DanfeSimples;
use File;
use Illuminate\Http\File as HttpFile;
use Dompdf\Dompdf;

class VendaController extends Controller
{
    public function index(Request $request)
    {
        if (!is_dir(public_path('xml_nfe'))) {
            mkdir(public_path('xml_nfe'), 0777, true);
        }
        if (!is_dir(public_path('xml_nfe_cancelada'))) {
            mkdir(public_path('xml_nfe_cancelada'), 0777, true);
        }
        if (!is_dir(public_path('xml_nfe_correcao'))) {
            mkdir(public_path('xml_nfe_correcao'), 0777, true);
        }
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
        $data = Venda::where('empresa_id', $request->empresa_id)
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

        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        $contigencia = $this->getContigencia(request()->empresa_id);
        return view('vendas.index', compact('data', 'config', 'contigencia', 'filial_id'));
    }

    private function getContigencia($empresa_id)
    {
        $active = Contigencia::where('empresa_id', $empresa_id)
        ->where('status', 1)
        ->where('documento', 'NFe')
        ->first();
        return $active;
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
        $telasPedido = TelaPedido::where('empresa_id', $request->empresa_id)->get();
        $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        $cidades = Cidade::all();
        $transportadoras = Transportadora::where('empresa_id', $request->empresa_id)->get();
        return view('vendas.create', compact(
            'formaPagamento',
            'paises',
            'grupos',
            'telasPedido',
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
            'config',
            'subDivisoes',
            'divisoes',
            'transportadoras'
        ));
    }

    public function store(Request $request)
    {

        if ($request->type == 'venda') {
            try {
                $result = DB::transaction(function () use ($request) {
                    $valor_total = $this->somaItens($request);
                    $empresa = Empresa::findOrFail($request->empresa_id);
                    $natureza = NaturezaOperacao::findOrFail($request->natureza_id);
                    $frete_id = null;
                    if($request->tipo_frete != '9'){
                        $dataFrete = [
                            'valor' => __convert_value_bd($request->valor_frete),
                            'placa' => $request->placa_frete ?? '',
                            'tipo' => $request->tipo_frete,
                            'uf' => $request->uf_frete ?? '',
                            'numeracaoVolumes' => $request->n_volumes_frete ?? '',
                            'peso_liquido' => $request->peso_liquido_frete ? __convert_value_bd($request->peso_liquido_frete) : 0,
                            'peso_bruto' => $request->peso_bruto_frete ? __convert_value_bd($request->peso_bruto_frete) : 0,
                            'especie' => $request->especie_frete ?? '',
                            'qtdVolumes' => $request->q_volumes_frete ?? ''
                        ];
                        $frete = Frete::create($dataFrete);
                        $frete_id = $frete->id;
                    }
                    $request->merge([
                        'usuario_id' => get_id_user(),
                        'frete_id' => $frete_id,
                        'observacao' => $request->observacao ?? '',
                        'qtd_volumes' => $request->qtd_volumes ?? 0,
                        'peso_liquido' => $request->peso_liquido ?? 0,
                        'peso_bruto' => $request->peso_bruto ?? 0,
                        'transportadora_id' => $request->transportadora_id ? $request->transportadora_id : null,
                        'valor_total' => __convert_value_bd($valor_total),
                        'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                        'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                        'estado_emissao' => 'novo',
                        'sequencia_cce' => $request->sequencia_cce ?? 0,
                        'chave' => $request->chave ?? 0,
                        'tipo_pagamento' => $request->tipo_pagamentos[0],
                        'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                    ]);

                    $venda = Venda::create($request->all());

                    //verifica frete

                    $stockMove = new StockMove();
                    for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                        $product = Produto::findOrFail($request->produto_id[$i]);
                        $cfop = 0;
                        if ($natureza->sobrescreve_cfop) {
                            $cfop = $natureza->CFOP_saida_estadual;
                        } else {
                            $cfop = $product->CFOP_saida_estadual;
                        }
                        ItemVenda::create([
                            'venda_id' => $venda->id,
                            'produto_id' => (int)$request->produto_id[$i],
                            'quantidade' => __convert_value_bd($request->quantidade[$i]),
                            'cfop' => $cfop,
                            'valor' => __convert_value_bd($request->valor_unitario[$i]),
                            'valor_custo' => $product->valor_compra,
                            'x_pedido' => $request->x_pedido[$i],
                            'num_item_pedido' => $request->num_item_pedido[$i]
                        ]);
                        $stockMove->downStock(
                            $product->id,
                            __convert_value_bd($request->quantidade[$i]),
                            $request->filial_id
                        );
                    }
                    if ($request->forma_pagamento != 'a_vista') {
                        
                        for ($i = 0; $i < sizeof($request->data_vencimento); $i++) {
                            ContaReceber::create([
                                'venda_id' => $venda->id,
                                'cliente_id' => $request->cliente_id,
                                'data_vencimento' => $request->data_vencimento[$i],
                                'data_recebimento' => $request->data_vencimento[$i],
                                'valor_integral' => __convert_value_bd($request->valor_parcela[$i]),
                                'tipo_pagamento' => $request->tipo_pagamentos[$i],
                                'valor_recebido' => 0,
                                'status' => 0,
                                'referencia' => "Parcela $i+1 da Compra código $venda->id",
                                'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->where('tipo', 'receber')->first()->id,
                                'empresa_id' => $request->empresa_id,
                                'juros' => 0,
                                'multa' => 0,
                                'venda_caixa_id' => null,
                                'observacao' => '',
                                'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                            ]);
                        }
                    }
                    return true;
                });
session()->flash("flash_sucesso", "Venda adicionada com sucesso!");
} catch (\Exception $e) {
    echo $e->getMessage();
    die;
    session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
    __saveLogError($e, request()->empresa_id);
}
return redirect()->route('vendas.index');
} else {
            // ORCAMENTO
    try {
        $result = DB::transaction(function () use ($request) {
            $valor_total = $this->somaItens($request);
                    //$config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
            $today = today();
            $request->merge([
                'usuario_id' => get_id_user(),
                'observacao' => $request->observacao ?? '',
                'qtd_volumes' => $request->qtd_volumes ?? 0,
                'peso_liquido' => $request->peso_liquido ?? 0,
                'peso_bruto' => $request->peso_bruto ?? 0,
                'desconto' => $request->desconto ?? 0,
                'valor_total' => __convert_value_bd($valor_total),
                'estado' => 'NOVO',
                'sequencia_cce' => $request->sequencia_cce ?? 0,
                'chave' => $request->chave ?? 0,
                'acrescimo' => $request->acrescimo ?? 0,
                'email_enviado' => $request->email_enviado ?? 0,
                        //'validade_orcamento' => $config->validade_orcamento ?? 0,
                'validade' => date("Y-m-d", strtotime("$today +7 day")),
                'venda_id' => 0,
                'filial_id' => $request->filial_id != -1 ? $request->filial_id   : null
            ]);
            $orcamento = Orcamento::create($request->all());
            for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                $product = Produto::findOrFail($request->produto_id[$i]);
                ItemOrcamento::create([
                    'orcamento_id' => $orcamento->id,
                    'produto_id' => (int)$request->produto_id[$i],
                    'quantidade' => __convert_value_bd($request->quantidade[$i]),
                    'valor' => __convert_value_bd($request->valor_unitario[$i]),
                    'altura' => $request->altura ?? 0,
                    'largura' => $request->largura ?? 0,
                    'profundidade' => $request->profundidade ?? 0,
                    'acrescimo_perca' => $request->acrescimo_perca ?? 0,
                    'esquerda' => $request->esquerda ?? 0,
                    'direita' => $request->direita ?? 0,
                    'inferior' => $request->inferior ?? 0,
                    'superior' => $request->superior ?? 0
                ]);
            }
                    // if ($request->forma_pagamento != 'a_vista') {
                    //     for ($i = 0; $i < sizeof($request->data_vencimento); $i++) {

                    //         // ContaReceber::create([
                    //         //     'venda_id' => $venda->id,
                    //         //     'cliente_id' => $request->cliente_id,
                    //         //     'data_vencimento' => $request->data_vencimento[$i],
                    //         //     'data_recebimento' => $request->data_vencimento[$i],
                    //         //     'valor_integral' => __convert_value_bd($request->valor_integral[$i]),
                    //         //     'valor_recebido' => 0,
                    //         //     'status' => 0,
                    //         //     'referencia' => "Parcela $i+1 da Compra código $venda->id",
                    //         //     'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->first()->id,
                    //         //     'empresa_id' => $request->empresa_id,
                    //         //     'juros' => 0,
                    //         //     'multa' => 0,
                    //         //     'venda_caixa_id' => null,
                    //         //     'observacao' => '',
                    //         //     'tipo_pagamento' => $request->tipo_pagamento
                    //         // ]);
                    //     }
                    // }
                    // return true;
        });
        session()->flash("flash_sucesso", "Orçamento adicionado com sucesso!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }
    return redirect()->route('orcamentoVenda.index');
}
}

private function somaItens($request)
{
    $valor_total = 0;
    for ($i = 0; $i < sizeof($request->produto_id); $i++) {
        $valor_total += __convert_value_bd($request->subtotal_item[$i]);
    }
    return $valor_total;
}

public function edit(Request $request, $id)
{
    $item = Venda::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    $dataValidate = [
        'categorias', 'produtos', 'clientes'
    ];
    $util = new Util();
    $validateEntry = $util->validateEntry($dataValidate, $request->empresa_id);
    if ($validateEntry != null) {
        session()->flash("flash_erro", $validateEntry['message']);
        return redirect($validateEntry['route']);
    }
    $paises = Pais::all();
    $cidades = Cidade::all();
    $clientes = Cliente::where('empresa_id', $request->empresa_id)->get();
    $transportadoras = Transportadora::where('empresa_id', $request->empresa_id)->get();
    $categorias = Categoria::where('empresa_id', $request->empresa_id)->get();
    $marcas = Marca::where('empresa_id', $request->empresa_id)->get();
    $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', request()->empresa_id)->get();
    $naturezaPadrao = NaturezaOperacao::where('empresa_id', $request->empresa_id)
    ->first();
    $tributacao = Tributacao::where('empresa_id', $request->empresa_id)
    ->first();
    $listaPreco = ListaPreco::where('empresa_id', $request->empresa_id)->get();
    $subDivisoes = DivisaoGrade::where('empresa_id', $request->empresa_id)
    ->where('sub_divisao', true)
    ->get();
    $telasPedido = TelaPedido::where('empresa_id', $request->empresa_id)->get();

    $naturezas = NaturezaOperacao::where('empresa_id', $request->empresa_id)
    ->get();
    $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

    $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
    return view(
        'vendas.edit',
        compact(
            'clientes',
            'transportadoras',
            'categorias',
            'marcas',
            'categoriasEcommerce',
            'naturezaPadrao',
            'naturezas',
            'tributacao',
            'item',
            'listaPreco',
            'divisoes',
            'subDivisoes',
            'telasPedido',
            'config',
            'cidades',
            'paises'
        )
    );
}

private function _validate(Request $request)
{
    $rules = [
        'cliente_id' => 'required',
        'natureza_id' => 'required',
        'produto_id' => 'required'
    ];
    $messages = [
        'cliente_id.required' => 'Campo Obrigatório',
        'natureza_id.required' => 'Campo Obrigatório'
    ];
    $this->validate($request, $rules, $messages);
}


public function update(Request $request, $id)
{
    $this->_validate($request);
    if ($request->type == 'venda') {
        try {
            $result = DB::transaction(function () use ($request, $id) {
                $item = Venda::findOrFail($id);
                $natureza = NaturezaOperacao::findOrFail($request->natureza_id);
                $valor_total = $this->somaItens($request);
                $frete_id = null;
                $freteAux = $item->frete;
                if($request->tipo_frete != '9'){

                    $dataFrete = [
                        'valor' => __convert_value_bd($request->valor_frete),
                        'placa' => $request->placa_frete ?? '',
                        'tipo' => $request->tipo_frete,
                        'uf' => $request->uf_frete ?? '',
                        'numeracaoVolumes' => $request->n_volumes_frete ?? '',
                        'peso_liquido' => $request->peso_liquido_frete ? __convert_value_bd($request->peso_liquido_frete) : 0,
                        'peso_bruto' => $request->peso_bruto_frete ? __convert_value_bd($request->peso_bruto_frete) : 0,
                        'especie' => $request->especie_frete ?? '',
                        'qtdVolumes' => $request->q_volumes_frete ?? ''
                    ];
                    $frete = Frete::create($dataFrete);
                    $frete_id = $frete->id;
                }
                $request->merge([
                    'frete_id' => $frete_id,
                    'usuario_id' => get_id_user(),
                    'transportadora_id' => $request->transportadora_id ? $request->transportadora_id : null,
                    'observacao' => $request->observacao ?? '',
                    'qtd_volumes' => $request->qtd_volumes ?? 0,
                    'peso_liquido' => $request->peso_liquido ?? 0,
                    'peso_bruto' => $request->peso_bruto ?? 0,
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                    'valor_total' => $valor_total,
                    'sequencia_cce' => $request->sequencia_cce ?? 0,
                    'chave' => $request->chave ?? 0,
                    'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                ]);
                $item->fill($request->all())->save();
                $stockMove = new StockMove();
                $itens = $item->itens;
                $this->revertStock($itens);
                $item->itens()->delete();
                $item->duplicatas()->delete();
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    $cfop = 0;
                    if ($natureza->sobrescreve_cfop) {
                        $cfop = $natureza->CFOP_saida_estadual;
                    } else {
                        $cfop = $product->CFOP_saida_estadual;
                    }

                    ItemVenda::create([
                        'venda_id' => $id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'cfop' => $cfop,
                        'valor' => __convert_value_bd($request->valor_unitario[$i]),
                        'valor_custo' => $product->valor_compra,
                        'x_pedido' => $request->x_pedido[$i],
                        'num_item_pedido' => $request->num_item_pedido[$i]
                    ]);

                    $stockMove->downStock(
                        $product->id,
                        __convert_value_bd($request->quantidade[$i]),
                        $request->filial_id
                    );
                }

                if ($request->forma_pagamento != 'a_vista') {
                    for ($i = 0; $i < sizeof($request->data_vencimento); $i++) {
                        ContaReceber::create([
                            'venda_id' => $item->id,
                            'cliente_id' => $request->cliente_id,
                            'data_vencimento' => $request->data_vencimento[$i],
                            'data_recebimento' => $request->data_vencimento[$i],
                            'valor_integral' => __convert_value_bd($request->valor_parcela[$i]),
                            'valor_recebido' => 0,
                            'status' => 0,
                            'referencia' => "Parcela $i+1 da Compra código $item->id",
                            'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->where('tipo', 'receber')->first()->id,
                            'empresa_id' => $request->empresa_id,
                            'juros' => 0,
                            'multa' => 0,
                            'venda_caixa_id' => null,
                            'observacao' => '',
                            'tipo_pagamento' => $request->tipo_pagamento,
                            'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                        ]);
                    }
                }
                if($freteAux){
                    $freteAux->delete();
                }
                return true;
            });
session()->flash("flash_sucesso", "Venda atualizada com sucesso!");
} catch (\Exception $e) {
                echo $e->getMessage();
                echo $e->getLine();
                die;
    session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
    __saveLogError($e, request()->empresa_id);
}
}
return redirect()->route('vendas.index');
}

private function revertStock($itens)
{
    $stockMove = new StockMove();
    foreach ($itens as $i) {
        $stockMove->pluStock(
            $i->produto_id,
            __convert_value_bd($i->quantidade),
            $itens[0]->venda->filial_id
        );
    }
}

public function show(Request $request, $id)
{
    $item = Venda::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    return view('vendas.show', compact('item'));
}

public function importacao()
{
    return view('importacao.index');
}

public function destroy($id)
{
    $item = Venda::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    try {
        $this->revertStock($item->itens);
        $item->delete();
        session()->flash("flash_sucesso", "Venda deletada!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }
    return redirect()->route('vendas.index');
}

public function xmlTemp($id)
{
    $item = Venda::findOrFail($id);

    if (!__valida_objeto($item)) {
        abort(403);
    }
    $config = ConfigNota::where('empresa_id', request()->empresa_id)
    ->first();

    $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

    $nfe_service = new NFService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$config->ambiente,
        "razaosocial" => $config->razao_social,
        "siglaUF" => $config->cidade->uf,
        "cnpj" => $cnpj,
        "schemes" => "PL_009_V4",
        "versao" => "4.00",
        "tokenIBPT" => "AAAAAAA",
        "CSC" => $config->csc,
        "CSCid" => $config->csc_id
    ], $config);

    $nfe = $nfe_service->gerarNFe($item);
    if (!isset($nfe['erros_xml'])) {
        $xml = $nfe['xml'];
        return response($xml)
        ->header('Content-Type', 'application/xml');
    } else {
            // print_r($nfe['erros_xml']);
        foreach ($nfe['erros_xml'] as $err) {
            echo $err;
        }
    }
}

public function danfeTemp($id)
{
    $item = Venda::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    $config = ConfigNota::where('empresa_id', request()->empresa_id)
    ->first();
    $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
    $nfe_service = new NFService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$config->ambiente,
        "razaosocial" => $config->razao_social,
        "siglaUF" => $config->cidade->uf,
        "cnpj" => $cnpj,
        "schemes" => "PL_009_V4",
        "versao" => "4.00",
        "tokenIBPT" => "AAAAAAA",
        "CSC" => $config->csc,
        "CSCid" => $config->csc_id
    ], $config);
    $nfe = $nfe_service->gerarNFe($item);
    if (!isset($nfe['erros_xml'])) {
        $xml = $nfe['xml'];
        try {
            $logo = null;
            $danfe = new Danfe($xml);
            $danfe->setVUnComCasasDec($config->casas_decimais);
            $pdf = $danfe->render($logo);
            header("Content-Disposition: ; filename=DANFE TEMPORÁRIA");
            return response($pdf)
            ->header('Content-Type', 'application/pdf');
        } catch (InvalidArgumentException $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    } else {
        print_r($nfe['erros_xml']);
    }
}

public function importStore(Request $request)
{
    $tabela = $request->tabela;
    $data = json_decode($request->data);
    $public = env('SERVIDOR_WEB') ? 'public/' : '';
    foreach ($data as $d) {
        if ($request->input('ch_' . $d->chave)) {
            $cliente = json_decode(json_encode($d->cliente), true);
            if ($cliente) {
                $cliente = $this->insereCliente($cliente);
            } else {
            }
            $produtos = json_decode(json_encode($d->produtos), true);
            $itens = $this->insereProdutos($produtos);
            if ($tabela == 'vendas') {
                if ($cliente != null) {
                    $vendaId = $this->salvarVenda($d, $cliente, $produtos);
                    $this->gravarItensVenda($vendaId, $itens);

                    $fatura = json_decode(json_encode($d->fatura), true);
                    $this->salvarFatura($vendaId, $fatura);

                    File::copy($d->file, $public . "xml_nfe/" . $d->chave . ".xml");
                }
            } else {
                $vendaId = $this->salvarVendaCaixa($d, $produtos);
                $this->gravarItensVendaCaixa($vendaId, $itens);

                File::copy($d->file, $public . "xml_nfce/" . $d->chave . ".xml");
            }
        }
    }
    session()->flash('flash_sucesso', 'Importação concluida!!');
    return redirect()->route('vendas.importacao');
}



public function gerarXml($id)
{
    $certificado = Certificado::where('empresa_id', request()->empresa_id)
    ->first();

    if ($certificado == null) {
        echo "Necessário o certificado para realizar esta ação!";
        die;
    }
    $venda = Venda::find($id);

    if (valida_objeto($venda)) {
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->UF,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
        ], $config);
        $nfe = $nfe_service->gerarNFe($id);
        if (!isset($nfe['erros_xml'])) {
            $xml = $nfe_service->sign($nfe['xml']);

            return response($xml)
            ->header('Content-Type', 'application/xml');
        } else {
            foreach ($nfe['erros_xml'] as $e) {
                echo $e;
            }
        }
    } else {
        return redirect('/403');
    }
}

public function print($id)
{
    $item = Venda::find($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    $config = ConfigNota::where('empresa_id', $item->empresa_id)
    ->first();
    $p = view('vendas.print', compact('config', 'item'));
    $domPdf = new Dompdf(["enable_remote" => true]);
    $domPdf->loadHtml($p);
    $pdf = ob_get_clean();
    $domPdf->setPaper("A4");
    $domPdf->render();
    $domPdf->stream("Pedido de Venda $id.pdf", array("Attachment" => false));
}

public function clone($id)
{
    $item = Venda::find($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }

    $semEstoque = $this->validaEstoque($item);

    $config = ConfigNota::where('empresa_id', request()->empresa_id)
    ->first();

    return view('vendas.clone', compact('config', 'semEstoque', 'item'));
}

private function validaEstoque($venda)
{
    $semEstoque = [];
    foreach ($venda->itens as $item) {
        $p = $item->produto;
        $qtdDisponivel = $p->estoquePorLocal($item->filial_id);
        if ($item->quantidade > $qtdDisponivel && $p->gerenciar_estoque) {
            array_push($semEstoque, $p);
        }
    }

    return $semEstoque;
}

public function clonarPut(Request $request, $id)
{
    $venda = Venda::findOrFail($id);
    $cliente_id = $request->cliente_id;

    if (!__valida_objeto($venda)) {
        abort(403);
    }

    if (!$cliente_id) {
        session()->flash("flash_erro", "Informe o cliente!");
        return redirect()->back();
    }

    $freteId = null;
    if ($venda->frete_id != NULL) {
        $frete = Frete::create([
            'placa' => $venda->frete->placa,
            'valor' => $venda->frete->valor,
            'tipo' => $venda->frete->tipo,
            'qtdVolumes' => $venda->frete->qtdVolumes,
            'uf' => $venda->frete->uf,
            'numeracaoVolumes' => $venda->frete->numeracaoVolumes,
            'especie' => $venda->frete->especie,
            'peso_liquido' => $venda->frete->peso_liquido,
            'peso_bruto' => $venda->frete->peso_bruto
        ]);
        $freteId = $frete->id;
    }

    $novaVenda = [
        'cliente_id' => $cliente_id,
        'usuario_id' => get_id_user(),
        'frete_id' => $freteId,
        'valor_total' => $venda->valor_total,
        'forma_pagamento' => $venda->forma_pagamento,
        'numero_nfe' => 0,
        'natureza_id' => $venda->natureza_id,
        'chave' => '',

        'estado_emissao' => 'novo',
        'observacao' => $venda->observacao,
        'desconto' => $venda->desconto,
        'acrescimo' => $venda->acrescimo,
        'transportadora_id' => $venda->transportadora_id,
        'sequencia_cce' => 0,
        'tipo_pagamento' => $venda->tipo_pagamento,
        'empresa_id' => $request->empresa_id,
        'bandeira_cartao' => $venda->bandeira_cartao,
        'cAut_cartao' => $venda->cAut_cartao,
        'cnpj_cartao' => $venda->cnpj_cartao,
        'descricao_pag_outros' => $venda->descricao_pag_outros,
        'filial_id' => $venda->filial_id
    ];

    $result = Venda::create($novaVenda);

    $itens = $venda->itens;
    $stockMove = new StockMove();
    foreach ($itens as $i) {
        ItemVenda::create([
            'venda_id' => $result->id,
            'produto_id' => $i->produto_id,
            'quantidade' => $i->quantidade,
            'valor' => $i->valor,
            'cfop' => $i->cfop,
            'valor_custo' => $i->produto->valor_compra,
            'x_pedido' => $i->x_pedido,
            'num_item_pedido' => $i->num_item_pedido

        ]);

        $prod = Produto
        ::where('id', $i->produto_id)
        ->first();

        if (!empty($prod->receita)) {

            $receita = $prod->receita;
            foreach ($receita->itens as $rec) {

                if (!empty($rec->produto->receita)) {
                    $receita2 = $rec->produto->receita;

                    foreach ($receita2->itens as $rec2) {
                        $stockMove->downStock(
                            $rec2->produto_id,
                            $i->quantidade *
                            ($rec2->quantidade / $receita2->rendimento),
                            $venda->filial_id
                        );
                    }
                } else {

                    $stockMove->downStock(
                        $rec->produto_id,
                        $i->quantidade *
                        ($rec->quantidade / $receita->rendimento),
                        $venda->filial_id
                    );
                }
            }
        } else {
            $stockMove->downStock(
                $i->produto_id,
                $i->quantidade,
                $venda->filial_id
            );
        }
    }

    if ($venda->forma_pagamento != 'a_vista' && $venda->forma_pagamento != 'conta_crediario') {
        $fatura = $venda->duplicatas;

        foreach ($fatura as $key => $f) {
            $valorParcela = str_replace(",", ".", $f['valor']);

            $resultFatura = ContaReceber::create([
                'venda_id' => $result->id,
                'data_vencimento' => $f->data_vencimento,
                'data_recebimento' => $f->data_recebimento,
                'valor_integral' => $f->valor_integral,
                'valor_recebido' => 0,
                'tipo_pagamento' => $f->tipo_pagamento,
                'status' => false,
                'entrada' => $f['entrada'],
                'referencia' => "Parcela " . ($key + 1) . "/" . sizeof($fatura) . ", da Venda " . $result->id,
                'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->where('tipo', 'receber')->first()->id,
                'empresa_id' => $request->empresa_id
            ]);
        }
    }

    session()->flash("flash_sucesso", "Venda duplicada com sucesso!");
    return redirect()->route('vendas.index');
}
}
