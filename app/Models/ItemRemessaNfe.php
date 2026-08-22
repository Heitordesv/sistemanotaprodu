<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemRemessaNfe extends Model
{
    use HasFactory;

    protected $fillable = [
        'remessa_id', 'produto_id', 'quantidade', 'valor_unitario', 'sub_total',
        'cst_csosn', 'cst_pis', 'cst_cofins', 'cst_ipi', 'perc_icms', 'perc_pis',
        'perc_cofins', 'perc_ipi', 'pRedBC', 'vbc_icms', 'vbc_pis', 'vbc_cofins',
        'vbc_ipi', 'vBCSTRet', 'vFrete', 'modBCST', 'vBCST', 'pICMSST', 'vICMSST',
        'pMVAST', 'x_pedido', 'num_item_pedido', 'cest', 'valor_icms', 'valor_pis', 
        'valor_cofins', 'valor_ipi', 'cfop'
    ];

    public function produto(){
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function venda(){
        return $this->belongsTo(RemessaNfe::class, 'remessa_id');
    }

    public function percentualUf($uf){
        $tributacao = TributacaoUf
        ::where('uf', $uf)
        ->where('produto_id', $this->produto_id)
        ->first();

        return $tributacao;
    }

}
