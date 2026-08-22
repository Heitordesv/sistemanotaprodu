<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManifestaDfe;
use App\Models\ConfigNota;
use App\Models\ManifestoDia;
use App\Services\DFeService;

class DfeController extends Controller
{
    public function novosDocumentos(Request $request){
        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $dfe_service = new DFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => 1,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);

        $manifesto = ManifestaDfe::
        where('empresa_id', $request->empresa_id)
        ->orderBy('nsu', 'desc')->first();

        if($manifesto == null) $nsu = 0;
        else $nsu = $manifesto->nsu;
        $docs = $dfe_service->novaConsulta($nsu);
        $novos = [];

        if(!isset($docs['erro'])){

            $novos = [];
            foreach($docs as $d) {
                if($this->validaNaoInserido($d['chave'])){
                    if($d['valor'] > 0 && $d['nome']){
                        ManifestaDfe::create($d);
                        array_push($novos, $d);
                    }
                }
            }

            ManifestoDia::create([
                'empresa_id' => request()->empresa_id
            ]);
            return response()->json($novos, 200);
        }else{
            return response()->json($docs, 401);
        }

    }

    private function validaNaoInserido($chave){
        $m = ManifestaDfe::
        where('empresa_id', request()->empresa_id)
        ->where('chave', $chave)->first();
        if($m == null) return true;
        else return false;
    }


}
