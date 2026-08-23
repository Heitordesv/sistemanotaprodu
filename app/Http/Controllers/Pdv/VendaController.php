<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthPdv;
use Illuminate\Http\Request;
use App\Models\VendaCaixa;
use App\Models\ItemVendaCaixa;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\FaturaFrenteCaixa;
use App\Helpers\StockMove;
use App\Models\ComissaoVenda;

class VendaController extends Controller
{
    public function salvar(Request $request){
        try{
            $venda = json_decode($request->venda, true);
            if (!is_array($venda)) {
                return response()->json('Venda inválida', 422);
            }

            $xml = $request->xml;
            $empresaId = (int) $request->attributes->get(AuthPdv::EMPRESA_ID_ATTRIBUTE);
            $usuarioAutenticadoId = (int) $request->attributes->get(AuthPdv::USER_ID_ATTRIBUTE);
            $empresa = Empresa::query()->where('id', $empresaId)->firstOrFail();

            $clienteId = isset($venda['cliente_id']) && (int) $venda['cliente_id'] > 0
                ? (int) $venda['cliente_id']
                : null;

            if ($clienteId !== null) {
                Cliente::query()
                    ->where('empresa_id', $empresaId)
                    ->where('id', $clienteId)
                    ->firstOrFail();
            }

            $vendedor = null;
            $usuarioVendaId = $usuarioAutenticadoId;
            if (!empty($venda['vendedor_id'])) {
                $vendedor = Funcionario::query()
                    ->where('empresa_id', $empresaId)
                    ->where('id', (int) $venda['vendedor_id'])
                    ->firstOrFail();

                $usuarioVendedor = Usuario::query()
                    ->where('empresa_id', $empresaId)
                    ->where('id', (int) $vendedor->usuario_id)
                    ->firstOrFail();

                $usuarioVendaId = (int) $usuarioVendedor->id;
            }

            if(($venda['rascunho'] ?? 'false') == "false"){
                $result = VendaCaixa::create([
                    'cliente_id' => $clienteId,
                    'usuario_id' => $usuarioVendaId,
                    'valor_total' => $venda['valor_total'],
                    'NFcNumero' => $venda['NFcNumero'] ?? 0,
                    'natureza_id' => $empresa->configNota->natureza->id,
                    'chave' => $venda['chave'] ?? '',
                    'path_xml' => '',
                    'estado' => $venda['estado'] ?? 'DISPONIVEL',
                    'tipo_pagamento' => $venda['tipo_pagamento'],
                    'forma_pagamento' => '',
                    'dinheiro_recebido' => $venda['dinheiro_recebido'] ?? 0,
                    'troco' => $venda['troco'] ?? 0,
                    'nome' => '',
                    'cpf' => '',
                    'observacao' => '',
                    'desconto' => $venda['desconto'] ?? 0,
                    'acrescimo' => $venda['acrescimo'] ?? 0,
                    'pedido_delivery_id' => 0,
                    'tipo_pagamento_1' => '',
                    'valor_pagamento_1' => 0,
                    'tipo_pagamento_2' => '',
                    'valor_pagamento_2' => 0,
                    'tipo_pagamento_3' => '',
                    'valor_pagamento_3' => 0,
                    'empresa_id' => $empresaId,
                    'bandeira_cartao' => '',
                    'cnpj_cartao' => '',
                    'cAut_cartao' => '',
                    'descricao_pag_outros' => '',
                    'rascunho' => 0,
                    'pdv_java' => 1,
                    'consignado' => 0
                ]);
            }else{
                $result = VendaCaixa::query()
                    ->where('empresa_id', $empresaId)
                    ->where('id', (int) ($venda['codigo_edit'] ?? 0))
                    ->firstOrFail();

                $result->rascunho = 0;
                $result->valor_total = $venda['valor_total'];
                $result->desconto = $venda['desconto'] ?? 0;
                $result->troco = $venda['troco'] ?? 0;
                $result->created_at = date('Y-m-d H:i:s');
                $result->save();

                $result->itens()->delete();
            }

            // Mantido no escopo legado deste PR. A fonte alternativa de XML
            // continua bloqueada para o pacote específico já planejado.
            if($xml != ""){
                $public = env('SERVIDOR_WEB') ? 'public/' : '';
                file_put_contents($public.'xml_nfce/'.$venda['chave'].'.xml', $xml);
            }

            $stockMove = new StockMove();
            foreach(($venda['itens'] ?? []) as $i){
                $produto = Produto::query()
                    ->where('empresa_id', $empresaId)
                    ->where('id', (int) $i['produto_id'])
                    ->firstOrFail();

                $cfop = 0;

                if($empresa->configNota->natureza->sobrescreve_cfop){
                    $cfop = $empresa->configNota->natureza->CFOP_saida_estadual;
                }else{
                    $cfop = $produto->CFOP_saida_estadual;
                }
                ItemVendaCaixa::create([
                    'produto_id' => $produto->id,
                    'venda_caixa_id' => $result->id,
                    'quantidade' => $i['quantidade'],
                    'valor' => $i['valor'],
                    'item_pedido_id' => null,
                    'observacao' => '',
                    'cfop' => $cfop,
                    'valor_custo' => $produto->valor_compra
                ]);

                $stockMove->downStock($produto->id,$i['quantidade']);
            }

            if($vendedor){
                $percentual_comissao = $vendedor->percentual_comissao;
                $valorComissao = $this->calcularComissaoVenda($result, $percentual_comissao);
                ComissaoVenda::create(
                    [
                        'funcionario_id' => $vendedor->id,
                        'venda_id' => $result->id,
                        'tabela' => 'venda_caixas',
                        'valor' => $valorComissao,
                        'status' => 0,
                        'empresa_id' => $empresaId
                    ]
                );
            }

            foreach(($venda['fatura'] ?? []) as $f){
                $fp = explode("-", $f['forma_pagamento'])[0];
                FaturaFrenteCaixa::create([
                    'valor' => __replace($f['valor']),
                    'forma_pagamento' => $fp,
                    'venda_caixa_id' => $result->id    
                ]);
            }

            return response()->json("ok", 200);
        }catch(\Exception $e){
            return response()->json("Erro: " . $e->getMessage(), 200);
        }
    }

    private function calcularComissaoVenda($venda, $percentual_comissao){
        $valorRetorno = 0;
        foreach($venda->itens as $i){
            if($i->produto->perc_comissao > 0){
                $valorRetorno += (($i->valor*$i->quantidade) * $i->produto->perc_comissao) / 100;
            }else{
                $valorRetorno += (($i->valor*$i->quantidade) * $percentual_comissao) / 100;
            }
        }
        return $valorRetorno;
    }

    public function rascunhos(Request $request){
        $vendas = VendaCaixa::
        where('empresa_id', $request->empresa_id)
        ->where('rascunho', 1)
        ->get();

        $retorno = [];
        foreach($vendas as $v){
            $data = [
                'codigo_edit' => $v->id,
                'valor_total' => $v->valor_total,
                'desconto' => $v->desconto,
                'acrescimo' => $v->acrescimo,
                'itensVenda' => $v->itensApi
            ];

            array_push($retorno, $data);
        }
        
        return response()->json($retorno, 200);
    }

}
