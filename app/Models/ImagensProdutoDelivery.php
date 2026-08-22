<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagensProdutoDelivery extends Model
{	
	protected $fillable = [
		'produto_id', 'path'
	];

	public function produto(){
        return $this->hasOne(ProdutoDelivery::class, 'id', 'produto_id');
    }

	public function getImgAttribute()
	{
		return env("PATH_URL") . "/uploads/produtoDelivery/" . $this->path;
	}
	
}
