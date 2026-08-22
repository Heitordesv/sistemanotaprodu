<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaProdutoDelivery extends Model
{
    protected $fillable = [
        'nome', 'descricao', 'path', 'empresa_id', 'tipo_pizza'
    ];

    public function produtos(){
        return $this->hasMany(ProdutoDelivery::class, 'categoria_id', 'id')->with('produto')
        ->orderBy('destaque', 'desc')
        ->orderBy('created_at', 'desc')
        ->with('categoria')
        ->where('status', 1)
        ->with('pizza');
    }

    public function adicionais(){
        return $this->hasMany(ListaComplementoDelivery::class, 'categoria_id', 'id');
    }
}
