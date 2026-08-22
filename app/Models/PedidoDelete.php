<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoDelete extends Model
{
    protected $fillable = [
		'pedido_id', 'produto', 'quantidade', 'valor', 'data_insercao', 'empresa_id'
	];
}
