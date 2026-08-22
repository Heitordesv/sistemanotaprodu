<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo', 'texto', 'prioridade', 'status'
    ];

    public static function prioridades(){
        return ['baixa', 'media', 'alta'];
    }

    public function views(){
        return $this->hasMany(AvisoAcesso::class, 'aviso_id');
    }

    public function getColor(){
        if($this->prioridade == 'baixa') return 'primary';
        elseif($this->prioridade == 'media') return 'warning';
        else return 'danger';
    }
}
