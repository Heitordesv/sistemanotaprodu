<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaturaPreVenda extends Model
{
    use HasFactory;

    protected $fillable = [
		'valor', 'forma_pagamento', 'pre_venda_id', 'vencimento'
	];

	public function preVenda(){
		return $this->belongsTo(VendaCaixaPreVenda::class, 'pre_venda_id');
	}
}
