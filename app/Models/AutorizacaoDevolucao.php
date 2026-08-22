<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutorizacaoDevolucao extends Model
{
    protected $table = 'autorizacoes_devolucao_caixa';

    protected $fillable = [
        'empresa_id',
        'venda_caixa_id',
        'usuario_solicitante_id',
        'usuario_solicitante_nome',
        'usuario_autorizador_id',
        'usuario_autorizador_nome',
        'tipo',
        'numero_nfce',
        'valor_venda',
        'motivo',
    ];

    protected $casts = [
        'valor_venda' => 'decimal:2',
    ];

    public function solicitante()
    {
        return $this->belongsTo(Usuario::class, 'usuario_solicitante_id');
    }

    public function autorizador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_autorizador_id');
    }
}