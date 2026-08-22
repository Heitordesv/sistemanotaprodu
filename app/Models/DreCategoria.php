<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LancamentoCategoria;

class DreCategoria extends Model
{
	protected $fillable = [
		'nome', 'dre_id'
	];

	public function lancamentos(){
		return $this->hasMany('App\Models\LancamentoCategoria', 'categoria_id', 'id');
	}

	public function dre(){
		return $this->belongsTo(Dre::class, 'dre_id');
	}

	public function soma(){
		$soma = 0;
		foreach($this->lancamentos as $l){
			$soma += $l->valor;
		}
		return $soma;
	}

	public function percentual(){
		if($this->soma() == 0) return 0;
		if($this->getFaturamento() == 0) return 0;
		$soma = ($this->soma()/$this->getFaturamento())*100;
		return $soma;
	}

	public function getFaturamento(){
		$faturamento = 0;
		$f = $this->dre->categorias[2];
		foreach($f->lancamentos as $l){
			$faturamento += $l->valor;
		}

		return $faturamento;
	}
}
