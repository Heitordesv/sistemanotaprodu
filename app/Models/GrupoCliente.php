<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoCliente extends Model
{
    protected $fillable = [
        'nome', 'empresa_id'
    ];

    public function clientes(){
		return $this->hasMany('App\Models\Cliente', 'grupo_id', 'id');
	}
	
}
