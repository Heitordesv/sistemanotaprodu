<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BairroDeliveryLoja extends Model
{
    use HasFactory;
    protected $fillable = [ 'empresa_id', 'nome', 'valor_entrega' ];
}
