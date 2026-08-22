<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
	protected $fillable = [
		'banco_id', 'conta_id', 'numero', 'numero_documento', 'carteira', 'convenio', 
		'linha_digitavel', 'nome_arquivo', 'juros', 'multa', 'juros_apos', 'instrucoes', 
		'logo', 'tipo', 'codigo_cliente', 'posto'
	];

	public function conta(){
        return $this->belongsTo(ContaReceber::class, 'conta_id');
    }

    public function banco(){
        return $this->belongsTo(ContaBancaria::class, 'banco_id');
    }

    public function itemRemessa(){
        return $this->hasOne('App\Models\RemessaBoleto', 'boleto_id', 'id');
    }
}
