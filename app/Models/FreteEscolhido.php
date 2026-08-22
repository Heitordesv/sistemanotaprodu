<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreteEscolhido extends Model
{
    use HasFactory;

    protected $table = 'fretes_escolhidos';

    protected $fillable = [
        'empresa_id',     // 👈 precisa estar aqui
        'name',
        'price',
        'delivery_time',
        'cep_origem',
        'cep_destino',
    ];

    public $timestamps = true; // created_at e updated_at
}
