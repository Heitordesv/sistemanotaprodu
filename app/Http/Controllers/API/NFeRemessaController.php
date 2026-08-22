<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ConfigNota;
use App\Models\Contigencia;
use App\Models\EscritorioContabil;
use App\Models\InutilizaNfe;
use App\Models\RemessaNfe;
use App\Models\NuvemShopPedido;
use App\Models\PedidoEcommerce;
use App\Services\NFeRemessaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NFeRemessaController extends Controller
{
    // transmitir antiga
    /*
    public function transmitir(Request $request)
    {
        $venda = RemessaNfe::findOrFail($request->id);
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();
        if ($config == null) {
            return response()->json("Configure o emitente", 401);
        }
        try {
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
            $nfe_service = new NFeRemessaService([
                "atualizacao" => $request->data_retroativa,
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->cidade->uf,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id
            ], $config);
            if ($venda->estado_emissao == 'rejeitado' || $venda->estado_emissao == 'novo') {
                $nfe = $nfe_service->gerarNFe($venda);
                if (!isset($nfe['erros_xml'])) {

                    $signed = $nfe_service->sign($nfe['xml']);
                    $resultado = $nfe_service->transmitir($signed, $nfe['chave']);

                    if ($resultado['erro'] == 0) {
                        $venda->chave = $nfe['chave'];
                        $venda->estado_emissao = 'aprovado';
                        $venda->nSerie = $config->numero_serie_nfe;
                        $venda->data_emissao = date('Y-m-d h:i:s');
                        $venda->numero_nfe = $nfe['nNf'];
                        $config->ultimo_numero_nfe = $nfe['nNf'];

                        // $venda->contigencia = $this->getContigencia($config->empresa_id);
                        // $venda->reenvio_contigencia = 0;
                        $config->save();

                        if ($venda->pedido_ecommerce_id > 0) {
                            $pedido = PedidoEcommerce::findOrFail($venda->pedido_ecommerce_id);
                            $pedido->numero_nfe = $nfe['nNf'];
                            $pedido->status_preparacao = 'approved';
                            $pedido->save();
                        }

                        if ($venda->pedido_nuvemshop_id > 0) {
                            $pedido = NuvemShopPedido::findOrFail($venda->pedido_nuvemshop_id);
                            $pedido->numero_nfe = $nfe['nNf'];
                            $pedido->save();
                        }
                        $venda->save();

                        $this->criarLog($venda);
                        $this->enviarEmailAutomatico($venda);

                        $file = file_get_contents(public_path('xml_nfe/') . $nfe['chave'] . '.xml');
                        importaXmlSieg($file, $venda->empresa_id);
                        return response()->json($resultado['success'], 200);
                    } else {
                        $venda->estado_emissao = 'rejeitado';
                        $venda->chave = $nfe['chave'];
                        $venda->save();
                        return response()->json($resultado['error'], 403);
                    }
                } else {
                    return response()->json($nfe['erros_xml'], 401);
                }
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 404);
        }
    }
    */
    
    // transmitir novo
    public function transmitir(Request $request)
    {
        $venda = RemessaNfe::findOrFail($request->id);

        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        if (!$config) {
            return response()->json([
                'erro' => 1,
                'mensagem' => 'Configure o emitente'
            ], 401);
        }

        try {
            // Só permite envio se estiver novo ou rejeitado
            if (!in_array($venda->estado_emissao, ['novo', 'rejeitado'])) {
                return response()->json([
                    'erro' => 1,
                    'mensagem' => 'NF-e já enviada ou em processamento'
                ], 409);
            }

            $cnpj = preg_replace('/\D/', '', $config->cnpj);

            $service = new NFeRemessaService([
                'atualizacao' => $request->data_retroativa ?? now()->format('Y-m-d H:i:s'),
                'tpAmb'       => (int) $config->ambiente,
                'razaosocial' => $config->razao_social,
                'siglaUF'     => $config->cidade->uf,
                'cnpj'        => $cnpj,
                'schemes'     => 'PL_009_V4',
                'versao'      => '4.00',
                'tokenIBPT'   => 'AAAAAAA',
                'CSC'         => $config->csc,
                'CSCid'       => $config->csc_id,
            ], $config);

            // Gera XML
            $nfe = $service->gerarNFe($venda);

            if (isset($nfe['erros_xml'])) {
                return response()->json([
                    'erro' => 1,
                    'mensagem' => 'Erro ao gerar XML',
                    'detalhes' => $nfe['erros_xml']
                ], 422);
            }

            // Assina e transmite
            $signed    = $service->sign($nfe['xml']);
            $resultado = $service->transmitir($signed, $nfe['chave']);

            /**
             * ❌ REJEIÇÃO
             */
            if ($resultado['erro'] === 1) {
                $venda->update([
                    'estado_emissao' => 'rejeitado',
                    'chave' => $nfe['chave']
                ]);

                return response()->json($resultado, 403);
            }

            /**
             * ✅ AUTORIZADA
             */
            if (($resultado['autorizado'] ?? false) === true) {

                $venda->update([
                    'chave'          => $nfe['chave'],
                    'estado_emissao' => 'aprovado',
                    'nSerie'         => $config->numero_serie_nfe,
                    'numero_nfe'     => $nfe['nNf'],
                    'data_emissao'   => now(),
                ]);

                $config->update([
                    'ultimo_numero_nfe' => $nfe['nNf']
                ]);

                // Atualizações externas
                if ($venda->pedido_ecommerce_id) {
                    PedidoEcommerce::where('id', $venda->pedido_ecommerce_id)
                        ->update([
                            'numero_nfe' => $nfe['nNf'],
                            'status_preparacao' => 'approved'
                        ]);
                }

                if ($venda->pedido_nuvemshop_id) {
                    NuvemShopPedido::where('id', $venda->pedido_nuvemshop_id)
                        ->update([
                            'numero_nfe' => $nfe['nNf']
                        ]);
                }

                $this->criarLog($venda);
                $this->enviarEmailAutomatico($venda);

                // Importa XML apenas se existir
                $xmlPath = public_path('xml_nfe/' . $nfe['chave'] . '.xml');
                if (file_exists($xmlPath)) {
                    importaXmlSieg(file_get_contents($xmlPath), $venda->empresa_id);
                }

                return response()->json($resultado, 200);
            }

            /**
             * ⚠️ Qualquer outro retorno inesperado
             */
            return response()->json([
                'erro' => 1,
                'mensagem' => 'Retorno inesperado da SEFAZ',
                'retorno' => $resultado
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'erro' => 1,
                'mensagem' => $e->getMessage()
            ], 500);
        }
    }

    private function getContigencia($empresa_id)
    {
        $active = Contigencia::where('empresa_id', $empresa_id)
        ->where('status', 1)
        ->where('documento', 'NFe')
        ->first();
        return $active != null ? 1 : 0;
    }

    private function criarLog($objeto, $tipo = 'emissao')
    {
        if (isset(session('user_logged')['log_id'])) {
            $record = [
                'tipo' => $tipo,
                'usuario_log_id' => session('user_logged')['log_id'],
                'tabela' => 'vendas',
                'registro_id' => $objeto->id,
                'empresa_id' => request()->empresa_id
            ];
            __saveLog($record);
        }
    }

    private function enviarEmailAutomatico($venda)
    {
        $escritorio = EscritorioContabil::where('empresa_id', request()->empresa_id)
        ->first();
        if ($escritorio != null && $escritorio->envio_automatico_xml_contador) {
            $email = $escritorio->email;
            Mail::send('mail.xml_automatico', ['descricao' => 'Envio de NF-e'], function ($m) use ($email, $venda) {
                $nomeEmpresa = env('MAIL_NAME');
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $emailEnvio = env('MAIL_USERNAME');
                $m->from($emailEnvio, $nomeEmpresa);
                $m->subject('Envio de XML Automático');
                $m->attach(public_path('xml_nfe/' . $venda->chave . '.xml'));
                $m->to($email);
            });
        }
    }

    public function corrigir(Request $request)
    {
        $venda = RemessaNfe::findOrFail($request->id);
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFeRemessaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        $nfe = $nfe_service->cartaCorrecao($venda, $request->motivo);
        if (!isset($nfe['erro'])) {
            return response()->json($nfe, 200);
        } else {
            return response()->json($nfe['data'], $nfe['status']);
        }
    }

    public function cartaCorrecao(Request $request)
    {
        $venda = RemessaNfe::where('id', $request->id)
        ->first();
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFeRemessaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        $nfe = $nfe_service->cartaCorrecao($request->id, $request->correcao);
        echo json_encode($nfe);
    }

    public function consultarNfe(Request $request)
    {
        $venda = RemessaNfe::findOrFail($request->id);
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFeRemessaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        $consulta = $nfe_service->consultar($venda);
        return response()->json($consulta, 200);
        // return response()->json($consulta['success'], 200);
    }

    public function cancelar(Request $request)
    {
        $venda = RemessaNfe::findOrFail($request->id);
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFeRemessaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        $nfe = $nfe_service->cancelar($venda, $request->motivo);
        if (!isset($nfe['erro'])) {
            $venda->estado_emissao = 'cancelado';
            $venda->valor_total = 0;
            $venda->save();
            // $this->reverteEstoque($venda->itens);
            //devolve estoque
            $file = file_get_contents(public_path('xml_nfe_cancelada/' . $venda->chave . '.xml'));
            importaXmlSieg($file, $config->empresa_id);
            // $this->removerDuplicatas($venda);
            $this->criarLog($venda, 'cancelamento');
            return response()->json($nfe, 200);
        } else {
            return response()->json($nfe['data'], $nfe['status']);
        }
    }

    public function inutilizar(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFeRemessaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        try {
            $result = $nfe_service->inutilizar(
                $config,
                $request->numero_inicial,
                $request->numero_final,
                $request->motivo
            );
            // if ($result['infInut']['cStat'] == '102') {
            //     InutilizaNfe::create([
            //         'empresa_id' => $config->empresa_id,
            //         'numero_inicial' => $request->numero_inicial,
            //         'numero_final' => $request->numero_final,
            //         'justificativa' => $request->motivo,
            //         'numero_serie' => $config->numero_serie_nfe
            //     ]);
            // }
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }
}
