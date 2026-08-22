<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrocaVendaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'troca_id', 'produto_id', 'valor', 'quantidade'
    ];
}
