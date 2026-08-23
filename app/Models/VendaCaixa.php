<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ConfigNota;

class VendaCaixa extends Model
{
	protected $fillable = [
		'cliente_id', 'usuario_id', 'valor_total', 'numero_nfce',
		'natureza_id', 'chave', 'estado_emissao', 'tipo_pagamento', 'forma_pagamento',
		'dinheiro_recebido', 'troco', 'nome', 'cpf', 'observacao', 'desconto', 'acrescimo',
		'pedido_delivery_id', 'empresa_id', 'bandeira_cartao',
		'cnpj_cartao', 'cAut_cartao', 'descricao_pag_outros', 'rascunho', 'consignado', 'pdv_java',
		'retorno_estoque', 'qr_code_base64', 'filial_id', 'abertura_caixa_id', 'estoque_filial_id'
	];


	public function filial(){
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function aberturaCaixa()
    {
        return $this->belongsTo(AberturaCaixa::class, 'abertura_caixa_id');
    }
	
	public function itens(){
		return $this->hasMany(ItemVendaCaixa::class, 'venda_caixa_id', 'id');
	}

	public function fatura(){
		return $this->hasMany('App\Models\FaturaFrenteCaixa', 'venda_caixa_id', 'id');
	}
	

	public function cliente(){
		return $this->belongsTo(Cliente::class, 'cliente_id');
	}

	public function pedidoDelivery(){
		return $this->belongsTo(PedidoDelivery::class, 'pedido_delivery_id');
	}

	public function natureza(){
		return $this->belongsTo(NaturezaOperacao::class, 'natureza_id');
	}

	public function usuario(){
		return $this->belongsTo(Usuario::class, 'usuario_id');
	}

	public function estadoEmissao(){
		if($this->estado_emissao == 'aprovado'){
			return "<span class='btn btn-sm btn-success'>Aprovado</span>";
		}else if($this->estado_emissao == 'cancelado'){
			return "<span class='btn btn-sm btn-danger'>Cancelado</span>";
		}else if($this->estado_emissao == 'rejeitado'){
			return "<span class='btn btn-sm btn-warning'>Rejeitado</span>";
		}
		return "<span class='btn btn-sm btn-info'>Novo</span>";
	}

	public function vendedor(){
		$usuario = Usuario::find($this->usuario_id);
		if($usuario->funcionario) return $usuario->funcionario->nome;
		else return '--';
	}

	public static function tiposPagamento(){
		return [
			'01' => 'Dinheiro',
			'02' => 'Cheque',
			'03' => 'Cartão de Crédito',
			'04' => 'Cartão de Débito',
			'05' => 'Crédito Loja',
			'06' => 'Crediário',
			'10' => 'Vale Alimentação',
			'11' => 'Vale Refeição',
			'12' => 'Vale Presente',
			'13' => 'Vale Combustível',
			'14' => 'Duplicata Mercantil',
			'15' => 'Boleto Bancário',
			'16' => 'Depósito Bancário',
			'17' => 'Pagamento Instantâneo (PIX)',
			'19' => 'Pagamento QRCODE (PIX)',
			'90' => 'Sem pagamento',
			'99' => 'Outros',
		];
	}

	public static function bandeiras(){
		return [
			'01' => 'Visa',
			'02' => 'Mastercard',
			'03' => 'American Express',
			'04' => 'Sorocred',
			'05' => 'Diners Club',
			'06' => 'Elo',
			'07' => 'Hipercard',
			'08' => 'Aura',
			'09' => 'Cabal',
			'99' => 'Outros',
		];
	}

	public static function getTipoPagamento($tipo){
		if(isset(VendaCaixa::tiposPagamento()[$tipo])){
			return VendaCaixa::tiposPagamento()[$tipo];
		}else{
			return "Não identificado";
		}
	}

	public function getTipoPagamento2(){
		foreach(VendaCaixa::tiposPagamento() as $key => $t){
			if($this->tipo_pagamento == $key) return $t;
		}
	}

	public static function tiposPagamentoMulti(){
		return [
			'01' => 'Dinheiro',
			'03' => 'Cartão de Crédito',
			'04' => 'Cartão de Débito',
			'06' => 'Crediário',
			'17' => 'PIX',
			'99' => 'Outros',
		];
	}
}
