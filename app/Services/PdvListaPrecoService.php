<?php

namespace App\Services;

use App\Models\ListaPreco;
use App\Models\Produto;
use App\Models\ProdutoListaPreco;
use Illuminate\Validation\ValidationException;

class PdvListaPrecoService
{
    public function resolverLista(?int $listaPrecoId, int $empresaId): ?ListaPreco
    {
        if (!$listaPrecoId) {
            return null;
        }

        $lista = ListaPreco::query()
            ->where('id', $listaPrecoId)
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$lista) {
            throw ValidationException::withMessages([
                'lista_preco_id' => [
                    'A lista de preços selecionada não pertence a esta empresa.',
                ],
            ]);
        }

        return $lista;
    }

    public function precoPdv(
        Produto $produto,
        ?int $listaPrecoId,
        int $empresaId
    ): float {
        if ((int) $produto->empresa_id !== $empresaId) {
            throw ValidationException::withMessages([
                'produto_id' => [
                    'O produto informado não pertence a esta empresa.',
                ],
            ]);
        }

        $this->resolverLista($listaPrecoId, $empresaId);
        $valor = (float) $produto->valor_venda;

        if ($listaPrecoId) {
            $itemLista = ProdutoListaPreco::query()
                ->where('lista_id', $listaPrecoId)
                ->where('produto_id', $produto->id)
                ->first();

            if ($itemLista && (float) $itemLista->valor > 0) {
                $valor = (float) $itemLista->valor;
            }
        }

        $categoria = $produto->relationLoaded('categoria')
            ? $produto->getRelation('categoria')
            : $produto->categoria;

        if (
            $categoria &&
            (int) $categoria->desconto_ativo === 1 &&
            (float) $categoria->desconto > 0
        ) {
            $valor -= $valor * ((float) $categoria->desconto / 100);
        }

        return round(max(0, $valor), 2);
    }

    public function aplicarAoProduto(
        Produto $produto,
        ?int $listaPrecoId,
        int $empresaId
    ): Produto {
        $produto->setAttribute(
            'valor_venda_pdv',
            $this->precoPdv($produto, $listaPrecoId, $empresaId)
        );
        $produto->setAttribute(
            'lista_preco_id_aplicada',
            $listaPrecoId
        );

        $categoria = $produto->relationLoaded('categoria')
            ? $produto->getRelation('categoria')
            : $produto->categoria;

        $produto->setAttribute(
            'percentual_desconto_categoria_pdv',
            $categoria && (int) $categoria->desconto_ativo === 1
                ? (float) $categoria->desconto
                : 0
        );

        return $produto;
    }
}
