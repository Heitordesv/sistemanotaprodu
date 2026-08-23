<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdvDevolucao extends Model
{
    protected $table = 'pdv_devolucoes';

    protected $fillable = [
        'empresa_id',
        'venda_caixa_id',
        'tipo',
        'status',
        'usuario_solicitante_id',
        'usuario_solicitante_nome',
        'usuario_autorizador_id',
        'usuario_autorizador_nome',
        'motivo',
        'valor_venda',
        'filial_id',
        'estoque_filial_id',
        'abertura_caixa_original_id',
        'abertura_caixa_compensacao_id',
        'valor_reembolso_dinheiro',
        'sefaz_cstat',
        'sefaz_protocolo',
        'sefaz_mensagem',
        'financeiro_json',
        'sefaz_cancelada_em',
        'estoque_processado_em',
        'financeiro_processado_em',
        'concluida_em',
    ];

    protected $casts = [
        'valor_venda' => 'decimal:2',
        'valor_reembolso_dinheiro' => 'decimal:2',
        'sefaz_cancelada_em' => 'datetime',
        'estoque_processado_em' => 'datetime',
        'financeiro_processado_em' => 'datetime',
        'concluida_em' => 'datetime',
    ];

    public function venda()
    {
        return $this->belongsTo(VendaCaixa::class, 'venda_caixa_id');
    }
}
