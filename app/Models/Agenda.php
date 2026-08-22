<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    protected $table = 'agendas';

    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'servico_id',
        'data',
        'hora',
        'duracao',
        'status',
        'observacao',
    ];

    /**
     * Relacionamento com Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relacionamento com Usuário (funcionário)
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Relacionamento com Serviço
     */
    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    /**
     * Escopo para agendamentos por data
     */
    public function scopePorData($query, $data)
    {
        return $query->where('data', $data);
    }

    /**
     * Escopo para agendamentos de um funcionário específico
     */
    public function scopePorFuncionario($query, $usuario_id)
    {
        return $query->where('usuario_id', $usuario_id);
    }

    /**
     * Escopo para agendamentos por status
     */
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
