<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WsAdicionaisCat extends Model
{
    use HasFactory;

    // Nome da tabela
    protected $table = 'ws_adicionais_cat';

    // Atributos que podem ser preenchidos (para mass assignment)
    protected $fillable = [
        'id_itens',
        'user_id',
        'id_cat',
        'name_adicionais_cat',
        'amount',
        'pay',
        'img_cat',
    ];

    // Definir a chave primária, caso seja diferente de "id"
    protected $primaryKey = 'id';

    // Caso o banco de dados não use timestamps (created_at, updated_at), defina como falso
    public $timestamps = false;

    // Você pode adicionar relacionamentos aqui, caso necessário

    public function Item()
    {
        // Definindo o relacionamento de muitos para um (BelongsTo)
        return $this->hasOne(Item::class, 'id', 'id_itens');  // 'id_cat' é a chave estrangeira
    }

   public function categoria()
{
    return $this->hasOne(Categoria::class, 'id', 'id_cat');
}

}