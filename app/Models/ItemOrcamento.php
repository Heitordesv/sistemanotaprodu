<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemOrcamento extends Model
{
    protected $fillable = [
		'produto_id', 'orcamento_id', 'quantidade', 'valor', 'altura', 'largura',
        'profundidade', 'acrescimo_perca', 'esquerda', 'direita', 'inferior', 'superior'
	];

	public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function orcamento(){
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }


}
