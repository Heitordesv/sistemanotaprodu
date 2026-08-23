<?php

namespace App\Helpers;

use App\Models\Estoque;
use App\Models\Produto;

class StockMove
{
    private function normalizeFilialId($filialId): ?int
    {
        if ($filialId === null || $filialId === '' || (int) $filialId === -1) {
            return null;
        }

        return (int) $filialId;
    }

    /**
     * O controller legado do PDV não repassa a filial ao downStock(). Para não
     * alterar semanticamente os demais módulos, usamos o escopo do request
     * somente na rota frenteCaixa.store, já validada pelo VendaTenantGuardService.
     */
    private function resolveFilialId($filialId): ?int
    {
        if ($filialId !== null) {
            return $this->normalizeFilialId($filialId);
        }

        $route = request()->route();
        if (!$route || $route->getName() !== 'frenteCaixa.store') {
            return null;
        }

        $payload = request()->all();

        if (array_key_exists('estoque_filial_id', $payload)) {
            return $this->normalizeFilialId($payload['estoque_filial_id']);
        }

        if (array_key_exists('filial_id', $payload)) {
            return $this->normalizeFilialId($payload['filial_id']);
        }

        return null;
    }

    private function existStock($productId, $filialId, bool $forUpdate = false)
    {
        $filialId = $this->normalizeFilialId($filialId);

        $query = Estoque::query()
            ->where('produto_id', $productId)
            ->when(
                $filialId === null,
                fn ($query) => $query->whereNull('filial_id'),
                fn ($query) => $query->where('filial_id', $filialId)
            );

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function getStockProduct($productId, $filialId = null)
    {
        $stock = $this->existStock($productId, $filialId);

        return $stock->quantidade ?? 0;
    }

    public function pluStock($productId, $quantity, $value = -1, $filialId = null)
    {
        $quantity = (float) $quantity;
        $filialId = $this->resolveFilialId($filialId);

        // Serializa operações do mesmo produto. Além de proteger o read/modify/save,
        // esse lock evita duas transações criarem simultaneamente o primeiro estoque.
        $produto = Produto::query()->whereKey($productId)->lockForUpdate()->firstOrFail();
        $stock = $this->existStock($productId, $filialId, true);

        if ($stock) {
            $stock->quantidade += $quantity;
            $stock->valor_compra = $value > -1 ? $value : $stock->valor_compra;
        } else {
            $stock = new Estoque();
            $stock->valor_compra = $value > -1 ? $value : $produto->valor_compra;
            $stock->quantidade = $quantity;
            $stock->produto_id = $productId;
            $stock->filial_id = $filialId;
            $stock->empresa_id = $produto->empresa_id;
        }

        return $stock->save();
    }

    public function downStock($productId, $quantity, $filialId = null)
    {
        $quantity = (float) $quantity;
        $filialId = $this->resolveFilialId($filialId);

        Produto::query()->whereKey($productId)->lockForUpdate()->firstOrFail();
        $stock = $this->existStock($productId, $filialId, true);

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
