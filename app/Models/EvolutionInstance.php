<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvolutionInstance extends Model
{
    protected $fillable = [
        'empresa_id', 'base_url', 'api_key', 'webhook_secret', 'instance_name',
        'integration', 'status', 'phone', 'active', 'last_connected_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_connected_at' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}