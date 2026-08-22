<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComissaoVenda extends Model
{
	protected $fillable = [
		'funcionario_id', 'venda_id', 'tabela', 'valor', 'status', 'empresa_id'
	];

	public function funcionario(){
		return $this->belongsTo(Funcionario::class, 'funcionario_id');
	}

	public function valodDaVenda(){
		if($this->tabela == 'vendas'){
			return $this->belongsTo(Venda::class, 'venda_id');
		}else{
			return $this->belongsTo(VendaCaixa::class, 'venda_id');
		}
	}

	public function tipo(){
		if($this->tabela == 'vendas'){
			return 'Venda';
		}else{
			return 'PDV';
		}
	}
}
