<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanoEmpresaRepresentante extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'plano_id', 'expiracao', 'representante_id'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    public function representante()
    {
        return $this->belongsTo(Representante::class, 'plano_id');
    }
}
