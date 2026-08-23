<?php

namespace App\Services;

use App\Helpers\StockMove;
use App\Models\AlteracaoEstoque;
use App\Models\Usuario;
use App\Models\VendaCaixa;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class DevolucaoEstoqueService
{
    public function devolver(
        VendaCaixa $venda,
        Usuario $solicitante,
        Usuario $autorizador
    ): void {
        // Esta validação é defesa em profundidade. O serviço orquestrador também
        // mantém a VendaCaixa sob lockForUpdate durante todo o processamento local.
        if ((bool) $venda->retorno_estoque) {
            throw ValidationException::withMessages([
                'venda' => 'O estoque desta venda já foi devolvido.',
            ]);
        }

        $venda->loadMissing('itens');

        $quantidadesPorProduto = [];
        foreach ($venda->itens as $item) {
            $produtoId = (int) $item->produto_id;
            $quantidade = (float) __convert_value_bd($item->quantidade);

            if (!isset($quantidadesPorProduto[$produtoId])) {
                $quantidadesPorProduto[$produtoId] = 0;
            }

            $quantidadesPorProduto[$produtoId] += $quantidade;
        }

        // Vendas novas gravam o escopo REAL usado na baixa. Para registros antigos
        // esse campo é NULL, refletindo corretamente o comportamento legado, que
        // baixava o estoque matriz mesmo quando a venda possuía filial_id.
        $estoqueFilialId = Schema::hasColumn('venda_caixas', 'estoque_filial_id')
            ? ($venda->estoque_filial_id === null ? null : (int) $venda->estoque_filial_id)
            : null;

        $stockMove = new StockMove();
        foreach ($quantidadesPorProduto as $produtoId => $quantidade) {
            $stockMove->pluStock(
                $produtoId,
                $quantidade,
                -1,
                $estoqueFilialId
            );

            $observacao = sprintf(
                'Devolução venda #%d. Operador: %s. Autorizado por: %s.',
                $venda->id,
                $solicitante->nome,
                $autorizador->nome
            );

            $alteracao = new AlteracaoEstoque([
                'produto_id' => $produtoId,
                'usuario_id' => $solicitante->id,
                'quantidade' => $quantidade,
                'tipo' => 'devolucao',
                'observacao' => mb_substr($observacao, 0, 200),
                'empresa_id' => $venda->empresa_id,
            ]);

            if (Schema::hasColumn('alteracao_estoques', 'filial_id')) {
                $alteracao->filial_id = $estoqueFilialId;
            }

            $alteracao->save();
        }
    }
}
