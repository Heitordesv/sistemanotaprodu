<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
	protected $fillable = [
		'nome', 'bairro', 'numero', 'rua', 'cpf', 'rg', 'telefone', 'celular',
		'email', 'data_registro', 'empresa_id', 'usuario_id', 'percentual_comissao',
		'salario', 'cidade_id','data_nasci'
	];

	public function contatos()
	{
		return $this->hasMany('App\Models\ContatoFuncionario', 'funcionario_id', 'id');
	}

	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'usuario_id');
	}

	public function cidade()
	{
		return $this->belongsTo(Cidade::class, 'cidade_id');
	}

	public function eventos()
	{
		return $this->hasMany(FuncionarioEvento::class, 'funcionario_id');
	}

	public function eventosAtivos()
	{
		return $this->hasMany(FuncionarioEvento::class, 'funcionario_id')->where('ativo', 1);
	}
}
