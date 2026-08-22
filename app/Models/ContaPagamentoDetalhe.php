<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaPagamentoDetalhe extends Model
{
    use HasFactory;

    // Tabela vinculada
    protected $table = 'conta_pagamento_detalhes';

    // Campos que podem ser preenchidos em mass assignment
    protected $fillable = [
        'conta_pagar_id',
        'tipo_pagamento',
        'boleto_pdf',
        'boleto_codigo',
        'pix_chave',
        'dados_bancarios',
    ];

    /**
     * Relacionamento com a conta a pagar
     */
    public function contaPagar()
    {
        return $this->belongsTo(ContaPagar::class, 'conta_pagar_id');
    }
}
