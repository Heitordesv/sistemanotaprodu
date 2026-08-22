<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CupomDescontoEcommerce extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'valor', 'status', 'codigo', 'tipo', 'descricao', 'valor_minimo_pedido'
    ];
}
