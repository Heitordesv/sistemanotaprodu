<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemessaReferenciaNfe extends Model
{
    use HasFactory;

    protected $fillable = [
        'remessa_id', 'chave'
    ];
}
