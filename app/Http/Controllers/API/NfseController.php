<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfigNota;
use App\Models\Nfse as NotaServico;

use Webmaniabr\Nfse\Api\Connection;
use Webmaniabr\Nfse\Api\Exceptions\APIException;
use Webmaniabr\Nfse\Models\NFSe;
use Webmaniabr\Nfse\Interfaces\APIResponse;
use Illuminate\Support\Facades\DB;
use Mail;
class NfseController extends Controller
{
    public function transmitir(Request $request){

        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        Connection::getInstance()->setBearerToken($config->token_nfse);

        if(!is_dir(public_path('nfse_doc'))){
            mkdir(public_path('nfse_doc'), 0777, true);
        }
        $item = NotaServico::findOrFail($request->id);
        $nfse = new NFSe();

        $servico = $item->servico;
        $nfse->Servico->valorServico = $servico->valor_servico;
        $nfse->Servico->discriminacao = $this->retiraAcentos($servico->discriminacao);
        $nfse->Servico->codigoServico = $servico->codigo_servico;
        $nfse->Servico->naturezaOperacao = $item->natureza_operacao;
        if($servico->iss_retido == 1){
            $nfse->Servico->issRetido = $servico->iss_retido;

            if($servico->iss_retido == 1){  
            // $nfse->Servico->responsavel_retencao_iss = $servico->responsavel_retencao_iss;
                $nfse->Servico->Intermediario->responsavelIss = $servico->responsavel_retencao_iss;
            }
        }
        if($servico->codigo_tributacao_municipio){
            $nfse->Servico->codigoTributacaoMunicipio = $servico->codigo_tributacao_municipio;
        }if($servico->codigo_cnae){
            $nfse->Servico->codigoCnae = $servico->codigo_cnae;
        }
        $nfse->Servico->exigibilidadeIss = $servico->exigibilidade_iss;
        if($servico->aliquota_iss){
            $nfse->Servico->Impostos->iss = $servico->aliquota_iss;
        }

            // $nfse->Servico->tipoTributacao = 1;

        $doc = preg_replace('/[^0-9]/', '', $item->documento);
        $nfse->Tomador->nomeCompleto = $item->razao_social;

        if(strlen($doc) == 11){
            $nfse->Tomador->cpf = $doc;
        }else{
            $nfse->Tomador->razaoSocial = $item->razao_social;
            $nfse->Tomador->cnpj = $doc;
        }

        $nfse->Tomador->cep = preg_replace('/[^0-9]/', '', $item->cep);
        $nfse->Tomador->endereco = $this->retiraAcentos($item->rua);
        $nfse->Tomador->numero = $item->numero;
        if($item->complemento){
            $nfse->Tomador->complemento = $this->retiraAcentos($item->complemento);
        }
        $nfse->Tomador->bairro = $this->retiraAcentos($item->bairro);
        $nfse->Tomador->cidade = $this->retiraAcentos($item->cidade->nome);
        $nfse->Tomador->uf = $item->cidade->uf;

        try {
            // $config->ambiente = 1;
            if($config->ambiente == 2){
                $response = $nfse->emitirHomologacao();
            }else{
                $response = $nfse->emitir();
            }

            // dd($response);
            // die;
            $object = json_decode($response->getMessage());
            if(isset($object->status)){

                if($object->status == 'reprovado'){
                    $item->estado_emissao = 'rejeitado';
                    $item->save();
                    return response()->json($object, 401);
                }elseif($object->status == 'processado'){
                    $object = $object->info_nfse[0];

                    $item->codigo_verificacao = $object->codigo_verificacao;
                    $item->url_pdf_nfse = $object->pdf_nfse;
                    $item->url_pdf_rps = $object->pdf_rps;
                    $item->url_xml = $object->xml;
                    $item->numero_nfse = $object->numero;
                    $item->uuid = $object->uuid;
                    $item->estado_emissao = 'aprovado';

                    $item->save();

                    $xml = file_get_contents($item->url_xml);
                    file_put_contents(public_path('nfse_doc/')."$item->uuid.xml", $xml);
                    return response()->json($object, 200);
                }elseif($object->status == 'processando'){
                    
                    $item->estado_emissao = 'processando';
                    $item->uuid = $object->uuid;
                    $item->save();
                    return response()->json($object, 401);
                }else{  
                    // return response()->json($object, 401);

                    $item->codigo_verificacao = $object->codigo_verificacao;
                    $item->url_pdf_nfse = $object->pdf_nfse;
                    $item->url_pdf_rps = $object->pdf_rps;
                    $item->url_xml = $object->xml;
                    $item->numero_nfse = $object->numero;
                    $item->uuid = $object->uuid;
                    $item->estado_emissao = 'aprovado';
                    
                    $item->save();

                    $xml = file_get_contents($item->url_xml);
                    file_put_contents(public_path('nfse_doc/')."$item->uuid.xml", $xml);
                    return response()->json($object, 200);
                }
                // dd($object);
            }else{
                $stringResp = substr($response->getMessage(), 0, 44);
                if($stringResp == 'Nota Fiscal já se encontra em processamento'){
                    $item->estado = 'processando';
                    $item->save();
                }
                return response()->json($response->getMessage(), 403);      
            }
        } catch (\Throwable $th) {
            // dd((object) [ 'exception' => $th->getMessage() ]);
            return response()->json($th->getMessage() . ", linha: " . $th->getLine(), 407);

        } catch (APIException $a) {
            // dd((object) [ 'error' => $a->getMessage() ]);

            return response()->json($a->getMessage(), 404);
        }

    }

    private function retiraAcentos($texto){
        return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/", "/(ç)/"),explode(" ","a A e E i I o O u U n N c"),$texto);
    }

    public function cancelar(Request $request){
        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        Connection::getInstance()->setBearerToken($config->token_nfse);
        $item = NotaServico::findOrFail($request->id);
        $nfse = new NFSe();

        $nfse->uuid = $item->uuid;

        try {
            $response = $nfse->cancelar($request->motivo);
            $message = $response->getMessage();
            if(isset($message->status)){
                if($message->status == 'cancelado'){
                    $item->estado = 'cancelado';
                    $item->save();
                }
            }
            // response($response->getMessage());
            return response()->json($response->getMessage(), 200);
        } catch (\Throwable $th) {
            // response((object) [ 'exception' => $th->getMessage() ]);
            return response()->json($th->getMessage(), 401);
        } catch (APIException $a) {
            // response((object) [ 'error' => $a->getMessage() ]);
            return response()->json($a->getMessage(), 401);
        }

    }

    public function consultar(Request $request){
        $config = ConfigNota::
        where('empresa_id', $request->empresa_id)
        ->first();

        Connection::getInstance()->setBearerToken($config->token_nfse);
        $item = NotaServico::findOrFail($request->id);
        $nfse = new NFSe();

        $nfse->uuid = $item->uuid;
        try {
            $response = $nfse->consultar();
            $object = json_decode($response->getMessage());

            if(isset($object->info_nfse)){
                $object = $object->info_nfse[0];
            }

            if(isset($object->codigo_verificacao)){
                $item->codigo_verificacao = $object->codigo_verificacao;
                $item->url_pdf_nfse = $object->pdf_nfse;
                $item->url_pdf_rps = $object->pdf_rps;
                $item->url_xml = $object->xml;
                $item->numero_nfse = $object->numero;
                $item->uuid = $object->uuid;
                $item->estado_emissao = 'aprovado';
                $item->save();
                $xml = file_get_contents($item->url_xml);
                file_put_contents(public_path('nfse_doc/')."$item->uuid.xml", $xml);
            }

            if($object->status == "reprovado"){
                $item->estado_emissao = 'rejeitado';
                $item->save();
                
                return response()->json($object->motivo[0], 401);
            }

            if($object->status == "cancelado"){
                $item->estado_emissao = 'cancelado';
                $item->save();
            }

            return response()->json($response->getMessage(), 200);

        } catch (\Throwable $th) {
            // response((object) [ 'exception' => $th->getMessage() ]);
            return response()->json($th->getMessage(), 401);
        } catch (APIException $a) {
            // response((object) [ 'error' => $a->getMessage() ]);
            return response()->json($a->getMessage(), 401);
        }

    }
}
