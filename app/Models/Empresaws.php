<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresaws extends Model
{
    protected $table = 'ws_empresa';
    protected $primaryKey = 'id_empresa';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'nome_empresa',
        'descricao_empresa',
        'nome_empresa_link',
        'cidade_empresa',
        'telefone_empresa',
        'email_empresa',
        'empresa_data_renovacao',
        'img_logo',
    ];
}
