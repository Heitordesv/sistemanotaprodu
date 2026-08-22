<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CobrancaAgentConfig extends Model
{
    protected $fillable = [
        'empresa_id',
        'nome_agente',
        'ativo',
        'os_notificacoes',
        'responder_clientes',
        'hora_envio',
        'dias_antes',
        'dias_atraso',
        'ultima_execucao_em',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'os_notificacoes' => 'boolean',
        'responder_clientes' => 'boolean',
        'dias_antes' => 'array',
        'dias_atraso' => 'array',
        'ultima_execucao_em' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}