<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadObservacao extends Model
{
    protected $table = 'lead_observacoes';
    protected $primaryKey = 'obs_id';
    public $timestamps = false;

    protected $fillable = [
        'lead_id',
        'observacao',
        'id_vendedor',
        'usuario_responsavel',
        'data_observacao',
    ];

    protected $casts = [
        'data_observacao' => 'datetime',
    ];

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_vendedor', 'id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'id');
    }
}