<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoAjuda extends Model
{
    use HasFactory;

    protected $fillable = [
        'url_sistema', 'url_video'
    ];
}
