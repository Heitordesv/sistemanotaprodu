<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemLocacao extends Model
{
    use HasFactory;
    protected $fillable = [
		'locacao_id', 'produto_id', 'observacao', 'valor'
	];

	public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
