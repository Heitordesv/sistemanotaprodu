<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motoboy extends Model
{
    protected $table = 'ws_motoboys'; 

    protected $fillable = [
        'deliveryman_name', 
        'deliveryman_phone_number',
        'user_id'
        
    ];

    public static function tiposTransporte()
    {
        return [
            'Baú',
            'Mochila',
            'Caixa',
            'Outro'
        ];
    }
}
