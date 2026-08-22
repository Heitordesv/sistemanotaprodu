<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventoSalario extends Model
{
    use HasFactory;

    use HasFactory;
    protected $fillable = [
        'nome', 'tipo', 'metodo', 'condicao', 'ativo', 'empresa_id', 'tipo_valor'
    ];
      // Relacionamento com os eventos da apuração (opcional)
    public function apuracoes()
    {
        return $this->hasMany(ApuracaoSalarioEvento::class, 'evento_id');
    }
    
}
