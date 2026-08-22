<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UsuarioOnline extends Model
{
    use HasFactory;

    protected $table = 'usuarios_online'; // Nome da tabela no banco
    protected $primaryKey = 'id'; // Chave primária, caso diferente de 'id'

    // Definindo os campos que podem ser preenchidos em massa
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'url',
        'ultima_atividade',
    ];

    // Se o nome das colunas no banco não seguir o padrão timestamp (created_at, updated_at)
    public $timestamps = false;  // Desabilita o gerenciamento automático de created_at e updated_at

    // Tratamento da coluna 'ultima_atividade' para o tipo Carbon
    protected $casts = [
        'ultima_atividade' => 'datetime',
    ];

    /**
     * Relacionamento com o modelo User (assumindo que a tabela 'users' existe).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
