<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerPromocionalModel extends Model
{
    protected $table = 'banner_promocional';
    protected $primaryKey = 'id_banner';
    public $timestamps = false;

    protected $fillable = [
        'id_banner',
        'user_id',
        'confirma_banner',
        'img_banner',
    ];
}
