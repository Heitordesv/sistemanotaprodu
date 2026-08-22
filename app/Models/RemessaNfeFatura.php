<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemessaNfeFatura extends Model
{
    use HasFactory;

    protected $fillable = [
        'remessa_id', 'tipo_pagamento', 'valor', 'data_vencimento'
    ];

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
            '90' => 'Sem Pagamento',
            '99' => 'Outros',
        ];
    }

    // public function getTipo(){
    //     $tipos = RemessaNfeFatura::tiposPagamento();
    //     return $tipos[$this->tipo_pagamento];
    // }

    public function getTipo()
    {
        foreach (RemessaNfe::tiposPagamento() as $key => $t) {
            if ($this->tipo_pagamento == $key) return $t;
        }
    }

    public function getTipoPagamento()
    {
        foreach (Venda::tiposPagamento() as $key => $t) {
            if ($this->tipo_pagamento == $key) return $t;
        }
    }
}
