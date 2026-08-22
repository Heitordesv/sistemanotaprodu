<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pais;
class Cliente extends Model
{
	protected $fillable = [
		'razao_social', 'nome_fantasia', 'bairro', 'numero', 'rua', 'cpf_cnpj', 'telefone',
		'celular', 'email', 'cep', 'ie_rg', 'consumidor_final', 'limite_venda', 'cidade_id',
		'contribuinte', 'rua_cobranca', 'numero_cobranca', 'bairro_cobranca', 'cep_cobranca',
		'cidade_cobranca_id', 'empresa_id', 'cod_pais', 'id_estrangeiro', 'grupo_id',
		'contador_nome', 'contador_telefone', 'funcionario_id', 'observacao',
		'contador_email', 'data_aniversario', 'complemento', 'nuvemshop_id', 'acessor_id',
		'imagem', 'inativo'
	];


	public function getImgAttribute()
	{
		if(!$this->imagem){
			return "/imgs/no_client.png";
		}
		return "/uploads/clients/$this->imagem";
	}

	public function cidade(){
		return $this->belongsTo(Cidade::class, 'cidade_id');
	}

	public static function tiposPesquisa(){
		return [
			'razao_social' => 'Razão social',
			'nome_fantasia' => 'Nome fantasia',
			'telefone' => 'Telefone',
		];
	}

	public static function estados(){
		return [
			"AC",
			"AL",
			"AM",
			"AP",
			"BA",
			"CE",
			"DF",
			"ES",
			"GO",
			"MA",
			"MG",
			"MS",
			"MT",
			"PA",
			"PB",
			"PE",
			"PI",
			"PR",
			"RJ",
			"RN",
			"RS",
			"RO",
			"RR",
			"SC",
			"SE",
			"SP",
			"TO"
		];
	}

	public static function verificaCadastrado($cnpj){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$forn = Cliente::where('cpf_cnpj', $cnpj)
		->where('empresa_id', $empresa_id)
		->first();

		return $forn;
	}

	public function getPais(){
		$pais = Pais::where('codigo', $this->cod_pais)->first();
		return $pais->nome;
	}
	public function agendamentos()
{
    return $this->hasMany(Agendamento::class, 'cliente_id');
}
public function vendaCaixaPreVendas()
{
    return $this->hasMany(VendaCaixaPreVenda::class, 'cliente_id');
}

public function remessas()
{
    return $this->hasMany(RemessaNfe::class, 'cliente_id', 'id');
}

public function ordensServico()
{
    return $this->hasMany(OrdemServico::class, 'cliente_id', 'id');
}

public function contasReceber()
{
    return $this->hasMany(ContaReceber::class, 'cliente_id', 'id');
}

public function orcamentos()
{
    return $this->hasMany(Orcamento::class, 'cliente_id', 'id');
}

public function vendasCaixa()
{
    return $this->hasMany(VendaCaixa::class, 'cliente_id', 'id');
}

public function vendas()
{
    return $this->hasMany(Venda::class, 'cliente_id', 'id');
}

}
