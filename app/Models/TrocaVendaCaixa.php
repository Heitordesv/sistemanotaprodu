<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrocaVendaCaixa extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'antiga_venda_caixas_id', 'nova_venda_caixas_id',
        'prod_removidos', 'prod_adicionados', 'observacao'
    ];

    // public function antigaVenda(){
    //  return $this->hasOne('App\Models\VendaCaixa', 'troca_venda_caixas_id', 'id');
    // }

    public static function filtroData($dataInicial, $dataFinal){
        $value = session('user_logged');
        $empresa_id = $value['empresa'];

        return TrocaVendaCaixa::
        orderBy('id', 'desc')
        ->whereBetween('created_at', [$dataInicial, 
            $dataFinal])
        ->where('empresa_id', $empresa_id)
        ->get();
    }
}
