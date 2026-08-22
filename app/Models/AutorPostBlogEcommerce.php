<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutorPostBlogEcommerce extends Model
{
    protected $fillable = [
        'nome', 'tipo', 'img', 'empresa_id'
    ];

    public function getImageAttribute(){
        return "/uploads/autorPost/$this->img";
    }


}
