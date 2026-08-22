<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ItemPurchase;
use App\Models\Produto;

class Estoque extends Model
{
    protected $fillable = [
        'produto_id', 'quantidade', 'valor_compra', 'validade', 'empresa_id', 'filial_id'
    ];

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function valorCompra()
    {
        if (!$this->produto->conversao_unitaria) {
            return $this->valor_compra;
        } else {
            $conv = (float) __convert_value_bd($this->produto->conversao_unitaria);
            if ($conv > 0) {
                return $this->valor_compra / $conv;
            } else {
                return $this->valor_compra;
            }
        }
    }

    public function value_purchase($productId = null)
    {

        $item = ItemPurchase::where('produto_id', $productId)
            ->orderBy('id', 'desc')
            ->first();
        return $item != null ? $item->value : 0;
    }

    public static function ultimoValorCompra($productId)
    {
        $estoque = Estoque::where('produto_id', $productId)
            ->orderBy('id', 'desc')
            ->first();

        return $estoque;
    }

    public function valorCompraUnitário()
    {
        $produto = Produto::find($this->produto_id);

        $valorMedio = $this->valor_compra / $produto->conversao_unitaria;
        return $valorMedio * $this->quantidade;
    }
}
