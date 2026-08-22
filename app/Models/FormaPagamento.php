<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    use HasFactory;
    protected $fillable = [
        'empresa_id', 'nome', 'chave', 'taxa', 'status', 'prazo_dias', 'tipo_taxa', 'infos'
    ];

}
