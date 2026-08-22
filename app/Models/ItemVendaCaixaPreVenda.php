<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemVendaCaixaPreVenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'produto_id', 'pre_venda_id', 'quantidade', 'valor',
        'observacao', 'cfop'
    ];

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
