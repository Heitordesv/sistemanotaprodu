<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemVenda extends Model
{
    protected $fillable = [
        'produto_id', 'venda_id', 'quantidade', 'valor', 'cfop', 'altura', 'largura', 'profundidade',
        'acrescimo_perca', 'esquerda', 'direita', 'inferior', 'superior', 'valor_custo', 
        'quantidade_dimensao', 'x_pedido', 'num_item_pedido'
    ];

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function venda(){
        return $this->belongsTo(Venda::class, 'venda_id');
    }
    
    public function percentualUf($uf){
        $tributacao = TributacaoUf
        ::where('uf', $uf)
        ->where('produto_id', $this->produto_id)
        ->first();

        return $tributacao;
    }

}
