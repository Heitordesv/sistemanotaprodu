<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemLocacaoDisponibilidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'produto_id', 'data', 'locacao_id'
    ];
}
