<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ConfigNota;
use App\Models\CteOs;
use App\Services\CTeOsService;
use App\Services\CTeService;
use Illuminate\Http\Request;

class CteOsController extends Controller
{
    public function transmitir(Request $request)
    {
        $item = CteOs::findOrFail($request->id);
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();
        if ($config == null) {
            return response()->json("Configure o emitente", 401);
        }
        try {
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
            $cte_service = new CTeOsService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->cidade->uf,
                "cnpj" => $cnpj,
                "schemes" => "PL_CTe_300",
                "versao" => '3.00',
                "proxyConf" => [
                    "proxyIp" => "",
                    "proxyPort" => "",
                    "proxyUser" => "",
                    "proxyPass" => ""
                ]
            ], $config);

            if ($item->estado_emissao == 'rejeitado' || $item->estado_emissao == 'novo') {

                $cte = $cte_service->gerarCTe($item);
                if (!isset($cte['erros_xml'])) {

                    $signed = $cte_service->sign($cte['xml']);
                    $resultado = $cte_service->transmitir($signed, $cte['chave']);
                    return response()->json($resultado, 401);
                    if ($resultado['erro'] == 0) {
                        $item->chave = $cte['chave'];
                        $item->estado_emissao = 'aprovado';

                        $item->cte_numero = $cte['nCte'];
                        $config->ultimo_numero_cte = $cte['nCte'];
                        $config->save();
                        $item->save();

                        $file = file_get_contents(public_path('xml_cte_os/') . $cte['chave'] . '.xml');

                        return response()->json($resultado['success'], 200);
                    } else {
                        $item->estado_emissao = 'rejeitado';
                        $item->chave = $cte['chave'];
                        $item->save();
                        return response()->json($resultado['error'], 403);
                    }
                } else {
                    return response()->json($cte['erros_xml'], 401);
                }
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 404);
        }
    }

    public function cancelar(Request $request)
    {

        $item = CteOs::findOrFail($request->id);

        $config = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $cte_service = new CTeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_CTe_300",
            "versao" => '3.00',
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ], $config);
        $cte = $cte_service->cancelar($item, $request->motivo);

        if (!isset($cte['erro'])) {

            $item->estado_emissao = 'cancelado';
            $item->save();
            return response()->json($cte, 200);
        } else {
            return response()->json($cte['data'], $cte['status']);
        }
    }

    public function corrigir(Request $request)
    {
        $item = CteOs::findOrFail($request->id);
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $cte_service = new CTeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_CTe_300",
            "versao" => '3.00',
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ], $config);
        $cte = $cte_service->cartaCorrecao($item, $request->grupo, $request->campo, $request->motivo);

        if (!isset($cte['erro'])) {

            return response()->json($cte, 200);
        } else {
            return response()->json($cte['data'], $cte['status']);
        }
    }
}
