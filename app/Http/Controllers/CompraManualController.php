<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\NaturezaOperacao;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\Transportadora;
use App\Models\Tributacao;
use App\Models\Compra;
use App\Models\ItemCompra;
use App\Models\ContaPagar;
use App\Models\CategoriaConta;
use Illuminate\Http\Request;
use App\Utils\Util;
use App\Helpers\StockMove;
use App\Models\DivisaoGrade;
use App\Models\TelaPedido;
use Illuminate\Support\Facades\DB;

class CompraManualController extends Controller
{
    public function index(Request $request)
    {
        $dataValidate = [
            'categorias', 'produtos', 'fornecedors'
        ];
        $util = new Util();
        $validateEntry = $util->validateEntry($dataValidate, $request->empresa_id);
        if ($validateEntry != null) {
            session()->flash("flash_erro", $validateEntry['message']);
            return redirect($validateEntry['route']);
        }
        $transportadoras = Transportadora::where('empresa_id', $request->empresa_id)->get();
        $categorias = Categoria::where('empresa_id', $request->empresa_id)->get();
        $marcas = Marca::where('empresa_id', $request->empresa_id)->get();
        $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', $request->empresa_id)->get();
        $naturezaPadrao = NaturezaOperacao::where('empresa_id', $request->empresa_id)
            ->first();
        $tributacao = Tributacao::where('empresa_id', $request->empresa_id)
            ->first();
        $fornecedores = Fornecedor::where('empresa_id', $request->empresa_id)->get();
        $divisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
            ->where('sub_divisao', false)
            ->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
            ->where('sub_divisao', true)
            ->get();
        $telasPedido = TelaPedido::where('empresa_id', request()->empresa_id)->get();
        return view(
            'compra_manual.create',
            compact(
                'fornecedores',
                'transportadoras',
                'categorias',
                'marcas',
                'categoriasEcommerce',
                'naturezaPadrao',
                'tributacao',
                'divisoes',
                'subDivisoes',
                'telasPedido'
            )
        );
    }

    public function store(Request $request)
    {
        try {
            $result = DB::transaction(function () use ($request) {
                $total = $this->somaItens($request);
                $request->merge([
                    'usuario_id' => get_id_user(),
                    'valor_frete' => $request->valor_frete ?? 0,
                    'qtd_volumes' => $request->qtd_volumes ?? 0,
                    'peso_liquido' => $request->peso_liquido ?? 0,
                    'peso_bruto' => $request->peso_bruto ?? 0,
                    'desconto' => $request->desconto ?? 0,
                    'total' => $total,
                    'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                ]);
                $compra = Compra::create($request->all());
                $stockMove = new StockMove();
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    ItemCompra::create([
                        'compra_id' => $compra->id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                        'unidade_compra' => $product->unidade_compra,
                    ]);
                    $product->valor_compra = __convert_value_bd($request->valor_unitario[$i]);
                    if ($product->reajuste_automatico) {
                        $product->valor_venda = $product->valor_compra +
                            (($product->valor_compra * $product->percentual_lucro) / 100);
                    }
                    $product->save();
                    $stockMove->pluStock(
                        $product->id,
                        __convert_value_bd($request->quantidade[$i]),
                        __convert_value_bd($request->valor_unitario[$i])
                    );
                }
                if ($request->forma_pagamento != 'a_vista') {
                    for ($i = 0; $i < sizeof($request->vencimento_parcela); $i++) {
                        ContaPagar::create([
                            'compra_id' => $compra->id,
                            'fornecedor_id' => $request->fornecedor_id,
                            'data_vencimento' => $request->vencimento_parcela[$i],
                            'data_pagamento' => $request->vencimento_parcela[$i],
                            'valor_integral' => __convert_value_bd($request->valor_parcela[$i]),
                            'valor_pago' => 0,
                            'status' => 0,
                            'referencia' => "Parcela $i+1 da Compra código $compra->id",
                            'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->first()->id,
                            'empresa_id' => $request->empresa_id
                        ]);
                    }
                }
                return true;
            });
            session()->flash("flash_sucesso", "Compra adicionada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('compras.index');
    }

    private function somaItens($request)
    {
        $total = 0;
        for ($i = 0; $i < sizeof($request->produto_id); $i++) {
            $total += __convert_value_bd($request->subtotal_item[$i]);
        }
        return $total;
    }


    public function update(Request $request, $id)
    {
        try {
            $result = DB::transaction(function () use ($request, $id) {
                $item = Compra::findOrFail($id);
                $total = $this->somaItens($request);
                $request->merge([
                    'usuario_id' => get_id_user(),
                    'valor_frete' => $request->valor_frete ? __convert_value_bd($request->valor_frete) : 0,
                    'qtd_volumes' => $request->qtd_volumes ? __convert_value_bd($request->qtd_volumes) : 0,
                    'peso_liquido' => $request->peso_liquido ? __convert_value_bd($request->peso_liquido) : 0,
                    'peso_bruto' => $request->peso_bruto ? __convert_value_bd($request->peso_bruto) : 0,
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'total' => $total,
                    'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                ]);
                $item->fill($request->all())->save();
                $stockMove = new StockMove();
                $this->revertStock($request);
                $item->itens()->delete();
                $item->fatura()->delete();
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    ItemCompra::create([
                        'compra_id' => $item->id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                        'unidade_compra' => $product->unidade_compra,
                    ]);
                    $product->valor_compra = __convert_value_bd($request->valor_unitario[$i]);
                    if ($product->reajuste_automatico) {
                        $product->valor_venda = $product->valor_compra +
                            (($product->valor_compra * $product->percentual_lucro) / 100);
                    }
                    $product->save();
                    $stockMove->pluStock(
                        $product->id,
                        __convert_value_bd($request->quantidade[$i]),
                        __convert_value_bd($request->valor_unitario[$i])
                    );
                }
                if ($request->forma_pagamento != 'a_vista') {
                    for ($i = 0; $i < sizeof($request->vencimento_parcela); $i++) {
                        ContaPagar::create([
                            'compra_id' => $item->id,
                            'fornecedor_id' => $request->fornecedor_id,
                            'data_vencimento' => $request->vencimento_parcela[$i],
                            'data_pagamento' => $request->vencimento_parcela[$i],
                            'valor_integral' => __convert_value_bd($request->valor_parcela[$i]),
                            'valor_pago' => 0,
                            'status' => 0,
                            'referencia' => "Parcela $i+1 da Compra código $item->id",
                            'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->first()->id,
                            'empresa_id' => $request->empresa_id
                        ]);
                    }
                }
                return true;
            });
            session()->flash("flash_sucesso", "Compra atualizada com sucesso!");
        } catch (\Exception $e) {
            // echo $e->getMessage();
            // echo $e->getLine();
            // die;
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('compras.index');
    }

    private function revertStock($request)
    {
        $stockMove = new StockMove();
        for ($i = 0; $i < sizeof($request->produto_id); $i++) {
            $stockMove->downStock(
                $request->produto_id[$i],
                __convert_value_bd($request->quantidade[$i])
            );
        }
    }
}
