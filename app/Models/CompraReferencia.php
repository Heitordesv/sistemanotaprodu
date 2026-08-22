<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraReferencia extends Model
{
    use HasFactory;
    protected $fillable = [
        'compra_id', 'chave'
    ];
}
