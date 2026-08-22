<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceitaCte extends Model
{	
	protected $fillable = [
		'descricao', 'cte_id', 'valor', 'data_registro'
	];

	public function cte(){
        return $this->hasOne('App\Models\Cte', 'id', 'cte_id');
    }
}
