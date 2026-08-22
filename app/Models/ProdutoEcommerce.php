<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoEcommerce extends Model
{
	protected $fillable = [
		'produto_id', 'categoria_id', 'empresa_id', 'descricao', 'controlar_estoque', 'status',
		'valor', 'destaque', 'cep', 'percentual_desconto_view', 'sub_categoria_id'
	];

	protected $appends = [
		'img'
	];

	public function getImgAttribute()
	{
		if(sizeof($this->galeria) == 0){
			return "";
		}
		return env("PATH_URL") . "/uploads/produtoEcommerce/" . $this->galeria[0]->path;
	}

	public function produto(){
		return $this->belongsTo(Produto::class, 'produto_id');
	}

	public function categoria(){
		return $this->belongsTo(CategoriaProdutoEcommerce::class, 'categoria_id');
	}

	public function subCategoria(){
		return $this->belongsTo(SubCategoriaEcommerce::class, 'sub_categoria_id');
	}

	public function galeria(){
		return $this->hasMany(ImagemProdutoEcommerce::class, 'produto_id', 'id');
	}
// Se você quer manter o nome 'produtoFiscal' no Controller:
public function produtoFiscal()
{
    // Ajuste o nome do Model 'Produto' para o seu model de produtos principal
    return $this->belongsTo(\App\Models\Produto::class, 'produto_id');
}
	public function isNovo(){
		$strCadastro = strtotime($this->created_at);
		$strHoje = strtotime(date('Y-m-d H:i:s'));
		$dif = $strHoje - $strCadastro;
		$dif = $dif/24/60/60;
		if($dif < 7) return 1;
		else return 0;
	}

}
