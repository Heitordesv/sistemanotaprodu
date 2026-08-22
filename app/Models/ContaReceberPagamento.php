<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaReceberPagamento extends Model
{
    protected $fillable = [
        'conta_receber_id',
        'empresa_id',
        'valor',
        'forma_pagamento',
        'data_pagamento',
        'origem',
        'provedor',
        'external_id',
        'lote_uuid',
        'status',
        'observacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_pagamento' => 'datetime',
    ];

    public function contaReceber()
    {
        return $this->belongsTo(ContaReceber::class, 'conta_receber_id');
    }
}