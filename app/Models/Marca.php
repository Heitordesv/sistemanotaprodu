<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $fillable = [
        'nome', 'empresa_id'
    ];

    public function produtos(){
        return $this->hasMany('App\Models\Produto', 'marca_id', 'id');
    }
    
}
