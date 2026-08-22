<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaContrato extends Model
{
    protected $fillable = [
        'empresa_id', 'status'
    ];
}
