<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtividadeEvento extends Model
{
    protected $fillable = [
		'responsavel_nome', 'responsavel_telefone', 'crianca_nome', 'inicio', 'fim', 'total',
		'status', 'evento_id', 'funcionario_id', 'forma_pagamento'
	];

	public function servicos(){
        return $this->hasMany('App\Models\AtividadeServico', 'atividade_id', 'id');
    }

    public function evento(){
        return $this->belongsTo(Evento::class, 'evento_id');
    }

}
