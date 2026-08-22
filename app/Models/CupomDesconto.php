<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CupomDesconto extends Model
{
    protected $table = 'cupom_desconto';
    protected $primaryKey = 'id_cupom';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'ativacao', 'porcentagem', 'total_vezes', 'mostrar_site', 'data_validade', 'vip'
    ];
}
