<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remessa extends Model
{
    protected $fillable = [
        'nome_arquivo', 'empresa_id'
    ];

    public function boletos(){
        return $this->hasMany('App\Models\RemessaBoleto', 'remessa_id', 'id');
    }
}
