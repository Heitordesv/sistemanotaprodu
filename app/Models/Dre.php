<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DreCategoria;


class Dre extends Model
{
	protected $fillable = [
		'empresa_id', 'inicio', 'fim', 'observacao', 'percentual_imposto', 'lucro_prejuizo'
	];

	public function criaCategoriasPreDefinidas(){
		$categoriaNomes = $this->categoriaNomes();
		foreach($categoriaNomes as $nome){
			DreCategoria::create(
				[
					'nome' => $nome,
					'dre_id' => $this->id
				]
			);
		} 
	}

	private function categoriaNomes(){
		return [
			'Faturamento Bruto',	
			'Total de Deduções',	
			'Faturamento Líquido',	
			'Custos de Produção Variáveis',
			'Custos Fixos e Despesas'
		];
	}

	public function categorias(){
        return $this->hasMany('App\Models\DreCategoria', 'dre_id', 'id');
    }
    
}
