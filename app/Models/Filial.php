<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filial extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'status', 'razao_social', 'nome_fantasia', 'cnpj', 'ie', 'logradouro', 'numero', 'bairro', 
        'cidade_id','codMun','pais','codPais','fone','cep','UF', 'complemento', 'nat_op_padrao', 
        'ambiente', 'cUF', 'ultimo_numero_nfe', 'ultimo_numero_nfce', 'ultimo_numero_cte', 
        'ultimo_numero_mdfe', 'numero_serie_nfe', 'numero_serie_nfce', 'numero_serie_cte', 
        'csc', 'csc_id', 'inscricao_municipal', 'aut_xml', 'logo', 'arquivo', 
        'senha', 'descricao', 'empresa_id', 'numero_serie_mdfe', 'email'
    ];

    public function natureza(){
        return $this->belongsTo(NaturezaOperacao::class, 'nat_op_padrao');
    }

    public function cidade(){
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }
}
