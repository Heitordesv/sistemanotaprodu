<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
	protected $fillable = [
		'nome', 'valor', 'maximo_clientes', 'maximo_produtos', 'maximo_fornecedores', 
		'maximo_nfes', 'maximo_nfces', 'maximo_cte', 'maximo_mdfe', 'maximo_evento', 
		'maximo_usuario', 'delivery', 'perfil_id', 'descricao', 'img', 'intervalo_dias',
		'visivel', 'maximo_usuario_simultaneo', 'armazenamento', 'api_sieg'
	];


	public static function backgroundArmazenamento($perc){
		if($perc <= 65){
			return 'bg-success';
		}elseif($perc > 65 && $perc < 80){
			return 'bg-warning';
		}else{
			return 'bg-danger';
		}
	}

	public static function divPlanos(){
		$planos = Plano::
		where('visivel', 1)
		->count();
		if($planos == 1){
			return 'col-xl-4 offset-xl-1';
		}
		if($planos == 2){
			return 'col-xl-4 offset-xl-1';
		}
		if($planos == 3 || $planos > 4){
			return 'col-xl-3';
		}
		if($planos == 4){
			return 'col-xl-3';
		}
	}
}
