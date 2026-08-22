<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenClienteDelivery extends Model
{
    protected $fillable = [
		'token', 'cliente_id', 'user_id'
	];
}
