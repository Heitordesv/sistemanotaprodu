<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BairroDelivery;

class EnderecoDelivery extends Model
{
	protected $fillable = [
		'cliente_id', 'rua', 'numero', 'bairro', 'bairro_id', 'referencia', 'latitude', 'longitude',
        'cidade_id', 'tipo', 'cep', 'padrao'
	];

	public function cliente(){
        return $this->belongsTo(ClienteDelivery::class, 'cliente_id');
    }

	public function cidade(){
        return $this->belongsTo(CidadeDelivery::class, 'cidade_id');
    }

    public function _bairro(){
        return $this->belongsTo(BairroDeliveryLoja::class, 'bairro_id');
    }
   
}
