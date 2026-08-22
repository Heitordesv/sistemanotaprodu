<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioAcesso extends Model
{
    protected $table = 'usuario_acessos';

    protected $fillable = [
        'usuario_id',
        'status',
        'hash',
        'ip_address',
        'cidade',
        'estado',
        'pais',
    ];

    protected $casts = [
        'status' => 'integer',
        'usuario_id' => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
