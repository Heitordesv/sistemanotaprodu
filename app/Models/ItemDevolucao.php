<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemDevolucao extends Model
{
	protected $fillable = [
		'cod', 'nome', 'ncm', 'cfop', 'valor_unit', 'quantidade', 'item_parcial', 
		'devolucao_id', 'codBarras', 'unidade_medida', 'cst_csosn', 'cst_pis', 'cst_cofins', 
		'cst_ipi', 'perc_icms', 'perc_pis', 'perc_cofins', 'perc_ipi', 'pRedBC', 'vBCSTRet', 
		'vFrete', 'modBCST', 'vBCST', 'pICMSST', 'vICMSST', 'pMVAST', 'orig', 'pST', 'vICMSSubstituto',
		'vICMSSTRet', 'perc_glp', 'perc_gnn', 'perc_gni', 'codigo_anp', 'descricao_anp', 'uf_cons', 
		'valor_partida', 'unidade_tributavel', 'quantidade_tributavel', 'CEST', 'vDesc'
	];
}


