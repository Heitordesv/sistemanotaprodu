<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frete extends Model
{
    protected $fillable = [
        'valor', 'placa', 'tipo', 'uf', 'numeracaoVolumes', 'peso_liquido', 'peso_bruto',
        'especie', 'qtdVolumes'
    ];

    public static function tipos(){
        return [
            '9' => 'Sem Frete',
            '0' => 'Emitente',
            '1' => 'Destinatário',
            '2' => 'Terceiros',
            '3' => 'Próprio por conta do remetente',
            '4' => 'Próprio por conta do destinatário'
        ];
    }
}
