<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NFeReferecia extends Model
{
    use HasFactory;
    protected $fillable = [
        'venda_id', 'chave'
    ];
}
