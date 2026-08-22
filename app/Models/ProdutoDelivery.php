<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ItemPedidoDelivery;

class ProdutoDelivery extends Model
{
	protected $fillable = [
		'categoria_id', 'produto_id', 'valor', 'descricao',
		'ingredientes', 'status', 'destaque', 'limite_diario', 'valor_anterior', 'empresa_id',
		'tem_adicionais', 'tipo', 'referencia', 'descricao_curta'
	];

	protected $appends = [
		'img'
	];

	public function getImgAttribute()
	{
		if(sizeof($this->galeria) == 0){
			return "";
		}
		return env("PATH_URL") . "/uploads/produtoDelivery/" . $this->galeria[0]->path;
	}

	public function produto(){
		return $this->belongsTo(Produto::class, 'produto_id');
	}

	public function categoria(){
		return $this->belongsTo(CategoriaProdutoDelivery::class, 'categoria_id');
	}

	public function galeria(){
		return $this->hasMany(ImagensProdutoDelivery::class, 'produto_id', 'id');
	}

	public function pizza(){
		return $this->hasMany(ProdutoPizza::class, 'produto_id', 'id');
	}

	public function itemPedido(){
		$dataInicial = date('Y-m-d', strtotime(date('Y-m-d')));
		$dataFinal = date('Y-m-d', strtotime("+1 day",strtotime(date('Y-m-d'))));
		$itensHoje = ItemPedidoDelivery::
		where('produto_id', $this->id)
		->whereBetween('created_at', [$dataInicial, 
			$dataFinal])
		->get();


		return count($itensHoje) >= $this->limite_diario ? false: true;
	}
}
