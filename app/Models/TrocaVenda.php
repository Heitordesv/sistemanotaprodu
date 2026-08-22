<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrocaVenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id', 'tipo', 'valor_total', 'valor_credito', 'empresa_id', 'cliente_id', 'usuario_id',
        'data_venda', 'status'
    ];

    public function venda(){
        if($this->tipo == 'pedido'){
            return Venda::find($this->venda_id);
        }else{
            return VendaCaixa::find($this->venda_id);
        }
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function itens(){
        return $this->hasMany(TrocaVendaItem::class, 'troca_id');
    }
}
