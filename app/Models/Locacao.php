<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locacao extends Model
{
    use HasFactory;
    protected $fillable = [
		'empresa_id', 'cliente_id', 'inicio', 'fim', 'total', 'observacao', 'status'
	];

	public function itens(){
        return $this->hasMany('App\Models\ItemLocacao', 'locacao_id', 'id');
    }

     public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
