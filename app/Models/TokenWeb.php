<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenWeb extends Model
{
     protected $fillable = [
		'token', 'cliente_id'
	];
}
