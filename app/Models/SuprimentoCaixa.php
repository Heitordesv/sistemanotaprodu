<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuprimentoCaixa extends Model
{
    protected $fillable = [
        'usuario_id', 'valor', 'observacao', 'empresa_id', 'abertura_caixa_id'
    ];

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function aberturaCaixa()
    {
        return $this->belongsTo(AberturaCaixa::class, 'abertura_caixa_id');
    }
}
