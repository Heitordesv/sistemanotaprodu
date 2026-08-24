<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Models\VendaCaixa;
use App\Services\PdvListaPrecoService;
use Illuminate\Http\Request;

class FrontBoxController extends Controller
{
    public function linhaProdutoVenda(
        Request $request,
        PdvListaPrecoService $listaPrecoService
    ) {
        $request->validate([
            'product_id' => 'required|integer',
            'empresa_id' => 'required|integer',
            'lista_preco_id' => 'nullable|integer',
            'qtd' => 'required',
            'value_unit' => 'required',
            'sub_total' => 'required',
        ]);

        $qtd = $request->qtd;
        $value_unit = __convert_value_bd($request->value_unit);
        $sub_total = __convert_value_bd($request->sub_total);
        $key = $request->key;

        $product = Produto::with('categoria')
            ->where('id', (int) $request->product_id)
            ->where('empresa_id', (int) $request->empresa_id)
            ->firstOrFail();

        if ($request->filled('lista_preco_id')) {
            $value_unit = $listaPrecoService->precoPdv(
                $product,
                (int) $request->lista_preco_id,
                (int) $request->empresa_id
            );
            $sub_total = round(
                __convert_value_bd($request->qtd) * $value_unit,
                2
            );
        }

        return view(
            'frontBox.partials.row_frontBox',
            compact('product', 'qtd', 'value_unit', 'sub_total', 'key')
        );
    }

    public function linhaParcelaVenda(Request $request)
    {
        $request->validate([
            'tipo_pagamento_row' => 'required|string',
            'data_vencimento_row' => 'required|date',
            'valor_integral_row' => 'required',
        ]);

        $tipo_pagamento_row = $request->tipo_pagamento_row;
        $data_vencimento_row = $request->data_vencimento_row;
        $valor_integral_row = $request->valor_integral_row;
        $quantidade = $request->quantidade;
        $obs_row = $request->obs_row;
        $tipo = VendaCaixa::getTipoPagamento($tipo_pagamento_row);

        return view(
            'frontBox.partials.row_pagMulti',
            compact(
                'valor_integral_row',
                'data_vencimento_row',
                'quantidade',
                'tipo',
                'obs_row',
                'tipo_pagamento_row'
            )
        );
    }
}