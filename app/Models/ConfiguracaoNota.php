<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoNota extends Model
{
    use HasFactory;

    // Garanta que esta linha está EXATAMENTE assim:
    protected $table = 'config_notas'; // <-- Sem 's' no final, conforme sua tabela real

    // ... o restante do seu código do modelo ConfiguracaoNota
    protected $fillable = [
        'empresa_id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        // ... todos os outros campos
        'logo',
        'validade_orcamento',
        // ...
    ];

    public function empresa()
    {
        return $this->belongsTo(Link::class, 'empresa_id', 'id');
    }
}