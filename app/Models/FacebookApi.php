<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookApi extends Model
{
    protected $table = 'config_api_facebook';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'nome_empresa',
        'pixel_id',
        'access_token',
    ];
}
