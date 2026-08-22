<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerMaisVendido extends Model
{
	protected $fillable = [
		'path', 'texto_primario', 'texto_secundario', 'produto_delivery_id', 'pack_id', 'ativo'
	];

	public function produto(){
		return $this->belongsTo(ProdutoDelivery::class, 'produto_delivery_id');
	}

	public function pack(){
		return $this->belongsTo(PackProdutoDelivery::class, 'pack_id');
	}

}
