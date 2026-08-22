<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaPreco extends Model
{
    protected $fillable = [
		'nome', 'percentual_alteracao', 'empresa_id', 'tipo', 'tipo_inc_red'
	];

	public function itens(){
        return $this->hasMany('App\Models\ProdutoListaPreco', 'lista_id', 'id');
    }
}
