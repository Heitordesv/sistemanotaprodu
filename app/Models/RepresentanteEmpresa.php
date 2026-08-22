<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepresentanteEmpresa extends Model
{
    use HasFactory;
    protected $fillable = [
		'representante_id', 'empresa_id'
	];

	public function empresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function representante(){
        return $this->belongsTo(Representante::class, 'representante_id');
    }

    public function pagamentos(){
        return $this->hasMany('App\Models\FinanceiroRepresentante', 'representante_empresa_id', 'id')->orderBy('id', 'desc');
    }
}
