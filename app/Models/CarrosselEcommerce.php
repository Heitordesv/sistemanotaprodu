<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarrosselEcommerce extends Model
{
	protected $fillable = [
		'empresa_id', 'titulo', 'descricao', 'img', 'link_acao', 'nome_botao', 'cor_titulo',
		'cor_descricao'
	];

    public function getImageAttribute(){
        return "/uploads/carrosselEcommerce/$this->img";
    }
}
