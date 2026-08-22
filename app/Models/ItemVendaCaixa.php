<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PedidoDelivery;

class ItemVendaCaixa extends Model
{
    protected $fillable = [
		'produto_id', 'venda_caixa_id', 'quantidade', 'valor', 'item_pedido_id', 'observacao',
        'cfop', 'valor_custo'
	];

	public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function venda(){
        return $this->belongsTo(VendaCaixa::class, 'venda_caixa_id');
    }

    public function itemPedido(){
        return $this->belongsTo(ItemPedido::class, 'item_pedido_id');
    }

    public function nomeDoProduto(){
    	$nome = $this->produto->nome;
    	if($this->observacao != '')
    		$nome .= ' obs: ' . $this->observacao;
    	return $nome;
    }

    public function nomeDoProdutoDelivery($pedido_delivery_id, $indice){
        $pedido = PedidoDelivery::find($pedido_delivery_id);
        foreach($pedido->itens as $key => $i){
            if($key == $indice){
                return $i->nomeDoProduto();
            }
        }

    }
}
