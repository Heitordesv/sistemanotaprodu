<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaComplementoDelivery extends Model
{
    protected $fillable = [
		'categoria_id', 'complemento_id'
	];

	public function complemento(){
        return $this->belongsTo(ComplementoDelivery::class, 'complemento_id');
    }

    public function categoria(){
        return $this->belongsTo(CategoriaProdutoDelivery::class, 'categoria_id');
    }
}
