<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoOs
{
	const PENDENTE = 'pd';
	const APROVADO = 'ap';
	const REPROVADO = 'rp';
	const FINALIZADO = 'fz';

	public static function values(){
		return ['PENDENTE'];
	}
    
}
