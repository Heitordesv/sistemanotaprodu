<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $fillable = [
		'senha', 'arquivo', 'empresa_id'
	];

	public function config(){
		return $this->belongsTo(ConfigNota::class, 'empresa_id');
	}
}
