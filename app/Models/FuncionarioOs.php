<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuncionarioOs extends Model
{
    protected $fillable = [
        'usuario_id', 'funcionario_id', 'ordem_servico_id', 'funcao'
    ];

    public function ordemServico(){
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function funcionario(){
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }
}
