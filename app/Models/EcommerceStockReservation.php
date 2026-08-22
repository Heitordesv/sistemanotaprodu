<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceStockReservation extends Model
{
    protected $fillable = [
        'empresa_id',
        'pedido_id',
        'produto_ecommerce_id',
        'produto_id',
        'quantidade',
        'expires_at',
        'consumed_at',
        'released_at',
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(PedidoEcommerce::class, 'pedido_id');
    }

    public function produtoEcommerce()
    {
        return $this->belongsTo(ProdutoEcommerce::class, 'produto_ecommerce_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}