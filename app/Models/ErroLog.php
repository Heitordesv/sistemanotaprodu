<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErroLog extends Model
{
    use HasFactory;

    protected $table = 'erro_logs';

    protected $fillable = [
        'arquivo',
        'linha',
        'erro',
        'empresa_id',
        'level',
        'level_name',
        'message',
        'file',
        'line',
        'trace',
        'context',
        'extra',
        'formatted'
    ];

    protected $casts = [
        'context' => 'array',
        'extra'   => 'array',
        'trace'   => 'array',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
