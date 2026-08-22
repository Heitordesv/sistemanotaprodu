<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
   protected $fillable = [
    'nome',
    'empresa_id',
    'desconto',
    'desconto_ativo'
];

    public function subs(){
        return $this->hasMany('App\Models\SubCategoria', 'categoria_id', 'id');
    }

    public function produtos(){
        return $this->hasMany('App\Models\Produto', 'categoria_id', 'id');
    }
    
}
