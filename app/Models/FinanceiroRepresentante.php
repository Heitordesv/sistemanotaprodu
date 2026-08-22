<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceiroRepresentante extends Model
{
    use HasFactory;
    protected $fillable = [
		'representante_empresa_id', 'forma_pagamento', 'valor', 'pagamento_comissao'
	];

	public function rep(){
        return $this->belongsTo(RepresentanteEmpresa::class, 'representante_empresa_id');
    }
}
