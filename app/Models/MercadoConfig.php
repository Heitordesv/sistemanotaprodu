<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoConfig extends Model
{
    protected $fillable = [
        'email', 'funcionamento', 'descricao', 'total_de_produtos', 'total_de_clientes', 'total_de_funcionarios'
    ];
}
