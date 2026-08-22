<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cat_ws extends Model
{
    use HasFactory;

    // Defina o nome da tabela, caso não seja o plural do nome do modelo
    protected $table = 'ws_cat';

    // Defina os campos que podem ser preenchidos (atributos mass assignable)
    protected $fillable = [
       'user_id',
        'nome_cat',
        'desc_cat',
        'icon_cat',
        'dias_semana',
        'hora_abertura',  // Adicionando hora de abertura
        'hora_fechamento', // Adicionando hora de fechamento
        'ord',

    ];

    /**
     * Relacionamento com o usuário (caso haja relação com o modelo User)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Método para buscar categorias com base no user_id
     *
     * @param  int  $user_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function buscarCategoriasPorUsuario(int $user_id)
    {
        // Retorna as categorias ordenadas pelo nome ou de acordo com sua necessidade
        return self::where('user_id', $user_id)
            ->orderBy('nome_cat') // Exemplo de ordenação pelo nome
            ->get(); // ou ->paginate(10) para paginação
    }
}
