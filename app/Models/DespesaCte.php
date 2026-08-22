<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DespesaCte extends Model
{
    protected $fillable = [
		'categoria_id', 'cte_id', 'valor', 'data_registro', 'descricao'
	];

	public function categoria(){
        return $this->belongsTo(CategoriaDespesaCte::class, 'categoria_id');
    }

    public function cte(){
        return $this->hasOne('App\Models\Cte', 'id', 'cte_id');
    }
}
