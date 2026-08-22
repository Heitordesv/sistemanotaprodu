<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoFavoritoDelivery extends Model
{
	protected $fillable = [ 
		'produto_id', 'cliente_id'
	];

	public function cliente(){
		return $this->belongsTo(ClienteDelivery::class, 'cliente_id');
	}

	public function produto(){
		return $this->belongsTo(ProdutoDelivery::class, 'produto_id');
	}

	public function totalCompras(){
		$cont = 0;
		$comprasDoCliente = $this->cliente->pedidos;
		foreach($comprasDoCliente as $c){
			foreach($c->itens as $i){
				if($i->produto->id == $this->produto_id)
					$cont++;
			}
		}
		return $cont;
	}
}
