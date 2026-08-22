<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WsAdicionalItemGratis extends Model
{
    protected $table = 'ws_adicionais_itens_gratis';

    protected $primaryKey = 'id_adicional_gratis';

    public $timestamps = false;

    protected $fillable = [
        'nome_adicional_gratis',
        'categorias_adicional_gratis',
        'user_id',
        'status_adicional_gratis',
        'id_adicionais_cat'
    ];

    // Adiciona um accessor para retornar "id" junto com os atributos
    protected $appends = ['id'];

    public function getIdAttribute()
    {
        return $this->attributes['id_adicional_gratis'];
    }

    // Relacionamento com a tabela de categorias
    // public function categoria()
    // {
    //     return $this->belongsTo(WsAdicionaisCat::class, 'id_adicionais_cat', 'id');
    // }

    public function categoria()
    {
        return $this->belongsTo(Cat_ws::class, 'categorias_adicional_gratis', 'id');
    }
}