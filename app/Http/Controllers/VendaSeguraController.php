<?php

namespace App\Http\Controllers;

use App\Exceptions\CaixaMovimentacaoException;
use App\Helpers\StockMove;
use App\Models\CategoriaConta;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\Frete;
use App\Models\ItemVenda;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\Venda;
use App\Services\VendaCaixaEdicaoService;
use App\Services\VendaTenantGuardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class VendaSeguraController extends VendaController
{
    public function __construct(
        private VendaTenantGuardService $tenantGuard,
        private VendaCaixaEdicaoService $caixaEdicao
    ) {
    }

    public function store(Request $request)
    {
        if ((string) $request->input('type') !== 'venda') {
            // Orçamento não movimenta caixa e mantém o fluxo legado.
            return parent::store($request);
        }

        $this->tenantGuard->validar($request);
        $empresaId = (int) $request->empresa_id;

        try {
            DB::transaction(function () use ($request, $empresaId) {
                // Defesa em profundidade: além do TenantGuard, todas as entidades
                // usadas para estoque/fiscal são relidas no tenant autenticado.
                Empresa::query()->whereKey($empresaId)->firstOrFail();

                $natureza = NaturezaOperacao::query()
                    ->whereKey((int) $request->natureza_id)
                    ->where('empresa_id', $empresaId)
                    ->firstOrFail();

                $freteId = null;
                if ((string) $request->tipo_frete !== '9') {
                    $frete = Frete::create([
                        'valor' => __convert_value_bd($request->valor_frete),
                        'placa' => $request->placa_frete ?? '',
                        'tipo' => $request->tipo_frete,
                        'uf' => $request->uf_frete ?? '',
                        'numeracaoVolumes' => $request->n_volumes_frete ?? '',
                        'peso_liquido' => $request->peso_liquido_frete
                            ? __convert_value_bd($request->peso_liquido_frete)
                            : 0,
                        'peso_bruto' => $request->peso_bruto_frete
                            ? __convert_value_bd($request->peso_bruto_frete)
                            : 0,
                        'especie' => $request->especie_frete ?? '',
                        'qtdVolumes' => $request->q_volumes_frete ?? '',
                    ]);
                    $freteId = $frete->id;
                }

                $tipoPagamentos = (array) $request->input('tipo_pagamentos', []);
                if (!isset($tipoPagamentos[0])) {
                    throw new RuntimeException('Forma de pagamento da venda não informada.');
                }

                $request->merge([
                    'usuario_id' => get_id_user(),
                    'frete_id' => $freteId,
                    'observacao' => $request->observacao ?? '',
                    'qtd_volumes' => $request->qtd_volumes ?? 0,
                    'peso_liquido' => $request->peso_liquido ?? 0,
                    'peso_bruto' => $request->peso_bruto ?? 0,
                    'transportadora_id' => $request->transportadora_id ?: null,
                    'valor_total' => __convert_value_bd($this->somaItensSegura($request)),
                    'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
                    'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
                    'estado_emissao' => 'novo',
                    'sequencia_cce' => $request->sequencia_cce ?? 0,
                    'chave' => $request->chave ?? 0,
                    'tipo_pagamento' => $tipoPagamentos[0],
                    'filial_id' => (int) $request->filial_id !== -1 ? $request->filial_id : null,
                ]);

                $venda = Venda::create($request->all());
                $stockMove = new StockMove();
                $produtoIds = (array) $request->input('produto_id', []);

                foreach ($produtoIds as $i => $produtoId) {
                    $product = Produto::query()
                        ->whereKey((int) $produtoId)
                        ->where('empresa_id', $empresaId)
                        ->firstOrFail();

                    $cfop = $natureza->sobrescreve_cfop
                        ? $natureza->CFOP_saida_estadual
                        : $product->CFOP_saida_estadual;

                    ItemVenda::create([
                        'venda_id' => $venda->id,
                        'produto_id' => (int) $produtoId,
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'cfop' => $cfop,
                        'valor' => __convert_value_bd($request->valor_unitario[$i]),
                        'valor_custo' => $product->valor_compra,
                        'x_pedido' => $request->x_pedido[$i] ?? null,
                        'num_item_pedido' => $request->num_item_pedido[$i] ?? null,
                    ]);

                    $stockMove->downStock(
                        $product->id,
                        __convert_value_bd($request->quantidade[$i]),
                        $request->filial_id
                    );
                }

                if ((string) $request->forma_pagamento !== 'a_vista') {
                    $categoriaReceber = CategoriaConta::query()
                        ->where('empresa_id', $empresaId)
                        ->where('tipo', 'receber')
                        ->first();

                    if (!$categoriaReceber) {
                        throw new RuntimeException('Categoria de contas a receber não configurada.');
                    }

                    $datas = (array) $request->input('data_vencimento', []);
                    $valores = (array) $request->input('valor_parcela', []);

                    foreach ($datas as $i => $dataVencimento) {
                        if (!isset($valores[$i], $tipoPagamentos[$i])) {
                            throw new RuntimeException('Parcelamento da venda está incompleto.');
                        }

                        ContaReceber::create([
                            'venda_id' => $venda->id,
                            'cliente_id' => $request->cliente_id,
                            'data_vencimento' => $dataVencimento,
                            'data_recebimento' => $dataVencimento,
                            'valor_integral' => __convert_value_bd($valores[$i]),
                            'tipo_pagamento' => $tipoPagamentos[$i],
                            'valor_recebido' => 0,
                            'status' => 0,
                            'referencia' => 'Parcela ' . ($i + 1) . ' da Venda código ' . $venda->id,
                            'categoria_id' => $categoriaReceber->id,
                            'empresa_id' => $empresaId,
                            'juros' => 0,
                            'multa' => 0,
                            'venda_caixa_id' => null,
                            'observacao' => '',
                            'filial_id' => (int) $request->filial_id !== -1 ? $request->filial_id : null,
                        ]);
                    }
                }
            });

            session()->flash('flash_sucesso', 'Venda adicionada com sucesso!');
        } catch (Throwable $e) {
            report($e);
            session()->flash('flash_erro', 'Não foi possível adicionar a venda. Verifique os dados e tente novamente.');
        }

        return redirect()->route('vendas.index');
    }

    public function update(Request $request, $id)
    {
        if ((string) $request->input('type') !== 'venda') {
            return parent::update($request, $id);
        }

        try {
            return $this->caixaEdicao->executar(
                (int) $id,
                (int) $request->empresa_id,
                function (Venda $venda, $abertura) use ($request, $id) {
                    // A validação e todo o update legado ficam dentro da mesma
                    // transação que mantém o lock da abertura até o commit.
                    $this->tenantGuard->prepararUpdate($request, (int) $id);

                    $response = parent::update($request, $id);

                    if ($abertura) {
                        // O controller legado grava get_id_user() em usuario_id.
                        // Em uma edição administrativa isso transferiria a venda
                        // para outro operador e a faria desaparecer do resumo do
                        // caixa original. Mantemos o operador histórico da sessão.
                        Venda::query()
                            ->whereKey((int) $id)
                            ->where('empresa_id', (int) $request->empresa_id)
                            ->update(['usuario_id' => (int) $abertura->usuario_id]);
                    }

                    return $response;
                }
            );
        } catch (CaixaMovimentacaoException $e) {
            return $this->respostaConflitoCaixa($request, $e);
        }
    }

    public function destroy($id)
    {
        $request = request();

        try {
            return $this->caixaEdicao->executar(
                (int) $id,
                (int) $request->empresa_id,
                function () use ($id) {
                    return parent::destroy($id);
                }
            );
        } catch (CaixaMovimentacaoException $e) {
            return $this->respostaConflitoCaixa($request, $e);
        }
    }

    private function somaItensSegura(Request $request): float
    {
        $total = 0.0;
        foreach ((array) $request->input('subtotal_item', []) as $subtotal) {
            $total += (float) __convert_value_bd($subtotal);
        }

        return $total;
    }

    private function respostaConflitoCaixa(Request $request, CaixaMovimentacaoException $e)
    {
        // O controller legado pode ter preparado flash de sucesso antes de a
        // camada externa detectar uma quebra de invariantes e fazer rollback.
        session()->forget('flash_sucesso');

        if ($request->expectsJson()) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return redirect()->back()->with('flash_erro', $e->getMessage());
    }
}
