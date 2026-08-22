<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CidadeDelivery extends Model
{
	protected $fillable = [ 'nome', 'cep', 'uf' ];

	public function getInfoAttribute()
    {
        return "$this->nome ($this->uf)";
    }
}
