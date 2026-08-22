<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Adicionando o uso do HasFactory

class Recorrencia extends Model
{
    use HasFactory; // Agora o trait está sendo utilizado corretamente

    protected $fillable = [
        'conta_receber_id',
        'vencimento',
        'valor',
        'status',
    ];

    protected $dates = [
        'vencimento', // Para garantir que a data de vencimento seja tratada corretamente
    ];

    // Relacionamento com a conta_receber
    public function contaReceber()
    {
        return $this->belongsTo(ContaReceber::class);
    }
    public function recorrencias()
{
    return $this->hasMany(Recorrencia::class);
}

}
