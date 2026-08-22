<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtividadeServico extends Model
{
    protected $fillable = [
		'servico_id', 'atividade_id'
	];

	public function servico(){
        return $this->belongsTo(Servico::class, 'servico_id');
    }
}
