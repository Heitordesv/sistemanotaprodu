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
		'retorno_estoque', 'qr_code_base64', 'filial_id', 'abertura_caixa_id'
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
			'99' => 'Outros'
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

	public static function lastNFCe($empresa_id = null){
		if($empresa_id == null){
			$value = session('user_logged');
			$empresa_id = $value['empresa'];
		}else{
			$empresa_id = $empresa_id;
		}

		$venda = VendaCaixa::
		where('numero_nfce', '!=', 0)
		->where('empresa_id', $empresa_id)
		->orderBy('numero_nfce', 'desc')
		->first();

		if($venda == null) {
			return ConfigNota::
			where('empresa_id', $empresa_id)
			->first()->ultimo_numero_nfce;
		}
		else{
			$configNum = ConfigNota::
			where('empresa_id', $empresa_id)->first()->ultimo_numero_nfce;
			return $configNum > $venda->numero_nfce ? $configNum : $venda->numero_nfce;
		}
	}

	public static function filtroData($dataInicial, $dataFinal, $config){
		$value = session('user_logged');
		$empresa_id = $value['empresa'];

		return VendaCaixa::
		orderBy('id', 'desc')
		->whereBetween('data_registro', [$dataInicial,
			$dataFinal])
		->where('empresa_id', $empresa_id)
		->when($config->caixa_por_usuario == 1, function ($q) use ($config) {
			return $q->where('usuario_id', get_id_user());
		})
		->get();
	}

	public static function filtroCliente($cliente){

		$value = session('user_logged');
		$empresa_id = $value['empresa'];

		return VendaCaixa::
		join('clientes', 'clientes.id' , '=', 'venda_caixas.cliente_id')
		->where('clientes.razao_social', 'LIKE', "%$cliente%")
		->where('venda_caixas.empresa_id', $empresa_id)
		->get();
	}

	public static function filtroData2($data){

		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$data = str_replace("/", "-", $data);
		$data = \Carbon\Carbon::parse($data)->format('Y-m-d');

		return VendaCaixa::
		whereBetween('created_at', [
			$data . " 00:00:00",
			$data . " 23:59:59",
		])
		->where('empresa_id', $empresa_id)
		->get();
	}

	public static function filtroNFCe($nfce){

		$value = session('user_logged');
		$empresa_id = $value['empresa'];

		return VendaCaixa::
		where('NFcNumero', $nfce)
		->where('empresa_id', $empresa_id)
		->get();
	}

	public static function filtroValor($valor){

		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		return VendaCaixa::
		where('valor_total', 'LIKE', "%$valor%")
		->where('empresa_id', $empresa_id)
		->get();
	}

	public static function filtroEstado($estado){

		$value = session('user_logged');
		$empresa_id = $value['empresa'];
		$c = VendaCaixa::
		where('estado_emissao', $estado)
		->where('empresa_id', $empresa_id)
		->where('forma_pagamento', '!=', 'conta_crediario');
		return $c->get();
	}

	public static function filtroDataApp($dataInicial, $dataFinal, $empresa_id){

		return VendaCaixa::
		orderBy('id', 'desc')
		->whereBetween('data_registro', [$dataInicial,
			$dataFinal])
		->where('empresa_id', $empresa_id)
		->get();
	}

	public static function filtroEstadoApp($estado, $empresa_id){
		$c = VendaCaixa::
		where('estado_emissao', $estado)
		->where('empresa_id', $empresa_id)
		->where('forma_pagamento', '!=', 'conta_crediario');
		return $c->get();
	}

	public function multiplo(){
		$text = '';
		// if($this->valor_pagamento_1 > 0){
		// 	$text .= VendaCaixa::getTipoPagamento($this->tipo_pagamento_1) . ' - R$ ' . number_format($this->valor_pagamento_1, 2);
		// }

		// if($this->valor_pagamento_2 > 0){
		// 	$text .= ' | '.VendaCaixa::getTipoPagamento($this->tipo_pagamento_2) . ' - R$ ' . number_format($this->valor_pagamento_2, 2);
		// }

		// if($this->valor_pagamento_3 > 0){
		// 	$text .= ' | '.VendaCaixa::getTipoPagamento($this->tipo_pagamento_3) . ' - R$ ' . number_format($this->valor_pagamento_3, 2);
		// }

		foreach($this->fatura as $key => $f){
			$text .= ($key > 0 ? ' | ' : '') .VendaCaixa::getTipoPagamento($f->forma_pagamento) . ' - R$ ' . number_format($f->valor, 2);
		}
		return $text;
	}

	public static function tiposPagamentoMulti(){
		return [
			'DINHEIRO',
			'CARTÃO DE DÉBITO',
			'CARTÃO DE CRÉDITO',
			'VALE REFEIÇÃO'
		];
	}

	public function isComprovanteAssessor(){
		foreach($this->itens as $i){
			if($i->valor_comissao_assessor > 0) return true;
		}
		return false;
	}

}
