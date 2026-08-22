<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemAgendamento extends Model
{
    protected $fillable = [
        'agendamento_id', 'servico_id', 'quantidade'
    ];

    public function servico(){
        return $this->belongsTo(Servico::class, 'servico_id');
    }

}
