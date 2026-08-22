<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoMotoboy extends Model
{
    protected $fillable = [
        'motoboy_id', 'pedido_id', 'valor', 'status'
    ];

    public function motoboy(){
		return $this->belongsTo(Motoboy::class, 'motoboy_id');
	}

	public function pedido(){
		return $this->belongsTo(PedidoDelivery::class, 'pedido_id');
	}
}
