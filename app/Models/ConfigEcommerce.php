<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigEcommerce extends Model
{
protected $fillable = [
    'nome',
    'link',
    'imagem',
    'rua',
    'numero',
    'bairro',
    'cidade_id',
    'cep',
    'telefone',
    'email',
    'link_facebook',
    'link_twiter',
    'link_instagram',
    'frete_gratis_valor',
    'mercadopago_public_key',
    'mercadopago_access_token',
    'mercadopago_webhook_secret',
    'funcionamento',
    'latitude',
    'longitude',
    'politica_privacidade',
    'empresa_id',
    'src_mapa',
    'cor_principal',
    'google_api',
    'tema_ecommerce',
    'uf',
    'habilitar_retirada',
    'desconto_padrao_boleto',
    'desconto_padrao_pix',
    'desconto_padrao_cartao',
    'api_token',
    'usar_api',
    'fav_icon',
    'timer_carrossel',
    'img_contato',
    'cor_fundo',
    'cor_btn',
    'mensagem_agradecimento',
    'token',
    'formas_pagamento',

    // Campos de credenciais dos Correios
    'correios_usuario',
    'correios_senha',
    'correios_cartao',
];

    protected $hidden = [
        'mercadopago_access_token',
        'mercadopago_webhook_secret',
        'correios_senha',
    ];

    public function cidade(){
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public function getImgAttribute()
	{
		if(!$this->imagem){
			return "/ecommerce/logo.png";
		}

		return "/uploads/configEcommerce/$this->imagem";
	}

	protected $appends = ['logo_url', 'contato_url', 'fav_url', 'img'];


	public function getLogoUrlAttribute()
    {
        if (!empty($this->logo)) {
            $image_url = asset('/ecommerce/logos/' . rawurlencode($this->logo));
        } else {
            $image_url = '';
        }
        return $image_url;
    }


    public function getContatoUrlAttribute()
    {

        if(!$this->img_contato){
            return "/imgs/no_image.png";
        }

        return "/uploads/contatoEcommerce/$this->img_contato";

    }

    public function getFavUrlAttribute()
    {
        if(!$this->fav_icon){
            return "/imgs/no_image.png";
        }
        return "/uploads/favIcon/$this->fav_icon";
    }

    public static function formasPagamento(){
        return [
            'cartao' => 'Cart«ªo de credito',
            'pix' => 'Pix',
            'boleto' => 'Boleto',
        ];
    }
}