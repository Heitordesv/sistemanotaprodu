<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCotacao extends Model
{
    protected $fillable = [
        'produto_id', 'cotacao_id', 'quantidade', 'valor'
    ];

    public function cotacao(){
		return $this->belongsTo(Cotacao::class, 'cotacao_id');
	}

	public function produto(){
		return $this->belongsTo(Produto::class, 'produto_id');
	}
}
