<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BairroDelivery extends Model
{
    use HasFactory;

    // Define o nome da tabela
    protected $table = 'bairros_delivery';

    // Defina as colunas que são atribuíveis em massa
    protected $fillable = [
        'user_id', 
        'uf', 
        'cidade', 
        'bairro', 
        'taxa',
    ];

    // Se você está usando timestamps e os campos 'created_at' e 'updated_at'
    public $timestamps = true;

    // Defina a chave primária, caso o nome seja diferente de 'id'
    protected $primaryKey = 'id';

    // Caso a chave primária não seja auto incremento, defina como false
    public $incrementing = true;

    // Se o tipo da chave primária for diferente de int (por exemplo, string), defina o tipo
    // protected $keyType = 'string';

    // Defina o formato de data
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    // Método para pegar os bairros com a consulta solicitada
    public static function getBairros()
    {
        return self::select('id', 'user_id', 'uf', 'cidade', 'bairro', 'taxa', 'created_at', 'updated_at')->get();
    }
}
