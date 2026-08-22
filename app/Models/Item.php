<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // Definindo a tabela que será utilizada
    protected $table = 'ws_itens';

    // Atributos que podem ser atribuídos em massa
    protected $fillable = [
        'user_id',
        'id_cat',  // Certifique-se de que esse campo é realmente o ID da categoria
        'img_item',
        'nome_item',
        'descricao_item',
        'preco_item',
        'config_total_s',
        'disponivel',
        'dia_semana',  // Certifique-se de que este campo está na lista de atributos preenchíveis
        'number_adicional',
        'number_adicional_pago',
        'posicao',
        'qd',
    ];

    // Definindo o tipo dos campos (casts) para garantir que os dados sejam corretamente tratados
    protected $casts = [
        'preco_item' => 'float',
        'config_total_s' => 'integer',
        'disponivel' => 'boolean',
        'number_adicional' => 'integer',
        'number_adicional_pago' => 'float',
        'posicao' => 'integer',
        'qd' => 'integer',
        'dia_semana' => 'array', // Convertendo o campo dia_semana para um array automaticamente
    ];

    /**
     * Relacionamento com a tabela de categorias.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoria()
    {
        // Definindo o relacionamento de muitos para um (BelongsTo)
        return $this->belongsTo(Categoria::class, 'id_cat');  // 'id_cat' é a chave estrangeira
    }
}