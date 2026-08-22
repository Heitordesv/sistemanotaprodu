<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DestaqueDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'img', 'status', 'empresa_id', 'produto_id', 'ordem'
    ];

    protected $appends = [
        'path'
    ];

    public function getPathAttribute()
    {
        if($this->img == null){
            return "/imagens/sem-imagem.png";
        }
        return env("PATH_URL") . "/destaques_delivery/" . $this->img;
    }

    public function empresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function produto(){
        return $this->belongsTo(ProdutoDelivery::class, 'produto_id');
    }
}
