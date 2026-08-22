<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WsDatasClose extends Model
{
    use HasFactory;

    protected $table = 'ws_datas_close';

    protected $fillable = [
        'user_id',
        'data',
    ];

    /**
     * Define se a tabela utiliza os campos de timestamps (created_at e updated_at)
     */
    public $timestamps = true;

    /**
     * Converte automaticamente o campo 'data' para uma instância do Carbon
     */
    protected $dates = ['data'];
}
