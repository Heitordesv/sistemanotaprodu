<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryConfigGaleria extends Model
{
    use HasFactory;

    protected $fillable = [ 'config_id', 'imagem' ];
}
