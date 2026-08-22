<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaProdutoEcommerce extends Model
{
    protected $fillable = [
        'nome', 'imagem', 'empresa_id', 'destaque'
    ];

    public function getImgAttribute()
	{
		if(!$this->imagem){
			return "/imgs/no_image.png";
		}
		return "/uploads/ecommerce/$this->imagem";
	}

    public function subs(){
        return $this->hasMany('App\Models\SubCategoriaEcommerce', 'categoria_id', 'id');
    }

    public function produtos(){
        return $this->hasMany('App\Models\ProdutoEcommerce', 'categoria_id', 'id');
    }


    public function produtosAtivos(){
        $produtos = ProdutoEcommerce::
        where('categoria_id', $this->id)
        ->where('status', 1)
        ->get();
        $temp = [];
        foreach($produtos as $p){
            if(sizeof($p->galeria) > 0){
                array_push($temp, $p);
            }
        }
        return $temp;
    }
}
