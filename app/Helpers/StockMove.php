<?php

namespace App\Helpers;

use App\Models\Empresa;
use App\Models\Estoque;
use App\Models\Produto;

class StockMove
{
    private function normalizeFilialId($filialId): ?int
    {
        if ($filialId === null || (int) $filialId === -1) {
            return null;
        }

        return (int) $filialId;
    }

    private function existStock($productId, $filialId)
    {
        $filialId = $this->normalizeFilialId($filialId);

        return Estoque::where('produto_id', $productId)
            ->when(
                $filialId === null,
                fn ($query) => $query->whereNull('filial_id'),
                fn ($query) => $query->where('filial_id', $filialId)
            )
            ->first();
    }

    public function getStockProduct($productId, $filialId = null)
    {
        $stock = $this->existStock($productId, $filialId);

        return $stock->quantidade ?? 0;
    }

    public function pluStock($productId, $quantity, $value = -1, $filialId = null)
    {
        $quantity = (float) $quantity;
        $filialId = $this->normalizeFilialId($filialId);
        $stock = $this->existStock($productId, $filialId);

        if ($stock) {
            $stock->quantidade += $quantity;
            $stock->valor_compra = $value > -1 ? $value : $stock->valor_compra;
        } else {
            $produto = Produto::findOrFail($productId);
            $stock = new Estoque();
            $stock->valor_compra = $value > -1 ? $value : $produto->valor_compra;
            $stock->quantidade = $quantity;
            $stock->produto_id = $productId;
            $stock->filial_id = $filialId;
            $stock->empresa_id = Empresa::getId();
        }

        return $stock->save();
    }

    public function downStock($productId, $quantity, $filialId = null)
    {
        $quantity = (float) $quantity;
        $stock = $this->existStock($productId, $filialId);

        if (!$stock) {
            return false;
        }

        $stock->quantidade -= $quantity;
        if ($stock->quantidade < 0.010) {
            $stock->quantidade = 0;
        }

        return $stock->save();
    }
}