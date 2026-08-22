<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = [
        'nome', 'descricao', 'logradouro', 'numero', 'bairro', 'cidade', 'status',
        'inicio', 'fim', 'empresa_id'
    ];

    public function funcionarios(){
        return $this->hasMany('App\Models\EventoFuncionario', 'evento_id', 'id');
    }

    public function atividades(){
        return $this->hasMany('App\Models\AtividadeEvento', 'evento_id', 'id');
    }

    public static function tiposPagamento(){
        return [
            'Dinheiro',
            'Cartão de Crédito',
            'Cartão de Débito',
        ];
    }
}
