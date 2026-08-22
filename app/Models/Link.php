<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $table = 'empresas'; // Seu modelo Link está mapeado para a tabela 'empresas'

    protected $fillable = [
        // ... (seus campos fillable existentes)
        'razao_social',
        'nome_fantasia',
        'nome_link',
        'rua',
        'telefone',
        'email',
        'numero',
        'bairro',
        'cidade_id',
        'cpf_cnpj',
        'hash',
        'permissao',
        'status',
        'tipo_representante',
        'tipo_contador',
        'perfil_id',
        'mensagem_bloqueio',
        'info_contador',
        'contador_id',
        'cep',
        'representante_legal',
        'cpf_representante_legal',
    ];

    /**
     * Define o relacionamento com o modelo ConfiguracaoNota.
     * Uma Empresa (Link) pode ter uma Configuração de Nota.
     */
    public function configuracaoNota()
    {
        // 'empresa_id' é a chave estrangeira na tabela 'config_notas' (do modelo ConfiguracaoNota)
        // 'id' é a chave primária da tabela 'empresas' (do próprio modelo Link)
        return $this->hasOne(ConfiguracaoNota::class, 'empresa_id', 'id');
    }

    // Removido: O relacionamento 'cidade' foi removido conforme sua solicitação anterior.
    // public function cidade() { ... }
}