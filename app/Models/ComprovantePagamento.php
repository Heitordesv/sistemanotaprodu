<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComprovantePagamento extends Model
{
    use HasFactory;

    // Nome da tabela (caso não siga a convenção plural)
    protected $table = 'comprovantes_pagamentos';

    // Campos que podem ser preenchidos via mass-assignment
    protected $fillable = [
        'conta_pagar_id',
        'empresa_id',
        'arquivo',
        'tipo_arquivo',
        'data_upload',
        'usuario_id',
        'observacao',
    ];

    // Campos do tipo data
    protected $dates = [
        'data_upload',
        'created_at',
        'updated_at',
    ];

    /**
     * Relacionamento com ContaPagar
     */
    public function contaPagar()
    {
        return $this->belongsTo(ContaPagar::class, 'conta_pagar_id');
    }

    /**
     * Relacionamento com Usuário (quem enviou o comprovante)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Retorna a URL pública do arquivo
     */
    public function getUrlAttribute()
    {
        return $this->arquivo ? asset('storage/' . $this->arquivo) : null;
    }
}
