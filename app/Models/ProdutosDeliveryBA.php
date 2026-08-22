<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutosDeliveryBA extends Model
{
    protected $table = 'ws_itens'; // Nome da tabela

    protected $primaryKey = 'id'; // Chave primária

    public $timestamps = false; // Se a tabela não tiver created_at/updated_at

    protected $fillable = [
        'user_id',
        'id_cat',
        'img_item',
        'nome_item',
        'descricao_item',
        'preco_item',
        'config_total_s',
        'disponivel',
        'dia_semana',
        'number_adicional',
        'number_adicional_pago',
        'posicao',
        'qd',
    ];

    // Relacionamentos podem ser definidos aqui, por exemplo:
    // public function categoria() {
    //     return $this->belongsTo(WsCategoria::class, 'id_cat');
    // }
}
