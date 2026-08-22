<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteDelivery extends Model
{
    protected $fillable = [
		'nome', 'sobre_nome', 'celular', 'email', 'token', 'ativo', 'senha', 'empresa_id',
        'cpf', 'foto', 'uid'
	];

	protected $hidden = [
		'senha' 
	];
    
    public function getImgAttribute()
    {
        if($this->foto == ""){
            return "";
        }
        return env("PATH_URL") . "/fotos_cliente_delivery/" . $this->foto;
    }

	public function enderecos(){
        return $this->hasMany(EnderecoDelivery::class, 'cliente_id', 'id');
    }

    public function pedidos(){
        return $this->hasMany(PedidoDelivery::class, 'cliente_id', 'id');
    }

    public function favoritos(){
        return $this->hasMany('App\Models\ProdutoFavoritoDelivery', 'cliente_id', 'id');
    }

    public function tokens(){
        return $this->hasMany('App\Models\TokenClienteDelivery', 'cliente_id', 'id');
    }

    public function tokensWeb(){
        return $this->hasMany('App\Models\TokenWeb', 'cliente_id', 'id');
    }

}
