<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPedidoEcommerce extends Model
{
    protected $fillable = [
        'pedido_id',
        'produto_id',
        'quantidade'
    ];

    // RELACIONAMENTO CORRETO
  public function produto()
{
    return $this->belongsTo(ProdutoEcommerce::class, 'produto_id');
}

// 👇 ADICIONE ISSO AQUI (ALIAS)
public function produtoEcommerce()
{
    return $this->produto();
}
}