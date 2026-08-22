<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoPagSeguro extends Model
{
    protected $fillable = [
		'pedido_delivery_id', 'numero_cartao', 'cpf', 'nome_impresso', 'codigo_transacao', 'referencia', 'parcelas',
		'bandeira', 'status'
	];
}
