<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatorioOs extends Model
{
    protected $fillable = [
        'usuario_id', 'texto', 'ordem_servico_id'
    ];

    public function ordemServico(){
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
