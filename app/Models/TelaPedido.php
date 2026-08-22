<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelaPedido extends Model
{
    use HasFactory;
    protected $fillable = [
        'nome', 'alerta_amarelo', 'alerta_vermelho', 'empresa_id'
    ];
}
