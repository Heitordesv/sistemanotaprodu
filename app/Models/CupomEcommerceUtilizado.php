<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CupomEcommerceUtilizado extends Model
{
    protected $table = 'cupom_ecommerce_utilizados';

    protected $fillable = [
        'empresa_id',
        'cupom_id',
        'cliente_id',
        'pedido_id',
    ];

    public function cupom()
    {
        return $this->belongsTo(CupomDescontoEcommerce::class, 'cupom_id');
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteEcommerce::class, 'cliente_id');
    }

    public function pedido()
    {
        return $this->belongsTo(PedidoEcommerce::class, 'pedido_id');
    }
}