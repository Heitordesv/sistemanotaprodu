<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemIBTE extends Model
{	
	protected $table = 'item_i_b_t_es';
	
    protected $fillable = [
		'ibte_id', 'codigo', 'descricao', 'nacional_federal', 'importado_federal', 'estadual',
		'municipal'
	];
}
