<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ComponenteCte;
use App\Models\Cte;
use App\Models\ConfigNota;
use Illuminate\Http\Request;
use App\Services\CTeService;

class CteController extends Controller
{
    // public function linhaInformacoes(Request $request){
    //     try{
    //         $cod = $request->cod;
    //         $tipo_medida = $request->tipo_medida;
    //         $qtd_carga = $request->qtd_carga;
    //         //$cte_id = $request->c;

    //        // $cte = Cte::findOrFail($cte_id);
    //         return view('cte.partials.row_info_cte', compact('cod', 'tipo_medida', 'qtd_carga'));
    //     }catch(\Exception $e){
    //         return response()->json($e->getMessage(), 401);
    //     }
    // }

    public function transmitir(Request $request){

        $item = Cte::findOrFail($request->id);
        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        if($config == null){
            return response()->json("Configure o emitente", 401);
        }

        try{
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
            $cte_service = new CTeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->cidade->uf,
                "cnpj" => $cnpj,
                "schemes" => "PL_CTe_400",
                "versao" => '4.00',
                "proxyConf" => [
                    "proxyIp" => "",
                    "proxyPort" => "",
                    "proxyUser" => "",
                    "proxyPass" => ""
                ]
            ], $config);
            
            if($item->estado_emissao == 'rejeitado' || $item->estado_emissao == 'novo'){

                $cte = $cte_service->gerarCTe($item);
                if(!isset($cte['erros_xml'])){

                    $signed = $cte_service->sign($cte['xml']);
                    $resultado = $cte_service->transmitir($signed, $cte['chave']);

                    if($resultado['erro'] == 0){
                        $item->chave = $cte['chave'];
                        $item->estado_emissao = 'aprovado';

                        $item->cte_numero = $cte['nCte'];
                        $config->ultimo_numero_cte = $cte['nCte'];
                        $config->save();
                        $item->save();

                        $file = file_get_contents(public_path('xml_cte/').$cte['chave'].'.xml');

                        return response()->json($resultado['success'], 200);

                    }else{
                        $item->estado_emissao = 'rejeitado';
                        $item->chave = $cte['chave'];
                        $item->save();
                        return response()->json($resultado['error'], 403);
                    }
                }else{
                    return response()->json($cte['erros_xml'], 401);
                }
            }
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 404);
        }

    }

    public function consultar(Request $request){

        $item = Cte::findOrFail($request->id);

        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $cte_service = new CTeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_CTe_400",
            "versao" => '4.00',
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ], $config);
        $consulta = $cte_service->consultar($item);
        return response()->json($consulta, 200);

    }

    public function corrigir(Request $request){

        $item = Cte::findOrFail($request->id);

        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $cte_service = new CTeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_CTe_400",
            "versao" => '4.00',
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ], $config);
        $cte = $cte_service->cartaCorrecao($item, $request->grupo, $request->campo, $request->motivo);

        if(!isset($cte['erro'])){

            return response()->json($cte, 200);
        }else{
            return response()->json($cte['data'], $cte['status']);
        }

    }

    public function cancelar(Request $request){

        $item = Cte::findOrFail($request->id);

        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $cte_service = new CTeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_CTe_400",
            "versao" => '4.00',
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ], $config);
        $cte = $cte_service->cancelar($item, $request->motivo);

        if(!isset($cte['erro'])){

            $item->estado_emissao = 'cancelado';
            $item->save();

            return response()->json($cte, 200);

        }else{
            return response()->json($cte['data'], $cte['status']);
        }

    }

    public function inutiliza(Request $request){

        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $cte_service = new CTeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_CTe_400",
            "versao" => '4.00',
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ], $config);
        try{
            $result = $cte_service->inutilizar($request->numero_inicial,
                $request->numero_final, $request->motivo, $config);

            return response()->json($result, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);

        }

    }
}
