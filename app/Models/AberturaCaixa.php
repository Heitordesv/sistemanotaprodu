<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AberturaCaixa extends Model
{
    protected $fillable = [
        'usuario_id', 'valor', 'ultima_venda_nfe', 'ultima_venda_nfce', 'empresa_id',
        'primeira_venda_nfe', 'primeira_venda_nfce', 'status', 'valor_dinheiro_caixa',
        'filial_id'
    ];

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function filial(){
        return $this->belongsTo(Filial::class, 'filial_id');
    }
}
