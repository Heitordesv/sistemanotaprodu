<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    // Define explicitamente a tabela associada ao model
    protected $table = 'leads';

    /**
     * Como você usa 'data_cadastro' em vez de 'created_at'/'updated_at',
     * mantemos false, mas o ideal seria usar o padrão do Laravel no futuro.
     */
    public $timestamps = false;

    /**
     * Atributos que podem ser preenchidos em massa.
     * Adicionei 'empresa', 'cnpj', 'cidade' e 'uf' que o Controller está enviando.
     */
   protected $fillable = [
        'id_vendedor',
        'nome_completo',
        'whatsapp',
        'email',
        'empresa',
        'cnpj',
        'cidade',
        'uf',
        'tipo_loja',
        'data_cadastro',
        'ip_origem',
        'status',
        'origem_lead',
    ];

    /**
     * Relacionamento com o Usuário (Vendedor)
     */
 public function vendedor()
{
    // Apontando para o seu model Usuario, usando o id_vendedor como chave
    return $this->belongsTo(\App\Models\Usuario::class, 'id_vendedor', 'id');
}
    /**
     * Mapeamento de tipos de atributos.
     */
    protected $casts = [
        'data_cadastro' => 'datetime',
        // Removido o cast de integer para status, pois usamos strings ('Novo', 'Convertido')
    ];

    /**
     * Relacionamento com as observações.
     */
    public function observacoes(): HasMany
    {
        return $this->hasMany(LeadObservacao::class, 'lead_id', 'id');
    }

    /**
     * Helper para formatar o WhatsApp para exibição se necessário
     */
    public function getWhatsappFormatadoAttribute()
    {
        // Exemplo simples: (99) 99999-9999
        $tel = $this->whatsapp;
        if (strlen($tel) == 13) { // Com 55
            return "+" . substr($tel, 0, 2) . " (" . substr($tel, 2, 2) . ") " . substr($tel, 4, 5) . "-" . substr($tel, 9);
        }
        return $tel;
    }
}