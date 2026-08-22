<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devolucao extends Model
{
    protected $fillable = [
        'fornecedor_id', 'usuario_id', 'natureza_id', 'data_registro', 'valor_integral',
        'valor_devolvido', 'motivo', 'observacao', 'estado_emissao', 'devolucao_parcial',
        'chave_nf_entrada', 'nNf', 'vFrete', 'vDesc', 'chave_gerada', 'numero_gerado',
        'empresa_id', 'tipo', 'transportadora_nome', 'transportadora_cidade',
        'transportadora_uf', 'transportadora_cpf_cnpj', 'transportadora_ie',
        'transportadora_endereco', 'frete_quantidade', 'frete_especie', 'frete_marca',
        'frete_numero', 'frete_tipo', 'veiculo_placa', 'veiculo_uf', 'frete_peso_bruto',
        'frete_peso_liquido', 'despesa_acessorias', 'transportadora_id', 'sequencia_cce'
    ];

    public function transportadora()
    {
        return $this->belongsTo(Transportadora::class, 'transportadora_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function natureza()
    {
        return $this->belongsTo(NaturezaOperacao::class, 'natureza_id');
    }

    public function itens()
    {
        return $this->hasMany(ItemDevolucao::class, 'devolucao_id', 'id');
    }

    public static function getTrib($objeto)
    {

        $arr = (array_values((array)$objeto->ICMS));
        $cst = $arr[0]->CST ? $arr[0]->CST : $arr[0]->CSOSN;

        $pICMS = $arr[0]->pICMS ?? 0;
        $vICMS = $arr[0]->vICMS ?? 0;
        $pRedBC = $arr[0]->pRedBC ?? 0;
        $vBCSTRet = $arr[0]->vBCSTRet ?? 0;
        $modBCST = $arr[0]->modBCST ?? 0;
        $vBCST = $arr[0]->vBCST ?? 0;
        $pICMSST = $arr[0]->pICMSST ?? 0;
        $vICMSST = $arr[0]->vICMSST ?? 0;
        $pMVAST = $arr[0]->pMVAST ?? 0;
        $pST = $arr[0]->pST ?? 0;
        $vICMSSubstituto = $arr[0]->vICMSSubstituto ?? 0;
        $vICMSSTRet = $arr[0]->vICMSSTRet ?? 0;
        $orig = $arr[0]->orig ?? 0;

        $vBC = $arr[0]->vBC ?? 0;

        $arr = (array_values((array)$objeto->PIS));

        $pis = $arr[0]->CST;
        $pPIS = $arr[0]->pPIS ?? 0;


        $arr = (array_values((array)$objeto->COFINS));
        $cofins = $arr[0]->CST;
        $pCOFINS = $arr[0]->COFINS ?? 0;
        if ($pCOFINS == 0) {
            $pCOFINS = $arr[0]->pCOFINS ?? 0;
        }

        $arr = (array_values((array)$objeto->IPI));

        if (isset($arr[1])) {

            $ipi = $arr[1]->CST ?? '99';
            $pIPI = $arr[0]->IPI ?? 0;
            if ($pIPI == 0) {
                $pIPI = $arr[0]->pIPI ?? 0;
            }

            if (isset($arr[1]->pIPI)) {
                $pIPI = $arr[1]->pIPI ?? 0;
            } else {
                if (isset($arr[4]->pIPI)) {
                    $ipi = $arr[4]->CST;
                    $pIPI = $arr[4]->pIPI;
                } else {
                    $pIPI = 0;
                }
            }
        } else {
            $ipi = '99';
            $pIPI = 0;
        }

        $data = [
            'cst_csosn' => (string)$cst,
            'pICMS' => (float)$pICMS,
            'cst_pis' => (string)$pis,
            'pPIS' => (float)$pPIS,
            'cst_cofins' => (string)$cofins,
            'pCOFINS' => (float)$pCOFINS,
            'cst_ipi' => (string)$ipi,
            'pIPI' => (float)$pIPI,
            'pRedBC' => (float)$pRedBC,
            'vBCSTRet' => (float)$vBCSTRet,
            'vBC' => (float)$vBC,
            'vICMS' => (float)$vICMS,
            'modBCST' => (float)$modBCST,
            'vBCST' => (float)$vBCST,
            'pICMSST' => (float)$pICMSST,
            'vICMSST' => (float)$vICMSST,
            'pMVAST' => (float)$pMVAST,
            'pST' => (float)$pST,
            'vICMSSubstituto' => (float)$vICMSSubstituto,
            'vICMSSTRet' => (float)$vICMSSTRet,
            'orig' => (int)$orig
        ];
        return $data;
    }

    public function estadoEmissao()
    {
        if ($this->estado_emissao == 'aprovado') {
            return "<span class='btn btn-sm btn-success px-3'>Aprovado</span>";
        } else if ($this->estado_emissao == 'cancelado') {
            return "<span class='btn btn-sm btn-danger px-3'>Cancelado</span>";
        } else if ($this->estado_emissao == 'rejeitado') {
            return "<span class='btn btn-sm btn-warning px-3'>Rejeitado</span>";
        }
        return "<span class='btn btn-sm btn-info px-3'>Novo</span>";
    }
}
