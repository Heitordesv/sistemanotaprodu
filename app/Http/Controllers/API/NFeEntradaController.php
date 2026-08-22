<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NFeEntradaService;
use App\Models\Compra;
use App\Models\ConfigNota;
use App\Models\EscritorioContabil;

class NFeEntradaController extends Controller
{
    public function transmitir(Request $request){

        $compra = Compra::findOrFail($request->id);
        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        if($config == null){
            return response()->json("Configure o emitente", 401);
        }

        try{
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
            $nfe_service = new NFeEntradaService([
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

            $nfe = $nfe_service->gerarNFe($compra);
            $signed = $nfe_service->sign($nfe['xml']);
            $resultado = $nfe_service->transmitir($signed, $nfe['chave']);

            if($resultado['erro'] == 0){

                $compra->chave = $nfe['chave'];
                $compra->estado = 'aprovado';
                $compra->numero_emissao = $nfe['nNf'];
                $compra->data_emissao = date('Y-m-d H:i:s');
                $compra->save();

                $config->ultimo_numero_nfe = $nfe['nNf'];
                $config->save();
                
                $this->enviarEmailAutomatico($compra);

                $file = file_get_contents(public_path('xml_entrada_emitida/'.$nfe['chave'].'.xml'));
                importaXmlSieg($file, $compra->empresa_id);

                return response()->json($resultado['success'], 200);

            }else{
                $compra->estado = 'rejeitado';
                $compra->save();
                return response()->json($resultado['error'], 403);
            }

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 404);
        }
    }

    private function enviarEmailAutomatico($compra){
        $escritorio = EscritorioContabil::
        where('empresa_id', $compra->empresa_id)
        ->first();

        if($escritorio != null && $escritorio->envio_automatico_xml_contador){
            $email = $escritorio->email;
            Mail::send('mail.xml_automatico', ['descricao' => 'Envio de NF-e Entrada'], function($m) use ($email, $compra){
                $nomeEmpresa = env('MAIL_NAME');
                $emailEnvio = env('MAIL_USERNAME');

                $m->from($emailEnvio, $nomeEmpresa);
                $m->subject('Envio de XML Automático');

                $m->attach(public_path('xml_entrada_emitida/'.$compra->chave.'.xml'));
                $m->to($email);
            });
        }
    }

    public function consultar(Request $request){

        $item = Compra::findOrFail($request->id);

        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFeEntradaService([
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
        $consulta = $nfe_service->consultar($item);
        return response()->json($consulta, 200);

    }

    public function corrigir(Request $request){

        $item = Compra::findOrFail($request->id);

        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFeEntradaService([
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
        $nfe = $nfe_service->cartaCorrecao($item, $request->motivo);

        if(!isset($nfe['erro'])){

            return response()->json($nfe, 200);
        }else{
            return response()->json($nfe['data'], $nfe['status']);
        }

    }

    public function cancelar(Request $request){

        $item = Compra::findOrFail($request->id);

        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFeEntradaService([
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
        $nfe = $nfe_service->cancelar($item, $request->motivo);

        if(!isset($nfe['erro'])){

            $item->estado = 'cancelado';
            $item->save();

            // $this->reverteEstoque($venda->itens);
            //devolve estoque

            $file = file_get_contents(public_path('xml_nfe_entrada_cancelada/'.$item->chave.'.xml'));
            importaXmlSieg($file, $config->empresa_id);

            return response()->json($nfe, 200);

        }else{
            return response()->json($nfe['data'], $nfe['status']);
        }

    }
}
