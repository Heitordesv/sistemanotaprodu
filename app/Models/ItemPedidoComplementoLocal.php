<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPedidoComplementoLocal extends Model
{
    protected $fillable = [
		'item_pedido', 'complemento_id', 'quantidade'
	];

	public function adicional(){
        return $this->belongsTo(ComplementoDelivery::class, 'complemento_id');
    }
}
