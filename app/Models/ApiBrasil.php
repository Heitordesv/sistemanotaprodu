<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiBrasil extends Model
{
    protected $table = 'api_brasil';

    protected $fillable = [
        'user_id',
        'DeviceToken',
        'Bearer',
        'tipo',
        'server_search',
        'situacao'
    ];

    // Se a tabela não tiver timestamps (created_at e updated_at), desativa assim:
    public $timestamps = false;
}
