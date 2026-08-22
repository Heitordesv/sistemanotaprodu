<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WsAdicionalItem extends Model
{
    protected $table = 'ws_adicionais_itens';

    protected $primaryKey = 'id_adicionais';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'categorias_adicional',
        'nome_adicional',
        'valor_adicional',
        'medida_adicional',
        'status_adicional',
        'id_adicionais_cat'
    ];

    // Adiciona um accessor para retornar "id" junto com os atributos
    protected $appends = ['id'];

    public function getIdAttribute()
    {
        return $this->attributes['id_adicionais'];
    }

    // Relacionamento com a tabela de categorias (ajuste conforme sua estrutura)
    public function categoria()
    {
        return $this->belongsTo(Cat_ws::class, 'categorias_adicional', 'id');
    }
}
