<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusPagamento extends Model
{
    protected $table = 'status'; // Nome da tabela no banco de dados

    protected $fillable = [
        'nome', 'cpf', 'email', 'total', 'linha', 'qrcode', 'status', 'codigo', 'id_venda',
        'adicionais', 'cep', 'logradouro', 'bairro', 'numero', 'cidade', 'uf', 'complemento',
        'quantidade', 'frete', 'servico', 'nome_produto', 'telefone', 'venda_id', 'user_id', 'id_pedido'
    ];
}
