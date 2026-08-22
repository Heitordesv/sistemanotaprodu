<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ItemIBTE;

class IBPT extends Model
{
	protected $table = 'i_b_p_ts';
	protected $fillable = [
		'uf', 'versao'
	];

	public function itens(){
		return $this->hasMany('App\Models\ItemIBTE', 'ibte_id', 'id');
	}

	public static function estados(){
		return [
			"AC",
			"AL",
			"AM",
			"AP",
			"BA",
			"CE",
			"DF",
			"ES",
			"GO",
			"MA",
			"MG",
			"MS",
			"MT",
			"PA",
			"PB",
			"PE",
			"PI",
			"PR",
			"RJ",
			"RN",
			"RS",
			"RO",
			"RR",
			"SC",
			"SE",
			"SP",
			"TO",
			
		];
	}

	public static function getIBPT($uf, $codigo){
		$trib = ItemIBTE::
		join('i_b_p_ts', 'i_b_p_ts.id' , '=', 'item_i_b_t_es.ibte_id')
		->where('i_b_p_ts.uf', $uf)
		->where('item_i_b_t_es.codigo', $codigo)
		->first();

		return $trib;
	}
}
