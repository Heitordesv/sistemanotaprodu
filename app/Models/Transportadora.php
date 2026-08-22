<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transportadora extends Model
{
    protected $fillable = [
		'razao_social', 'cnpj_cpf', 'logradouro', 'cidade_id', 'empresa_id', 'email',
        'telefone'
	];

	public function cidade(){
		return $this->belongsTo(Cidade::class, 'cidade_id');
	}

	public static function verificaCadastrado($cnpj){
    	$value = session('user_logged');
        $empresa_id = $value['empresa'];
        $transp = Transportadora::where('cnpj_cpf', $cnpj)
        ->where('empresa_id', $empresa_id)
        ->first();
        return $transp;
    }
}
