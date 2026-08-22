<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCompra extends Model
{
    protected $fillable = [
        'produto_id', 'compra_id', 'quantidade', 'valor_unitario', 'unidade_compra', 'validade', 'cfop_entrada', 'codigo_siad'
    ];

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function compra(){
        return $this->belongsTo(Compra::class, 'compra_id');
    }
}
