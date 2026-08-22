<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdicionaisCat extends Model
{
    use HasFactory;

    protected $table = 'ws_adicionais_cat';

    protected $fillable = [
        'nome_adicional_cat', 
        'descricao_adicional_cat', 
        'status_adicional_cat',
        'id_itens', 
        'user_id', 
        'id_cat', 
        'name_adicionais_cat', 
        'amount', 
        'pay', 
        'img_cat'
    ];

    public $timestamps = true;

    /**
     * Método para listar as condições adicionais.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function listarCondicoesAdicionais()
    {
        return self::select('id', 'id_itens', 'user_id', 'id_cat', 'name_adicionais_cat', 'amount', 'pay', 'img_cat')
            ->get();
    }
}
