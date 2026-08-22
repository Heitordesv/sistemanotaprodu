<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaturaFrenteCaixa extends Model
{
    use HasFactory;
    protected $fillable = [
		'valor', 'forma_pagamento', 'venda_caixa_id'
	];

	public function venda(){
		return $this->belongsTo(VendaCaixa::class, 'venda_id');
	}
}
