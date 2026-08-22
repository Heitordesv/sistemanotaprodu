<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrosselDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'path', 'status', 'valor_ordem'
    ];

    protected $appends = [
        'img'
    ];

    public function getImgAttribute()
    {
        return env("PATH_URL") . "/carrossel_delivery/" . $this->path;
    }
}
