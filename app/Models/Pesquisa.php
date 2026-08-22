<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesquisa extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo', 'texto', 'status', 'maximo_acessos'
    ]; 

    public function respostas(){
        return $this->hasMany(PesquisaResposta::class, 'pesquisa_id');
    }
}
