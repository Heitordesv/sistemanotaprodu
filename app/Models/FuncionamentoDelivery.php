<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuncionamentoDelivery extends Model
{

    protected $fillable = [
        'ativo', 'dia', 'inicio_expediente', 'fim_expediente', 'empresa_id'
    ];

    public static function dias(){
    	return [
            'DOMINGO' => 'DOMINGO',
    		'SEGUNDA' => 'SEGUNDA',
    		'TERÇA' => 'TERÇA',
    		'QUARTA' => 'QUARTA',
    		'QUINTA' => 'QUINTA',
    		'SEXTA' => 'SEXTA',
    		'SABADO' => 'SABADO'
    	];
    }

    public static function _dias(){
        return [
            'DOMINGO',
            'SEGUNDA',
            'TERÇA',
            'QUARTA',
            'QUINTA',
            'SEXTA',
            'SABADO'
        ];
    }

    public static function getDia($dia){
        return FuncionamentoDelivery::_dias()[$dia];
    }
}
