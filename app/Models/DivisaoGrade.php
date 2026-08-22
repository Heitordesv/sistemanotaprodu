<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DivisaoGrade extends Model
{
    protected $fillable = [
        'nome', 'empresa_id', 'sub_divisao'
    ];
}
