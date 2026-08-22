<?php

namespace App\Http\Controllers;

use App\Models\ConfigNota;
use App\Models\RemessaNfe;
use App\Services\NFeRemessaService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use NFePHP\DA\NFe\Danfe;

class NfeRemessaXmlController extends Controller
{
    protected $empresa_id = null;
    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if (!$value) {
                return redirect("/login");
            }
            return $next($request);
        });
    }

    public function xmlTemp($id)
    {
        $item = RemessaNfe::where('empresa_id', $this->empresa_id)
        ->where('id', $id)
        ->first();
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
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
        $nfe = $nfe_service->gerarNFe($item);
        if (!isset($nfe['erros_xml'])) {
            $xml = $nfe['xml'];
            return response($xml)
            ->header('Content-Type', 'application/xml');
        } else {
            // print_r($nfe['erros_xml']);
            foreach ($nfe['erros_xml'] as $err) {
                echo $err;
            }
        }
    }

    public function danfeTemp($id)
    {
        $item = RemessaNfe::where('empresa_id', $this->empresa_id)
        ->where('id', $id)
        ->first();
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
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
        $nfe = $nfe_service->gerarNFe($item);
        if (!isset($nfe['erros_xml'])) {
            $xml = $nfe['xml'];
            try {
                $logo = null;
                $danfe = new Danfe($xml);
                $danfe->setVUnComCasasDec($config->casas_decimais);
                $pdf = $danfe->render($logo);
                header("Content-Disposition: ; filename=DANFE TEMPORÁRIA");
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            print_r($nfe['erros_xml']);
        }
    }

    public function gerarXml($id)
    {
        $item = RemessaNfe::where('empresa_id', $this->empresa_id)
        ->where('id', $id)
        ->first();

        if (valida_objeto($item)) {
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
            $nfe_service = new NFeRemessaService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->UF,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id,
            ], $config);
            $nfe = $nfe_service->gerarNFe($id);
            if (!isset($nfe['erros_xml'])) {
                $xml = $nfe_service->sign($nfe['xml']);

                return response($xml)
                ->header('Content-Type', 'application/xml');
            } else {
                foreach ($nfe['erros_xml'] as $e) {
                    echo $e;
                }
            }
        } else {
            return redirect('/403');
        }
    }

    public function imprimir($id)
    {
        $item = RemessaNfe::where('id', $id)
        ->where('empresa_id', $this->empresa_id)
        ->first();
        if (valida_objeto($item)) {
            $config = ConfigNota::where('empresa_id', $this->empresa_id)
            ->first();
            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            if (file_exists($public . 'xml_nfe/' . $item->chave . '.xml')) {
                $xml = file_get_contents($public . 'xml_nfe/' . $item->chave . '.xml');
                if ($config->logo) {
                    $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'uploads/configEmitente' . $config->logo));
                } else {
                    $logo = null;
                }
                try {
                    $danfe = new Danfe($xml);
                    $danfe->setVUnComCasasDec($config->casas_decimais);

                    // $id = $danfe->monta($logo);
                    $pdf = $danfe->render($logo);
                    header("Content-Disposition: ; filename=DANFE $item->numero_nfe.pdf");
                    return response($pdf)
                    ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }
            } else {
                echo "Arquivo XML não encontrado!!";
            }
        } else {
            return redirect('/403');
        }
    }

    public function baixarXml($id){
        $item = RemessaNfe::findOrFail($id);
        if(valida_objeto($item)){
            $public = env('SERVIDOR_WEB') ? 'public/' : '';
            if(file_exists($public.'xml_nfe/'.$item->chave.'.xml')){

                return response()->download($public.'xml_nfe/'.$item->chave.'.xml');
            }else{
                echo "Arquivo XML não encontrado!!";
            }
        }else{
            return redirect('/403');
        }

    }

    
}
