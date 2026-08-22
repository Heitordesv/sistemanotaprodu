<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tributacao extends Model
{
	protected $fillable = [
		'icms', 'pis', 'cofins', 'regime', 'ipi', 'ncm_padrao', 'empresa_id', 'link_nfse',
		'perc_ap_cred'
	];

	public static function regimes(){
		return [ 
			0 => 'Simples',
			1 => 'Normal',
			2 => 'MEI',
		];
	}
}
