<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlteracaoEstoque extends Model
{
    protected $fillable = [
		 'produto_id', 'usuario_id', 'quantidade', 'tipo', 'observacao', 'empresa_id'
	];

	public function produto(){
		return $this->belongsTo(Produto::class, 'produto_id');
	}

	public function usuario(){
		return $this->belongsTo(Usuario::class, 'usuario_id');
	}

    public function apontamento(){
		return $this->hasMany('App\Models\Apontamento', 'produto_id', 'id');
    }
}
