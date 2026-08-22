<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscritorioContabil extends Model
{
    protected $fillable = [
        'razao_social', 'nome_fantasia', 'cnpj', 'ie', 'logradouro',
        'numero', 'bairro', 'fone', 'email', 'cep', 'empresa_id', 'token_sieg', 
        'envio_automatico_xml_contador', 'cidade_id'
    ];
}
