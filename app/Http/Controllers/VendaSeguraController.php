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
use Illuminate\Support\Collection;
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
                Empresa::query()->whereKey($empresaId)->firstOrFail();

                $natureza = NaturezaOperacao::query()
                    ->whereKey((int) $request->natureza_id)
                    ->where('empresa_id', $empresaId)
                    ->firstOrFail();

                $freteId = $this->criarFreteSeNecessario($request);
                $tipoPagamentos = $this->tiposPagamentoObrigatorios($request);

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
                $this->gravarItensEBaixarEstoque($request, $venda, $natureza, $empresaId);
                $this->gravarContasReceber($request, $venda, $empresaId, $tipoPagamentos);
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
                    // Toda a validação e mutação permanece dentro da transação
                    // que mantém AberturaCaixa -> Venda bloqueadas até o commit.
                    $this->tenantGuard->prepararUpdate($request, (int) $id);
                    $this->validarUpdateSeguro($request);
                    $this->atualizarVendaSegura($request, $venda);

                    if ($abertura) {
                        // Preserva o operador histórico do caixa mesmo quando um
                        // administrador diferente realiza a edição autorizada.
                        Venda::query()
                            ->whereKey((int) $id)
                            ->where('empresa_id', (int) $request->empresa_id)
                            ->update(['usuario_id' => (int) $abertura->usuario_id]);
                    }

                    session()->flash('flash_sucesso', 'Venda atualizada com sucesso!');
                    return redirect()->route('vendas.index');
                }
            );
        } catch (CaixaMovimentacaoException $e) {
            return $this->respostaConflitoCaixa($request, $e);
        } catch (Throwable $e) {
            report($e);
            return $this->respostaErroInternoVenda($request, 'Não foi possível atualizar a venda.');
        }
    }

    public function destroy($id)
    {
        $request = request();

        try {
            return $this->caixaEdicao->executar(
                (int) $id,
                (int) $request->empresa_id,
                function (Venda $venda) {
                    $this->reverterEstoqueSeguro($venda->itens, $venda->filial_id);
                    $venda->delete();

                    session()->flash('flash_sucesso', 'Venda deletada!');
                    return redirect()->route('vendas.index');
                }
            );
        } catch (CaixaMovimentacaoException $e) {
            return $this->respostaConflitoCaixa($request, $e);
        } catch (Throwable $e) {
            report($e);
            return $this->respostaErroInternoVenda($request, 'Não foi possível excluir a venda.');
        }
    }

    private function atualizarVendaSegura(Request $request, Venda $venda): void
    {
        $empresaId = (int) $request->empresa_id;
        $natureza = NaturezaOperacao::query()
            ->whereKey((int) $request->natureza_id)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();

        // Captura relações/filial antigas antes de alterar a venda, para que o
        // estorno de estoque ocorra exatamente no local original.
        $itensAnteriores = $venda->itens;
        $filialAnterior = $venda->filial_id;
        $freteAnterior = $venda->frete;
        $freteId = $this->criarFreteSeNecessario($request);

        $request->merge([
            'frete_id' => $freteId,
            'usuario_id' => get_id_user(),
            'transportadora_id' => $request->transportadora_id ?: null,
            'observacao' => $request->observacao ?? '',
            'qtd_volumes' => $request->qtd_volumes ?? 0,
            'peso_liquido' => $request->peso_liquido ?? 0,
            'peso_bruto' => $request->peso_bruto ?? 0,
            'desconto' => $request->desconto ? __convert_value_bd($request->desconto) : 0,
            'acrescimo' => $request->acrescimo ? __convert_value_bd($request->acrescimo) : 0,
            'valor_total' => $this->somaItensSegura($request),
            'sequencia_cce' => $request->sequencia_cce ?? 0,
            'chave' => $request->chave ?? 0,
            'filial_id' => (int) $request->filial_id !== -1 ? $request->filial_id : null,
        ]);

        $venda->fill($request->all())->save();

        $this->reverterEstoqueSeguro($itensAnteriores, $filialAnterior);
        $venda->itens()->delete();
        $venda->duplicatas()->delete();

        $this->gravarItensEBaixarEstoque($request, $venda, $natureza, $empresaId);

        $tipoPagamentos = (array) $request->input('tipo_pagamentos', []);
        $this->gravarContasReceber($request, $venda, $empresaId, $tipoPagamentos, true);

        if ($freteAnterior) {
            $freteAnterior->delete();
        }
    }

    private function gravarItensEBaixarEstoque(
        Request $request,
        Venda $venda,
        NaturezaOperacao $natureza,
        int $empresaId
    ): void {
        $stockMove = new StockMove();
        $produtoIds = (array) $request->input('produto_id', []);

        foreach ($produtoIds as $i => $produtoId) {
            if (!isset($request->quantidade[$i], $request->valor_unitario[$i])) {
                throw new RuntimeException('Itens da venda estão incompletos.');
            }

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
    }

    private function gravarContasReceber(
        Request $request,
        Venda $venda,
        int $empresaId,
        array $tipoPagamentos,
        bool $aceitarTipoPagamentoUnico = false
    ): void {
        if ((string) $request->forma_pagamento === 'a_vista') {
            return;
        }

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
            $tipo = $tipoPagamentos[$i] ?? ($aceitarTipoPagamentoUnico ? $request->tipo_pagamento : null);

            if (!isset($valores[$i]) || $tipo === null || $tipo === '') {
                throw new RuntimeException('Parcelamento da venda está incompleto.');
            }

            ContaReceber::create([
                'venda_id' => $venda->id,
                'cliente_id' => $request->cliente_id,
                'data_vencimento' => $dataVencimento,
                'data_recebimento' => $dataVencimento,
                'valor_integral' => __convert_value_bd($valores[$i]),
                'tipo_pagamento' => $tipo,
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

    private function criarFreteSeNecessario(Request $request): ?int
    {
        if ((string) $request->tipo_frete === '9') {
            return null;
        }

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

        return (int) $frete->id;
    }

    private function tiposPagamentoObrigatorios(Request $request): array
    {
        $tipos = (array) $request->input('tipo_pagamentos', []);
        if (!isset($tipos[0])) {
            throw new RuntimeException('Forma de pagamento da venda não informada.');
        }

        return $tipos;
    }

    private function validarUpdateSeguro(Request $request): void
    {
        $request->validate([
            'cliente_id' => ['required'],
            'natureza_id' => ['required'],
            'produto_id' => ['required', 'array', 'min:1'],
        ], [
            'cliente_id.required' => 'Campo Obrigatório',
            'natureza_id.required' => 'Campo Obrigatório',
            'produto_id.required' => 'Campo Obrigatório',
        ]);
    }

    private function reverterEstoqueSeguro($itens, $filialId): void
    {
        $stockMove = new StockMove();

        foreach ($itens as $item) {
            $stockMove->pluStock(
                $item->produto_id,
                __convert_value_bd($item->quantidade),
                $filialId
            );
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
        session()->forget('flash_sucesso');

        if ($request->expectsJson()) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return redirect()->back()->with('flash_erro', $e->getMessage());
    }

    private function respostaErroInternoVenda(Request $request, string $mensagem)
    {
        session()->forget('flash_sucesso');

        if ($request->expectsJson()) {
            return response()->json(['message' => $mensagem], 500);
        }

        return redirect()->back()->with('flash_erro', $mensagem);
    }
}
