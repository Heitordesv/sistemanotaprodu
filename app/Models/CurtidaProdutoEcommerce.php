<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurtidaProdutoEcommerce extends Model
{
    protected $fillable = [
        'produto_id', 'cliente_id'
    ];

    public function produto(){
        return $this->belongsTo(ProdutoEcommerce::class, 'produto_id');
    }
}
