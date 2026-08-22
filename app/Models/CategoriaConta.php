<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaConta extends Model
{
    protected $fillable = [
        'nome', 'empresa_id', 'tipo'
    ];
}
