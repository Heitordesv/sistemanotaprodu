<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemDfe extends Model
{
    protected $fillable = [
		'numero_nfe', 'produto_id', 'empresa_id'
	];
}
