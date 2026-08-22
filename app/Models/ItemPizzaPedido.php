<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProdutoPizza;

class ItemPizzaPedido extends Model
{
	protected $fillable = [
		'item_pedido', 'sabor_id'
	];

	public function produto(){
		return $this->belongsTo(ProdutoDelivery::class, 'sabor_id');
	}

	public function itensPedido(){
		return $this->hasMany('App\Models\ItemPedidoDelivery', 'id', 'item_pedido');
	}

	public function maiorValor($saborId, $tamanho_id){
		$maiorValor = 0;

		$p = ProdutoPizza::
		where('tamanho_id', $tamanho_id)
		->where('produto_id', $saborId)
		->first();

		return $p->valor ?? 0;
	}

}
