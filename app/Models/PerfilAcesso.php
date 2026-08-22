<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilAcesso extends Model
{
    protected $fillable = [
        'nome', 'permissao'
    ];
}
