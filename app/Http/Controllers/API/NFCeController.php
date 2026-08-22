<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ConfigNota;
use App\Models\Certificado;
use App\Models\VendaCaixa;
use App\Models\Usuario;
use App\Models\Contigencia;
use App\Models\EscritorioContabil;
use App\Services\AutorizacaoDevolucaoService;
use App\Services\DevolucaoEstoqueService;
use App\Services\NFCeService;
use Illuminate\Support\Facades\DB;
class NFCeController extends Controller
{
    public function transmitir(Request $request)
    {

        $venda = VendaCaixa::findOrFail($request->id);
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();

        if ($config == null) {
            return response()->json("Configure o emitente", 401);
        }

        try {
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
            $nfce_service = new NFCeService([
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

            if ($venda->estado_emissao == 'rejeitado' || $venda->estado_emissao == 'novo') {

                $nfce = $nfce_service->gerarNFCe($venda);

                if (!isset($nfce['erros_xml'])) {

                    $signed = $nfce_service->sign($nfce['xml']);

                    if($this->getContigencia($venda->empresa_id)){
                        if(!is_dir(public_path('xml_nfce_contigencia'))){
                            mkdir(public_path('xml_nfce_contigencia'), 0777, true);
                        }
                        $venda->contigencia = 1;
                        $venda->reenvio_contigencia = 0;
                        $venda->chave = $nfce['chave'];
                        $venda->estado_emissao = 'APROVADO';
                        $venda->numero_nfce = $nfce['nNf'];
                        $venda->save();
                        $config->ultimo_numero_nfce = $nfce['nNf'];
                        $config->save();

                        file_put_contents(public_path('xml_nfce_contigencia/').$nfce['chave'].'.xml', $signed);

                        return response()->json('OFFL', 200);

                    }else{
                        $resultado = $nfce_service->transmitir($signed, $nfce['chave']);

                        if ($resultado['erro'] == 0) {
                            $venda->chave = $nfce['chave'];
                            $venda->estado_emissao = 'aprovado';
                            $venda->numero_nfce = $nfce['nNf'];
                            $venda->save();

                            $config->ultimo_numero_nfce = $nfce['nNf'];
                            $config->save();

                            $this->enviarEmailAutomatico($venda);
                            $file = file_get_contents(public_path('xml_nfce/'.$nfce['chave'].'.xml'));
                            importaXmlSieg($file, $venda->empresa_id);
                            return response()->json($resultado['success'], 200);
                        } else {
                            $venda->estado_emissao = 'rejeitado';
                            $venda->chave = $nfce['chave'];
                            $venda->save();
                            return response()->json($resultado['error'], 403);
                        }
                    }
                } else {
                    return response()->json($nfce['erros_xml'], 401);
                }
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 404);
        }
    }

    private function getContigencia($empresa_id){
        $active = Contigencia::
        where('empresa_id', $empresa_id)
        ->where('status', 1)
        ->where('documento', 'NFCe')
        ->first();
        return $active != null ? 1 : 0;
    }


    private function enviarEmailAutomatico($venda)
    {
        $escritorio = EscritorioContabil::where('empresa_id', $venda->empresa_id)
        ->first();

        if ($escritorio != null && $escritorio->envio_automatico_xml_contador) {
            $email = $escritorio->email;
            Mail::send('mail.xml_automatico', ['descricao' => 'Envio de NFCe'], function ($m) use ($email, $venda) {
                $nomeEmpresa = env('MAIL_NAME');
                $emailEnvio = env('MAIL_USERNAME');

                $m->from($emailEnvio, $nomeEmpresa);
                $m->subject('Envio de XML Automático');

                $m->attach(public_path('xml_nfce/' . $venda->chave . '.xml'));
                $m->to($email);
            });
        }
    }

    public function consultaStatusSefaz(Request $request){
        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $nfce_service = new NFCeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id,
            "is_filial" => 0
        ], $config);
        $consulta = $nfce_service->consultaStatus((int)$config->ambiente, $config->cidade->uf);
        return response()->json($consulta, 200);
    }

    public function consultar(Request $request)
    {

        $venda = VendaCaixa::findOrFail($request->id);

        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfce_service = new NFCeService([
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
        $consulta = $nfce_service->consultar($venda);
        return response()->json($consulta, 200);
    }

   public function cancelar(
        Request $request,
        AutorizacaoDevolucaoService $autorizacaoService,
        DevolucaoEstoqueService $devolucaoEstoqueService
    )
    {
        $dados = $request->validate([
            'id' => 'required|integer',
            'empresa_id' => 'required|integer',
            'motivo' => 'required|string|min:15|max:255',
            'admin_id' => 'nullable|integer',
            'admin_senha' => 'nullable|string|max:100',
        ]);

        $usuarioLogado = Usuario::query()
        ->where('id', (int) get_id_user())
        ->where('ativo', true)
        ->firstOrFail();
        $empresaId = (int) $usuarioLogado->empresa_id;

        if ((int) $dados['empresa_id'] !== $empresaId) {
            abort(403, 'Empresa inválida para esta devolução.');
        }

        $venda = VendaCaixa::where('id', $dados['id'])
        ->where('empresa_id', $empresaId)
        ->firstOrFail();

        if (
            $venda->estado_emissao === 'cancelado' ||
            (bool) $venda->retorno_estoque
        ) {
            return response()->json([
                'message' => 'Esta venda já foi cancelada e devolvida.',
            ], 409);
        }

        $autorizacao = $autorizacaoService->autorizar(
            $empresaId,
            $dados['admin_id'] ?? null,
            $dados['admin_senha'] ?? null
        );

        $config = ConfigNota::where('empresa_id', $venda->empresa_id)
        ->firstOrFail();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfce_service = new NFCeService([
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
        $nfe = $nfce_service->cancelar($venda, $dados['motivo']);

        if (!isset($nfe['erro'])) {
            DB::transaction(function () use (
                $venda,
                $dados,
                $autorizacao,
                $autorizacaoService,
                $devolucaoEstoqueService
            ) {
                $autorizacaoService->registrar(
                    $venda,
                    $autorizacao['solicitante'],
                    $autorizacao['autorizador'],
                    'cancelamento_fiscal',
                    $dados['motivo']
                );

                $devolucaoEstoqueService->devolver(
                    $venda,
                    $autorizacao['solicitante'],
                    $autorizacao['autorizador']
                );

                $venda->estado_emissao = 'cancelado';
                $venda->retorno_estoque = 1;
                $venda->save();
            });

            $file = file_get_contents(public_path('xml_nfce_cancelada/' . $venda->chave . '.xml'));
            importaXmlSieg($file, $config->empresa_id);

            $this->criarLog($venda, 'cancelamento');
            return response()->json($nfe, 200);
        } else {
            return response()->json($nfe['data'], $nfe['status']);
        }
    }


    private function criarLog($objeto, $tipo = 'emissao')
    {
        if (isset(session('user_logged')['log_id'])) {
            $record = [
                'tipo' => $tipo,
                'usuario_log_id' => session('user_logged')['log_id'],
                'tabela' => 'venda_caixas',
                'registro_id' => $objeto->id,
                'empresa_id' => $objeto->empresa_id
            ];
            __saveLog($record);
        }
    }

    public function inutilizar(Request $request)
    {

        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfce_service = new NFCeService([
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
            $result = $nfce_service->inutilizar(
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