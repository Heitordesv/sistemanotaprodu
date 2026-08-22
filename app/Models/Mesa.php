<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pedido;
use LaravelQRCode\Facades\QRCode;

class Mesa extends Model
{
	protected $fillable = [
		'nome', 'empresa_id', 'token'
	];

	public function comandas(){
		$pedidos = Pedido::
		where('desativado', false)
		->where('mesa_id', $this->id)
		->get();

		return sizeof($pedidos);
	}

	public function pedidos(){
        return $this->hasMany('App\Pedido', 'mesa_id', 'id');
    }

    public function somaItens(){
    	$pedidos = Pedido::
		where('desativado', false)
		->where('mesa_id', $this->id)
		->get();

		$soma = 0;
		foreach($pedidos as $p){

			$pItem = $p->somaItems();
			$soma+= $pItem;
		}
		return number_format($soma, 2);
    }

}
