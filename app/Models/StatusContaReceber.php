<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusContaReceber extends Model
{
    // Nome da tabela
    protected $table = 'status';

    // Chave primária
    protected $primaryKey = 'id';

    // Desabilita os timestamps (created_at, updated_at)
    public $timestamps = false;

    // Campos preenchíveis
    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'total',
        'linha',
        'qrcode',
        'status',
        'codigo',
        'id_venda',
        'adicionais',
        'cep',
        'logradouro',
        'bairro',
        'numero',
        'cidade',
        'uf',
        'complemento',
        'quantidade',
        'frete',
        'servico',
        'nome_produto',
        'telefone',
        'id_pedido',
        'user_id',
        'venda_id',
        'cliente_id',
    ];

    // Casts de tipo
    protected $casts = [
        'total' => 'float',
        'adicionais' => 'array',
    ];

    /**
     * Retorna o status mais recente com base no venda_id.
     *
     * @param int $vendaId
     * @return string|null
     */
    public static function getStatusByVendaId($vendaId)
    {
        return self::where('venda_id', $vendaId)
                    ->orderByDesc('id') // Usa o ID como referência de ordem
                    ->value('status');
    }
}
