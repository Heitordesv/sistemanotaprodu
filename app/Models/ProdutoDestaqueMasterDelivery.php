<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoDestaqueMasterDelivery extends Model
{
	protected $fillable = [ 'produto_id', 'categoria_id' ];

	public function produto(){
		return $this->belongsTo(ProdutoDelivery::class, 'produto_id');
	}
}
