<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Importação do modelo EventoSalario, se não estiver no mesmo namespace
// use App\Models\EventoSalario; 

class FuncionarioEvento extends Model
{
    use HasFactory;

    protected $fillable = [
        'evento_id', 'funcionario_id', 'condicao', 'metodo', 'valor', 'ativo'
    ];

    /**
     * Relação original (mantida por segurança).
     */
    public function evento()
    {
        return $this->belongsTo(EventoSalario::class, 'evento_id');
    }

    /**
     * Relação com o funcionário.
     */
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    /**
     * Relação corrigida para uso no Controller (com with('eventoSalario')).
     * O Return Type Hinting (BelongsTo) e as importações estão agora corretos.
     * * @return BelongsTo
     */
    public function eventoSalario(): BelongsTo
    {
        // Usa a chave 'evento_id' para ligar ao modelo EventoSalario
        return $this->belongsTo(EventoSalario::class, 'evento_id');
    }
}