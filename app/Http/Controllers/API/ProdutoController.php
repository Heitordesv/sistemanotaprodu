<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\DivisaoGrade;
use App\Models\Empresa;
use App\Models\Estoque;
use App\Services\PdvListaPrecoService;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function getBarcode()
    {
        try {
            $rand = rand(11111, 99999);
            $code = $this->incluiDigito('7891000' . $rand);
            return response()->json($code, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    private function incluiDigito($code)
    {
        $weightflag = true;
        $sum = 0;
        for ($i = strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int)$code[$i] * ($weightflag ? 3 : 1);
            $weightflag = !$weightflag;
        }
        return $code . (10 - ($sum % 10)) % 10;
    }

    public function store(Request $request)
    {
        try {
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
                'perc_reducao' => $request->perc_reducao ?? 0,
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
                'conversao_unitaria' => $request->conversao_unitaria ?? 1,
                'cBenef' => $request->cBenef ?? 0,
                'imagem' => '',
                'perc_icms_interestadual' => $request->perc_icms_interestadual ?? 0,
                'perc_icms_interno' => $request->perc_icms_interno ?? 0,
                'perc_fcp_interestadual' => $request->perc_fcp_interestadual ?? 0,
                'alerta_vencimento' => $request->alerta_vencimento ?? 0,
                'unidade_compra' => 'UN',
                'unidade_venda' => 'UN',
                'valor_locacao' => $request->valor_locacao ?? 0,
                'CFOP_entrada_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_entrada_estadual,
                'CFOP_entrada_inter_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_entrada_inter_estadual,
            ]);

            $item = Produto::create($request->all());

            if ($request->estoque_inicial > 0) {
                // Estoque::create([
                //     'empresa_id' => $request->empresa_id,
                //     'produto_id' => $item->id,
                //     'quantidade' => $request->estoque_inicial,
                //     'valor_compra' => $request->valor_compra,
                // ]);
            }
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

public function pesquisa(Request $request)
{
    return $this->pesquisar($request, false);
}

public function pesquisaWeb(Request $request)
{
    return $this->pesquisar($request, true);
}

private function pesquisar(Request $request, bool $aceitarProdutoLegadoSemLocal)
{
    $filial_id = $aceitarProdutoLegadoSemLocal
        ? $this->normalizarFilialConsulta($request->input('filial_id'))
        : ($request->filial_id ?? null);

    $data = Produto::with(['estoque', 'categoria'])
        ->orderBy('nome', 'desc')
        ->where('produtos.empresa_id', $request->empresa_id)
        ->where(function($query) use ($request) {
            $query->where('produtos.nome', 'like', "%{$request->pesquisa}%")
                  ->orWhere('produtos.referencia', 'like', "%{$request->pesquisa}%")
                  ->orWhere('produtos.codBarras', 'like', "%{$request->pesquisa}%");
        })
        ->get();

    $temp = [];

    foreach ($data as $p) {

        $locais = json_decode((string) $p->locais, true);

        $p->estoqueAtual = $p->estoquePorLocalPavaVenda($filial_id);

        // ðŸ”¥ PREÃ‡OS PARA FRONT
        $precoOriginal = (float) $p->valor_venda;

        $desconto = 0;
        $temDesconto = false;
        $precoFinal = $precoOriginal;

        if (
            $p->categoria &&
            $p->categoria->desconto_ativo == 1 &&
            (float) $p->categoria->desconto > 0
        ) {
            $desconto = (float) $p->categoria->desconto;
            $temDesconto = true;

            $precoFinal = $precoOriginal - ($precoOriginal * $desconto / 100);
        }

        // ðŸ”¥ CAMPOS NOVOS PARA O FRONT
        $p->preco_original = $precoOriginal;
        $p->preco_com_desconto = $precoFinal;
        $p->desconto_percentual = $desconto;
        $p->tem_desconto = $temDesconto;

        if ($aceitarProdutoLegadoSemLocal && $this->produtoDisponivelNoLocal($locais, $filial_id)) {
            $temp[] = $p;
        } elseif (!$aceitarProdutoLegadoSemLocal && $filial_id) {
            foreach ((array) $locais as $local) {
                if ($local == $filial_id) {
                    $temp[] = $p;
                }
            }
        } elseif (!$aceitarProdutoLegadoSemLocal) {
            $temp[] = $p;
        }
    }

    return response()->json($temp, 200);
}

private function normalizarFilialConsulta($filialId)
{
    if ($filialId === null || $filialId === '' || $filialId === 'todos') {
        return null;
    }

    $filialId = (int) $filialId;

    return $filialId === -1 || $filialId > 0 ? $filialId : null;
}

private function produtoDisponivelNoLocal($locais, $filialId): bool
{
    // Produtos legados sem locais definidos continuam disponíveis. Quando um
    // local foi selecionado, produtos explicitamente vinculados a outros
    // locais permanecem ocultos.
    if ($filialId === null || !is_array($locais) || count($locais) === 0) {
        return true;
    }

    return in_array((string) $filialId, array_map('strval', $locais), true);
}
   
    public function find(
        $id,
        Request $request,
        PdvListaPrecoService $listaPrecoService
    ) {
        $query = Produto::with(['estoque', 'categoria'])
            ->where('id', (int) $id);

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', (int) $request->empresa_id);
        }

        $item = $query->firstOrFail();

        if ($request->filled('empresa_id')) {
            $listaPrecoService->aplicarAoProduto(
                $item,
                $request->filled('lista_preco_id')
                    ? (int) $request->lista_preco_id
                    : null,
                (int) $request->empresa_id
            );
        }

        return response()->json($item);
    }

    public function findByBarcode(
        Request $request,
        PdvListaPrecoService $listaPrecoService
    ) {
        $request->validate([
            'barcode' => 'required|string|max:64',
            'empresa_id' => 'required|integer',
            'lista_preco_id' => 'nullable|integer',
        ]);

        $item = Produto::with(['estoque', 'categoria'])
            ->where('codBarras', $request->barcode)
            ->where('empresa_id', $request->empresa_id)
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'Produto não encontrado para o código informado.',
            ], 404);
        }

        $listaPrecoService->aplicarAoProduto(
            $item,
            $request->filled('lista_preco_id')
                ? (int) $request->lista_preco_id
                : null,
            (int) $request->empresa_id
        );

        return response()->json($item, 200);
    }

// /**
//  * Busca um produto baseado no cÃ³digo de barras da balanÃ§a
//  */
// public function findByBarcodeReference(Request $request)
// {
//     // $config = ConfigCaixa::where('usuario_id', $request->usuario_id)->first();
//     // $balanca_valor_peso = $config != null ? $config->balanca_valor_peso : 0;
//     // $balanca_digito_verificador = $config != null ? $config->balanca_digito_verificador : 6;
//     // $barcode = $request->barcode;
//     // $ref = (int)substr($barcode, 1, $balanca_digito_verificador);
//     // $valor = (float)substr($barcode, 7, 12);
//     // $valor = $valor / 1000;
//     // $quantidade = 1;
//     // $item = Produto::with('estoque')
//     //     ->where('referencia_balanca', $ref)
//     //     ->where('empresa_id', $request->empresa_id)
//     //     ->first();
//     // if (is_null($item)) {
//     //     return response()->json([
//     //         'message' => 'Produto com referÃªncia de balanÃ§a ' . $ref . ' nÃ£o encontrado para esta empresa.',
//     //         'status' => 'error'
//     //     ], 404);
//     // }
//     // if ($item->unidade_venda == 'KG') {
//     //     if ($balanca_valor_peso == 1) {
//     //         $quantidade = $valor / $item->valor_venda;
//     //         $valor = $valor;
//     //     } else {
//     //         $quantidade = $valor / 10;
//     //         $valor = $item->valor_venda * $quantidade;
//     //     }
//     // }
//     // $item->valor = $valor;
//     // $item->quantidade = $quantidade;
//     // $item->valor_venda_calculado = $valor;
//     // return response()->json($item, 200);
// }
    public function findByBarcodeReference(Request $request)
    {
        $barcode = $request->barcode;

        if (!str_starts_with($barcode, '2') || strlen($barcode) != 13) {
            return response()->json(['message' => 'Código de balança inválido', 'status' => 'error'], 400);
        }

        // 1. Extração da Referência e do Valor da Etiqueta
        $refNova = (int)substr($barcode, 1, 6); 
        // Captura 00353 da Banana ou 00974 do Queijo e divide por 100
        $valorTotalEtiqueta = (float)substr($barcode, 7, 5) / 100;

        // 2. Busca do Produto
        $item = Produto::with(['estoque', 'categoria'])
            ->where('empresa_id', $request->empresa_id)
            ->where(function($q) use ($refNova) {
                $q->where('referencia_balanca', $refNova)
                ->orWhere('referencia_balanca', str_pad($refNova, 5, '0', STR_PAD_LEFT))
                ->orWhere('referencia_balanca', str_pad($refNova, 6, '0', STR_PAD_LEFT))
                ->orWhere('referencia', $refNova);
            })
            ->first();

        if (!$item) {
            return response()->json(['message' => "Produto REF $refNova não encontrado.", 'status' => 'error'], 404);
        }

        // 3. Lógica de Cálculo (KG vs UNIDADE)
        $quantidade = 1;
        $valorUnitarioOriginal = $item->valor_venda;
        $valorFinal = $valorTotalEtiqueta;

        // Se o produto for vendido por peso (KG)
        if (strtoupper($item->unidade_venda) == 'KG' || strtoupper($item->unidade_venda) == 'KILO') {
            // Quantidade = Valor Total da Etiqueta / Preço do quilo no sistema
            // Ex Banana: 3.53 / 7.00 = 0.504 kg
            if ($item->valor_venda > 0) {
                $quantidade = $valorTotalEtiqueta / $item->valor_venda;
            }
        } else {
            // Se for unidade, a quantidade é 1 e o valor é o que está na etiqueta
            $quantidade = 1;
        }

        // 4. Preparação do retorno para o seu JavaScript
        $item->quantidade = round($quantidade, 3);
        $item->valor = (
            strtoupper($item->unidade_venda) == 'KG' ||
            strtoupper($item->unidade_venda) == 'KILO'
        )
            ? round((float) $valorUnitarioOriginal, 2)
            : round((float) $valorTotalEtiqueta, 2);
        $item->valor_venda_calculado = round($valorFinal, 2); // subtotal da etiqueta

        return response()->json($item, 200);
    }
    public function linhaProdutoCompra(Request $request)
    {
        try {
            $qtd = $request->qtd;
            $value_unit = __convert_value_bd($request->value_unit);
            $sub_total = __convert_value_bd($request->sub_total);
            $product_id = $request->product_id;

            $product = Produto::findOrFail($product_id);
            $rand = rand(0, 10000000);
            return view('compra_manual.partials.row_product_purchase', 
                compact('product', 'qtd', 'value_unit', 'sub_total', 'rand'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function linhaParcelaCompra(Request $request)
    {
        try {
            $vencimento = $request->vencimento;
            $valor_parcela = $request->valor_parcela;

            return view('compra_manual.partials.row_payment_purchase', compact('valor_parcela', 'vencimento'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function storeProdutoRapido(Request $request)
    {
        try {

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
                'info_tecnica_composto' => $request->info_tecnica_composto ?? '',
                'limite_maximo_desconto' => $request->limite_maximo_desconto ?? 0,
                'alerta_vencimento' => $request->alerta_vencimento ?? 0,
                'CEST' => $request->CEST ?? 0,
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
                'perc_iss' => $request->perc_iss ?? 0,
                'cBenef' => $request->cBenef ?? 0,
                'tela_pedido_id' => $request->tela_pedido_id != "" ? $request->tela_pedido_id : 0,
                'imagem' => '',
                'perc_icms_interestadual' => $request->perc_icms_interestadual ?? 0,
                'perc_icms_interno' => $request->perc_icms_interno ?? 0,
                'perc_fcp_interestadual' => $request->perc_fcp_interestadual ?? 0,
                'alerta_vencimento' => $request->alerta_vencimento ?? 0,
                'unidade_compra' => 'UN',
                'unidade_venda' => 'UN',
                'codigo_anp' => 0,
                'gerenciar_estoque' => 0,
                'CFOP_saida_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_saida_estadual,
                'CFOP_saida_inter_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_saida_inter_estadual,
                'CFOP_entrada_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_entrada_estadual,
                'CFOP_entrada_inter_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_entrada_inter_estadual,

            ]);
            $item = Produto::create($request->all());
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function linhaProdutoReceita(Request $request)
    {
        try {
            $qtd = $request->qtd;
            $product_id = $request->product_id;

            $item = Produto::findOrFail($product_id);
            return view('produtos.produtos_composto._row_product_receita', compact('item', 'qtd'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function montarGrade(Request $request)
    {
        $comb = $request->divisoes;
        $sub = $request->subDivisoes;

        return view('produtos.partials._grade', compact('comb', 'sub'));
    }

    public function findProdRemessa(Request $request)
    {
        $cliente = null;
        if (isset($request->cliente_id)) {
            $cliente = Cliente::where('id', $request->cliente_id)
                ->where('empresa_id', $request->empresa_id)
                ->first();
        }
        $item = Produto::where('id', $request->produto_id)
            ->where('empresa_id', $request->empresa_id)
            ->firstOrFail();

        $item->cfop_atual = $item->cfop_estadual;

        if ($cliente != null) {

            $empresa = Empresa::find($item->empresa_id);
            if ($empresa != null) {

                if ($empresa->cidade && $empresa->cidade->uf != $cliente->cidade->uf) {
                    $item->cfop_atual = $item->cfop_outro_estado;
                }
            }
        }
        return response()->json($item, 200);
    }
}
