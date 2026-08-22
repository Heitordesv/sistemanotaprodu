<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{

    protected $fillable = [
        'nome', 'uf', 'codigo'
    ];

    public function getInfoAttribute()
    {
        return "$this->nome ($this->uf)";
    }

    public static function getCidadeCod($codMun){
    	return Cidade::
    	where('codigo', $codMun)
    	->first();
    }

    public static function getId($id){
    	return Cidade::
    	where('id', $id)
    	->first();
    }

    public static function estados(){
        return [
            "AC" => "AC",
            "AL" => "AL",
            "AM" => "AM",
            "AP" => "AP",
            "BA" => "BA",
            "CE" => "CE",
            "DF" => "DF",
            "ES" => "ES",
            "GO" => "GO",
            "MA" => "MA",
            "MG" => "MG",
            "MS" => "MS",
            "MT" => "MT",
            "PA" => "PA",
            "PB" => "PB",
            "PE" => "PE",
            "PI" => "PI",
            "PR" => "PR",
            "RJ" => "RJ",
            "RN" => "RN",
            "RS" => "RS",
            "RO" => "RO",
            "RR" => "RR",
            "SC" => "SC",
            "SE" => "SE",
            "SP" => "SP",
            "TO" => "TO"
        ];
    }
}
