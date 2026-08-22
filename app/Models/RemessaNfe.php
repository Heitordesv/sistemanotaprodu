<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemessaNfe extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', 'usuario_id', 'valor_total', 'forma_pagamento', 'numero_nfe',
        'natureza_id', 'chave', 'estado_emissao', 'observacao', 'desconto', 'transportadora_id', 
        'sequencia_cce', 'empresa_id', 'acrescimo', 'data_entrega', 'nSerie', 'data_emissao', 
        'numero_sequencial', 'filial_id', 'baixa_estoque', 'tipo_nfe', 'placa', 'uf', 
        'valor_frete', 'tipo_frete', 'qtd_volumes', 'numeracao_volumes',
        'especie', 'peso_liquido', 'peso_bruto', 'data_retroativa', 'gerar_conta_receber', 
        'venda_caixa_id'
    ];

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

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function filial(){
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function fatura(){
        return $this->hasMany(RemessaNfeFatura::class, 'remessa_id', 'id');
    }

    public function duplicatas()
    {
        return $this->hasMany(ContaReceber::class, 'remessa_nfe_id', 'id');
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function natureza(){
        return $this->belongsTo(NaturezaOperacao::class, 'natureza_id');
    }

    public function transportadora(){
        return $this->belongsTo(Transportadora::class, 'transportadora_id');
    }

    public function itens(){
        return $this->hasMany(ItemRemessaNfe::class, 'remessa_id', 'id');
    }

    public function referencias(){
        return $this->hasMany(RemessaReferenciaNfe::class, 'remessa_id', 'id');
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
            '90' => 'Sem Pagamento',
            '99' => 'Outros',
        ];
    }

    public static function getTipo($tipo)
    {
        $tipos = RemessaNfe::tiposPagamento();
        return $tipos[$tipo];
    }
    
    public function getTipoPagamento()
    {
        foreach (RemessaNfe::tiposPagamento() as $key => $t) {
            if ($this->tipo_pagamento == $key) return $t;
        }
    }

    public function getFormaPagamento($empresa_id)
    {
        $forma = FormaPagamento::where('chave', $this->forma_pagamento)
            ->where('empresa_id', $empresa_id)
            ->first();

        return $forma;
    }

}
