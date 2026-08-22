<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemaUsuario extends Model
{
    use HasFactory;

    protected $fillable = [
        'plano_fundo', 'usuario_id', 'cabecalho', 'tema'
    ];

}
