<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acessor extends Model
{

	protected $fillable = [
		'razao_social', 'bairro', 'numero', 'rua', 'cpf_cnpj', 'telefone',
		'celular', 'email', 'cep', 'cidade_id', 'empresa_id', 'data_registro',
		'percentual_comissao', 'ativo', 'funcionario_id', 'ie_rg'
	];

	public function cidade(){
		return $this->belongsTo(Cidade::class, 'cidade_id');
	}
}
