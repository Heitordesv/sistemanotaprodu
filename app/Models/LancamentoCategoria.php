<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LancamentoCategoria extends Model
{
    protected $fillable = [
        'categoria_id', 'nome', 'valor', 'percentual'
    ];

    public function categoria(){
        return $this->belongsTo(DreCategoria::class, 'categoria_id');
    }

}
