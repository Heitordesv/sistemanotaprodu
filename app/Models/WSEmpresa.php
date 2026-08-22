<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WSEmpresa extends Model
{
    /**
     * O nome da tabela correspondente no banco de dados.
     *
     * @var string
     */
    protected $table = 'ws_empresa';

    /**
     * A chave primária da tabela.
     *
     * @var string
     */
    protected $primaryKey = 'id_empresa';

    /**
     * Define se o modelo gerencia ou não os campos de timestamps (created_at, updated_at).
     * Caso sua tabela não os tenha, desative-os:
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'linkavali',
    ];
}
