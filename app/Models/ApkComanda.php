<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApkComanda extends Model
{
    use HasFactory;

    protected $fillable = [ 'nome_arquivo', 'empresa_id' ];

}
