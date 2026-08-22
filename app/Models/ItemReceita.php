<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemReceita extends Model
{
    protected $fillable = [
		'receita_id', 'produto_id', 'quantidade', 'medida'
	];

	public function receita(){
		return $this->belongsTo(Receita::class, 'receita_id');
	}

	public function produto(){
		return $this->belongsTo(Produto::class, 'produto_id');
	}
}
