<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaLojaSegmento extends Model
{
    use HasFactory;
    protected $fillable = [ 'categoria_id', 'loja_id' ];

    public function categoria(){
        return $this->belongsTo(CategoriaMasterDelivery::class, 'categoria_id');
    }
}
