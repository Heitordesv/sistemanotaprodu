<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Devolucao;
use App\Models\ConfigNota;
use App\Models\FormaPagamento;
use App\Models\Compra;

class Venda extends Model
{
    protected $fillable = [
        'cliente_id', 'usuario_id', 'frete_id', 'valor_total', 'forma_pagamento', 'numero_nfe',
        'natureza_id', 'chave', 'estado_emissao', 'observacao', 'desconto',
        'transportadora_id', 'sequencia_cce', 'tipo_pagamento', 'empresa_id',
        'pedido_ecommerce_id', 'bandeira_cartao', 'cnpj_cartao', 'cAut_cartao',
        'descricao_pag_outros', 'acrescimo', 'data_entrega', 'pedido_nuvemshop_id',
        'nSerie', 'data_emissao', 'filial_id', 'abertura_caixa_id'
    ];

    public function filial(){
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function aberturaCaixa()
    {
        return $this->belongsTo(AberturaCaixa::class, 'abertura_caixa_id');
    }
    
    public function vendedor_setado()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    public function duplicatas()
    {
        return $this->hasMany(ContaReceber::class, 'venda_id', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function pedidoNuvemShop()
    {
        return $this->belongsTo(NuvemShopPedido::class, 'pedido_nuvemshop_id');
    }

    public function natureza()
    {
        return $this->belongsTo(NaturezaOperacao::class, 'natureza_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function vendedor()
    {
        $usuario = Usuario::find($this->usuario_id);
        if ($usuario->funcionario) return $usuario->funcionario->nome;
        else return '--';
    }

    public function frete()
    {
        return $this->belongsTo(Frete::class, 'frete_id');
    }

    public function transportadora()
    {
        return $this->belongsTo(Transportadora::class, 'transportadora_id');
    }

    public function itens()
    {
        return $this->hasMany(ItemVenda::class, 'venda_id', 'id');
    }

    public function referencias()
    {
        return $this->hasMany(NFeReferecia::class, 'venda_id', 'id');
    }

    public static function tiposPagamento()
    {
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
            '90' => 'Sem Pagamento',
            '99' => 'Outros',
        ];
    }

    public static function bandeiras()
    {
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

    public static function getTipo($tipo)
    {
        if(isset(Venda::tiposPagamento()[$tipo])){
			return Venda::tiposPagamento()[$tipo];
		}else{
			return "Não identificado";
		}
        // $tipos = Venda::tiposPagamento();
        // return $tipos[$tipo];
    }

    public static function getTipoPagamento2($tipo){
		if(isset(VendaCaixa::tiposPagamento()[$tipo])){
			return VendaCaixa::tiposPagamento()[$tipo];
		}else{
			return "Não identificado";
		}
	}

    public static function filtroData($dataInicial, $dataFinal, $estado, $tipoPesquisaData, $numero_nfe)
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = Venda::select('vendas.*')
            ->whereBetween($tipoPesquisaData, [
                $dataInicial,
                $dataFinal
            ])
            ->where('vendas.empresa_id', $empresa_id)
            ->where('vendas.forma_pagamento', '!=', 'conta_crediario');

        if ($estado != 'TODOS') $c->where('vendas.estado', $estado);
        if ($numero_nfe != "") {
            $c->where('NfNumero', $numero_nfe);
        }
        return $c->get();
    }

    public static function filtroDataCliente(
        $cliente,
        $dataInicial,
        $dataFinal,
        $estado,
        $tipoPesquisa,
        $tipoPesquisaData,
        $numero_nfe
    ) {

        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = Venda::select('vendas.*')
            ->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
            ->where('clientes.' . $tipoPesquisa, 'LIKE', "%$cliente%")
            ->where('vendas.forma_pagamento', '!=', 'conta_crediario')
            ->where('vendas.empresa_id', $empresa_id)

            ->whereBetween($tipoPesquisaData, [
                $dataInicial,
                $dataFinal
            ]);
        if ($numero_nfe != "") {
            $c->where('NfNumero', $numero_nfe);
        }

        if ($estado != "TODOS") $c->where('vendas.estado', $estado);
        return $c->get();
    }

    public static function filtroCliente($cliente, $estado, $tipoPesquisa, $numero_nfe)
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = Venda::select('vendas.*')
            ->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
            ->where('clientes.' . $tipoPesquisa, 'LIKE', "%$cliente%")
            ->where('vendas.empresa_id', $empresa_id)
            ->where('vendas.forma_pagamento', '!=', 'conta_crediario');

        if ($estado != "TODOS") $c->where('vendas.estado', $estado);
        if ($numero_nfe != "") {
            $c->where('NfNumero', $numero_nfe);
        }
        return $c->get();
    }

    public static function filtroEstado($estado, $numero_nfe)
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = Venda::where('vendas.estado', $estado)
            ->where('vendas.empresa_id', $empresa_id)
            ->where('vendas.forma_pagamento', '!=', 'conta_crediario');

        if ($numero_nfe != "") {
            $c->where('NfNumero', $numero_nfe);
        }
        return $c->get();
    }

    public static function filtroDataApp($dataInicial, $dataFinal, $estado, $empresa_id)
    {

        $c = Venda::select('vendas.*')
            ->whereBetween('data_registro', [
                $dataInicial,
                $dataFinal
            ])
            ->where('vendas.empresa_id', $empresa_id)
            ->where('vendas.forma_pagamento', '!=', 'conta_crediario');

        if ($estado != 'TODOS') $c->where('vendas.estado', $estado);

        return $c->get();
    }

    public static function filtroDataClienteApp($cliente, $dataInicial, $dataFinal, $estado, $empresa_id)
    {

        $c = Venda::select('vendas.*')
            ->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
            ->where('clientes.razao_social', 'LIKE', "%$cliente%")
            ->where('vendas.forma_pagamento', '!=', 'conta_crediario')
            ->where('vendas.empresa_id', $empresa_id)

            ->whereBetween('data_registro', [
                $dataInicial,
                $dataFinal
            ]);

        if ($estado != 'TODOS') $c->where('vendas.estado', $estado);
        return $c->get();
    }

    public static function filtroClienteApp($cliente, $estado, $empresa_id)
    {

        $c = Venda::select('vendas.*')
            ->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
            ->where('clientes.razao_social', 'LIKE', "%$cliente%")
            ->where('vendas.forma_pagamento', '!=', 'conta_crediario')
            ->where('vendas.empresa_id', $empresa_id);

        if ($estado != 'TODOS') $c->where('vendas.estado', $estado);

        return $c->get();
    }

    public static function filtroEstadoApp($estado, $empresa_id)
    {

        $c = Venda::where('vendas.estado', $estado)
            ->where('vendas.empresa_id', $empresa_id)
            ->where('vendas.forma_pagamento', '!=', 'conta_crediario');
        return $c->get();
    }

    public function getTipoPagamento()
    {
        foreach (Venda::tiposPagamento() as $key => $t) {
            if ($this->tipo_pagamento == $key) return $t;
        }
    }

    public static function getTipoPagamentoNFe($tipo)
    {
        $values = [
            'Dinheiro' => '01',
            'Cheque' => '02',
            'Cartão de Crédito' => '03',
            'Cartão de Débito' => '04',
            'Crédito Loja' => '05',
            'Crediário' => '06',
            'Vale Alimentação' => '10',
            'Vale Refeição' => '11',
            'Vale Presente' => '12',
            'Vale Combustível' => '13',
            'Duplicata Mercantil' => '14',
            'Boleto Bancário' => '15',
            'Depósito Bancário' => '16',
            'Pagamento Instantâneo (PIX)' => '17',
             'Pagamento QRCODE (PIX)' => '19',
            'Sem Pagamento' => '90',
            'Outros' => '99',
        ];
        try {
            return $values[$tipo];
        } catch (\Exception $e) {
            return $values["Dinheiro"];
        }
    }

    public function estadoEmissao()
    {
        if ($this->estado_emissao == 'aprovado') {
            return "<span class='btn btn-sm btn-success'>Aprovado</span>";
        } else if ($this->estado_emissao == 'cancelado') {
            return "<span class='btn btn-sm btn-danger'>Cancelado</span>";
        } else if ($this->estado_emissao == 'rejeitado') {
            return "<span class='btn btn-sm btn-warning'>Rejeitado</span>";
        }
        return "<span class='btn btn-sm btn-info'>Novo</span>";
    }

    public static function estados()
    {
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
            "TO",

        ];
    }

    public function multiplo()
    {
        return "Outros";
    }

    public function taxaFormaPagamento()
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $formaPag = FormaPagamento::where('nome', $this->forma_pagamento)
            ->where('empresa_id', $empresa_id)
            ->first();
        if ($formaPag != null) {
            if ($formaPag->tipo_taxa == 'perc') {
                return number_format($formaPag->taxa, 2, ',', '.') . '%';
            } else {
                return 'R$ ' . number_format($formaPag->taxa, 2, ',', '.');
            }
        } else {
            return "0,00";
        }
    }

    public function valorLiquido()
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $formaPag = FormaPagamento::where('nome', $this->forma_pagamento)
            ->where('empresa_id', $empresa_id)
            ->first();
        if ($formaPag != null) {
            $total = $this->valor_total + $this->acrescimo - $this->desconto;
            $valor = 0;
            if ($formaPag->tipo_taxa == 'perc') {
                $valor = $total - (($total * $formaPag->taxa) / 100);
            } else {
                $valor = $total - $formaPag->taxa;
            }

            return $valor;
        } else {
            return $this->valor_total - $this->desconto + $this->acrescimo;
        }
    }

    public function valorDespesaOperacionais()
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $formaPag = FormaPagamento::where('nome', $this->forma_pagamento)
            ->where('empresa_id', $empresa_id)
            ->first();
        if ($formaPag != null) {
            $total = $this->valor_total + $this->acrescimo - $this->desconto;
            $valor = 0;
            if ($formaPag->tipo_taxa == 'perc') {
                $valor = (($total * $formaPag->taxa) / 100);
            } else {
                $valor = $formaPag->taxa;
            }

            return 'R$ ' . number_format($valor, 2, ',', '.');
        } else {
            return "R$ 0,00";
        }
    }

    public function getFormaPagamento($empresa_id)
    {
        $forma = FormaPagamento::where('chave', $this->forma_pagamento)
            ->where('empresa_id', $empresa_id)
            ->first();

        return $forma;
    }

    public static function randSuccess()
    {
        $arr = [
            'success.json',
            'success2.json',
            'success3.json',
            'success4.json',
        ];
        $rand = rand(0, sizeof($arr) - 1);
        return $arr[$rand];
    }
}