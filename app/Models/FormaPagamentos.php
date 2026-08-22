<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPagamentos extends Model
{
    protected $table = 'ws_formas_pagamento';
    protected $primaryKey = 'id_f_pagamento';

    protected $fillable = [
        'id_f_pagamento',
        'user_id',
        'f_pagamento',
    ];

    public $timestamps = false;

    // Escopo para buscar por usuário
    public function scopeDoUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
