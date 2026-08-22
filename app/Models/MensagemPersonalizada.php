<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MensagemPersonalizada extends Model
{
    use HasFactory;

    protected $table = 'mensagens_personalizadas';

    protected $fillable = [
        'id',
        'user_id',
        'mensagem',
        'status',
        'tipo',
    ];

    // Se quiser tratar os campos de data automaticamente:
    protected $dates = ['created_at', 'updated_at'];
}
