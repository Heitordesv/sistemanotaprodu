<?php

namespace App\Services;

use App\Helpers\StockMove;
use App\Models\AlteracaoEstoque;
use App\Models\Usuario;
use App\Models\VendaCaixa;
use Illuminate\Validation\ValidationException;

class DevolucaoEstoqueService
{
    public function devolver(
        VendaCaixa $venda,
        Usuario $solicitante,
        Usuario $autorizador
    ): void {
        if ((bool) $venda->retorno_estoque) {
            throw ValidationException::withMessages([
                'venda' => 'O estoque desta venda já foi devolvido.',
            ]);
        }

        $quantidadesPorProduto = [];
        foreach ($venda->itens as $item) {
            $produtoId = (int) $item->produto_id;
            $quantidade = (float) __convert_value_bd($item->quantidade);

            if (!isset($quantidadesPorProduto[$produtoId])) {
                $quantidadesPorProduto[$produtoId] = 0;
            }

            $quantidadesPorProduto[$produtoId] += $quantidade;
        }

        $stockMove = new StockMove();
        foreach ($quantidadesPorProduto as $produtoId => $quantidade) {
            $stockMove->pluStock(
                $produtoId,
                $quantidade,
                -1,
                $venda->filial_id
            );

            $observacao = sprintf(
                'Devolução venda #%d. Operador: %s. Autorizado por: %s.',
                $venda->id,
                $solicitante->nome,
                $autorizador->nome
            );

            AlteracaoEstoque::create([
                'produto_id' => $produtoId,
                'usuario_id' => $solicitante->id,
                'quantidade' => $quantidade,
                'tipo' => 'devolucao',
                'observacao' => mb_substr($observacao, 0, 200),
                'empresa_id' => $venda->empresa_id,
            ]);
        }
    }
}