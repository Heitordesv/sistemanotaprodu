<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagemProdutoEcommerce extends Model
{
    protected $fillable = [
        'produto_id', 'path'
    ];

    public function produto(){
        return $this->hasOne('App\Models\ProdutoEcommerce', 'id', 'produto_id');
    }

    public function getImgAttribute()
	{
		return env("PATH_URL") . "/uploads/produtoEcommerce/" . $this->path;
	}
}
