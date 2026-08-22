<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SangriaCaixa extends Model
{
    protected $fillable = [
        'usuario_id', 'valor', 'empresa_id', 'observacao'
    ];

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
