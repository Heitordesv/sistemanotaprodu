<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotacao extends Model
{
    protected $fillable = [
        'forma_pagamento', 'fornecedor_id', 'valor', 'referencia', 
        'resposta', 'ativa', 'observacao', 'link', 'desconto', 'responsavel', 'escolhida', 'empresa_id'
    ];

    public function fornecedor(){
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function itens(){
        return $this->hasMany('App\Models\ItemCotacao', 'cotacao_id', 'id');
    }

    public function contaItens(){
        $c = Cotacao::
        where('referencia', $this->referencia)
        ->first();
        return count($c->itens);
    }

    public function escolhida(){
        $cotacoes = Cotacao::
        where('referencia', $this->referencia)
        ->get();
        foreach($cotacoes as $c){
            if($c->escolhida) return $c;
        }
        return false;
    }

    public function contaFornecedores(){
        $c = Cotacao::
        where('referencia', $this->referencia)
        ->get();
        return count($c);
    }

    public function getValores($max = false){
        $cotacoes = Cotacao::
        where('referencia', $this->referencia)
        ->where('resposta', true)
        ->get();

        if(count($cotacoes) == 0) return 0;

        $valorRetorno = 0;

        if($max == false){
            $valorRetorno = $cotacoes[0]->valor;
        }
        foreach($cotacoes as $c){
            if($max == true){
                if($c->valor > $valorRetorno) $valorRetorno = $c->valor;    
            }else{
                if($c->valor < $valorRetorno) $valorRetorno = $c->valor;    
            }
        }
        return $valorRetorno;
    }


}
