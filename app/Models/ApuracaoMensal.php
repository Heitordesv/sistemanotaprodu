<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApuracaoMensal extends Model
{
    use HasFactory;

    protected $fillable = [
        'funcionario_id', 'mes', 'ano', 'valor_final', 'forma_pagamento', 'observacao', 
        'conta_pagar_id'
    ];

    public function funcionario(){
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

     public function eventos()
    {
        return $this->hasMany(ApuracaoSalarioEvento::class, 'apuracao_id');
    }

    public static function tiposPagamento(){
        return [
            'Dinheiro',
            'Cheque',
            'Boleto',
            'Depósito Bancário',
            'Pix',
            'Outros'
        ];
    }

    public static function mesesApuracao(){
        return [
            'janeiro',
            'fevereiro',
            'março',
            'abril',
            'maio',
            'junho',
            'julho',
            'agosto',
            'setembro',
            'outubro',
            'novembro',
            'dezembro',
        ];
    }

    public static function anosApuracao(){
        $anos = [];
        $a = date('Y');
        for($i=$a; $i<$a+20; $i++){
            array_push($anos, $i);
        }
        return $anos;
    }
}
