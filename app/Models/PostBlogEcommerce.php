<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostBlogEcommerce extends Model
{
	protected $fillable = [
		'titulo', 'texto', 'categoria_id', 'autor_id', 'tags', 'empresa_id', 'img'
	];
	
	public function categoria(){
		return $this->belongsTo(CategoriaPostBlogEcommerce::class, 'categoria_id');
	}

	public function autor(){
		return $this->belongsTo(AutorPostBlogEcommerce::class, 'autor_id');
	}

	public function getImageAttribute(){
        return "/uploads/postBlog/$this->img";
    }


}
