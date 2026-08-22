<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PedidoDelete;

class Pedido extends Model
{
	protected $fillable = [
		'comanda', 'status', 'desativado', 'observacao', 'rua', 'numero', 'bairro_id',
		'referencia', 'telefone', 'nome', 'mesa_id', 'referencia_cliete', 'mesa_ativa', 'fechar_mesa', 
		'empresa_id', 'cliente_id'
	];

	public function itens(){
		// return $this->hasMany(ItemPedido::class, 'pedido_id', 'id')->with('produto');
		return $this->hasMany('App\Models\ItemPedido', 'pedido_id', 'id')->with('produto')
		->with('tamanho')->with('itensAdicionais')->with('sabores');
	}

	public function QrCode(){
		return $this->hasMany(PedidoQrCodeCliente::class, 'pedido_id', 'id');
	}

	public function bairro(){
		return $this->belongsTo(BairroDelivery::class, 'bairro_id');
	}

	public function mesa(){
		return $this->belongsTo(Mesa::class, 'mesa_id');
	}

	public function cliente(){
		return $this->belongsTo(Cliente::class, 'cliente_id');
	}

	public function somaItems(){
		if(count($this->itens) > 0){
			$total = 0;
			foreach($this->itens as $i){
				$valorItem = 0;
				$i->produto;

				// if(count($i->sabores) > 0){
				// 	$maiorValor = 0;
				// 	foreach($i->sabores as $s){
				// 		$v = $s->maiorValor($s->sabor_id, $i->tamanho_pizza_id);
				// 		if($v > $maiorValor) $maiorValor = $v;

				// 		$s->produto->produto;
				// 	}
				// 	$valorItem = $maiorValor;
				// }else if(isset($i->produto->produto) && $i->produto->produto->valor_venda > 0){
				// 	$valorItem = $i->produto->produto->valor_venda;
				// }else{
				// 	$valorItem = $i->produto->valor_venda;
				// }
				foreach($i->sabores as $s){$s->produto->produto;}
				$valorItem = $i->valor;

				$somaadicionais = 0;
				// if(count($i->itensAdicionais) > 0){
				// 	foreach($i->itensAdicionais as $a){
				// 		$somaadicionais += $a->adicional->valor * $i->quantidade;
				// 	}
				// }

				$total += $somaadicionais;
				$i->valorItem = $valorItem + $somaadicionais;
				$total += $i->quantidade * $valorItem;
			}
			return $total;
		}else{
			return 0;
		}
	}

	public function itensPendentes(){
		$cont = 0;
		foreach($this->itens as $i){
			if(!$i->status) $cont++;
		}
		return $cont;
	}

	public function temItemDeletetado(){
		$item = PedidoDelete::
		where('pedido_id', $this->id)
		->first();

		return $item != null;
	}

	public function randomColor(){
		$colors = ['red', 'green', 'blue', 'brown', 'yellow', 'cyan', 'purple'];
		return $colors[rand(0,6)];
	}
	
}
