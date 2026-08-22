<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryConfig extends Model
{
	protected $fillable = [
		'link_face', 'link_twiteer', 'link_google', 'link_instagram', 'telefone',
		'rua', 'numero', 'bairro', 'cep', 'tempo_medio_entrega', 'valor_entrega', 
		'tempo_maximo_cancelamento', 
		'nome', 'latitude', 'longitude', 'politica_privacidade', 
		'valor_entrega_gratis', 'valor_km', 'usar_bairros', 'maximo_km_entrega', 
		'maximo_adicionais', 'maximo_adicionais_pizza', 'empresa_id', 'descricao', 'logo',
		'cidade_id', 'status', 'tipos_pagamento', 'tipo_divisao_pizza', 'tipo_entrega',
		'pedido_minimo', 'mercadopago_public_key', 'mercadopago_access_token', 
		'avaliacao_media', 'maximo_sabores_pizza', 'api_token', 'notificacao_novo_pedido',
		'autenticacao_sms', 'confirmacao_pedido_cliente'
	];

	protected $appends = [
		'logo_path'
	];

	public function getLogoPathAttribute()
	{
		return env("PATH_URL") . "/delivery/logos/" . $this->logo;
	}

	public function categorias(){
		return $this->hasMany(CategoriaLojaSegmento::class, 'loja_id')->with('categoria');
	}

	public function galeria(){
		return $this->hasMany(DeliveryConfigGaleria::class, 'config_id');
	}

	public function empresa(){
		return $this->belongsTo(Empresa::class, 'empresa_id');
	}

	public function cidade(){
		return $this->belongsTo(CidadeDelivery::class, 'cidade_id');
	}

	public function getFuncionamento($dia, $empresa_id){
		$retorno = [
			'aberto' => false,
			'fim_expediente' => '',
			'inicio_expediente' => '',
		];

		$dataHoje = date('Y-m-d');
		$dataAmanha = date('Y-m-d', strtotime('+1 days'));
		$funcionamento = FuncionamentoDelivery::
		where('dia', $dia)
		->where('empresa_id', $empresa_id)
		->first();
		$atual = strtotime(date('Y-m-d H:i'));

		if($funcionamento != null){
			$validaHora = explode(":", $funcionamento->fim_expediente);
			$aberto = false;
			$inicio = strtotime($dataHoje . " " . $funcionamento->inicio_expediente);
			$fim = strtotime($dataHoje . " " . $funcionamento->fim_expediente);

			if((int)$validaHora[0] < 4){
				die;
				//dia seguinte
				$fim = strtotime($dataAmanha . " " . $funcionamento->fim_expediente);
			}

			if($atual > $inicio && $atual < $fim){
				$aberto = true;
			}
			
			$retorno['fim_expediente'] = $funcionamento->fim_expediente;
			$retorno['inicio_expediente'] = $funcionamento->inicio_expediente;
			$retorno['aberto'] = $aberto;
		}

		return $retorno;
	}


	public static function tiposPagamento(){
		return [
			'Dinheiro',
			'Visa crédito',
			'Mastercard crédito',
			'Hipercard crédito',
			'Elo crédito',
			'Visa débito',
			'Mastercard débito',
			'Hipercard débito',
			'Elo débito',
			'Pix',
			'Pix pelo App',
			'Cartão pelo App',
		];
	}
}
