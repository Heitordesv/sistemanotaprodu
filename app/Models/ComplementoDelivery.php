<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplementoDelivery extends Model
{
    protected $fillable = [
		'nome', 'valor', 'categoria', 'empresa_id', 'tipo'
	];

	public function nome(){
		$nome = explode('>', $this->nome);
		if(sizeof($nome) > 1) return $nome[1];
		return $this->nome;
	}

	public function setCategoriaAttribute($value)
    {
        $this->attributes['categoria'] = json_encode($value);
    }
}
