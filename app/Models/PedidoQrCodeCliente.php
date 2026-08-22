<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoQrCodeCliente extends Model
{
    protected $fillable = [
        'pedido_id', 'hash'
    ];

}
