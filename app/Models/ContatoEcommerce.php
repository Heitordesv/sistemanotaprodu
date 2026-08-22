<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContatoEcommerce extends Model
{
    protected $fillable = [
		'nome', 'email', 'texto', 'empresa_id'
	];
}
