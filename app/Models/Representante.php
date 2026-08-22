<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Representante extends Model
{
  use HasFactory;

  protected $fillable = [
    'nome', 'rua', 'telefone', 'email', 'numero', 'bairro', 'cidade_id', 'cpf_cnpj',
    'usuario_id', 'status', 'comissao', 'acesso_xml', 'limite_cadastros'
  ];

  public function usuario()
  {
    return $this->belongsTo(Usuario::class, 'usuario_id');
  }

  public function cidade()
  {
    return $this->belongsTo(Cidade::class, 'cidade_id');
  }

  public function empresas()
  {
    return $this->hasMany(RepresentanteEmpresa::class, 'representante_id', 'id');
  }
}
