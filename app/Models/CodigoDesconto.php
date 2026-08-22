<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ClienteDelivery;

class CodigoDesconto extends Model
{
    protected $fillable = [
		'codigo', 'valor', 'tipo', 'cliente_id', 'ativo', 'push', 'sms', 'empresa_id',
		'valor_minimo_pedido', 'descricao', 'expiracao'
	];

	public function cliente(){
		return $this->belongsTo(ClienteDelivery::class, 'cliente_id');
	}

	public function totalDeClientesAtivosCad(){
		$clientesAtivos = ClienteDelivery::
		where('ativo', true)
		->get();

		return count($clientesAtivos);
	}
}
