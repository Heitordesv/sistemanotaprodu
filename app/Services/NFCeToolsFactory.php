<?php

namespace App\Services;

use App\Models\ConfigNota;
use App\Models\Contigencia;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;
use NFePHP\NFe\Factories\Contingency;
use NFePHP\NFe\Tools;

class NFCeToolsFactory
{
    public function make(ConfigNota $config): Tools
    {
        $cnpj = preg_replace('/\D/', '', (string) $config->cnpj);
        $serviceConfig = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb' => (int) $config->ambiente,
            'razaosocial' => (string) $config->razao_social,
            'siglaUF' => (string) $config->cidade->uf,
            'cnpj' => $cnpj,
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'tokenIBPT' => 'AAAAAAA',
            'CSC' => (string) $config->csc,
            'CSCid' => (string) $config->csc_id,
        ];

        $tools = new Tools(
            json_encode($serviceConfig),
            Certificate::readPfx($config->arquivo, $config->senha)
        );

        $soapCurl = new SoapCurl();
        $soapCurl->httpVersion('1.1');
        $tools->loadSoapClass($soapCurl);
        $tools->model(65);

        $contingencia = Contigencia::query()
            ->where('empresa_id', (int) $config->empresa_id)
            ->where('status', 1)
            ->where('documento', 'NFCe')
            ->first();

        if ($contingencia) {
            $tools->contingency = new Contingency($contingencia->status_retorno);
        }

        return $tools;
    }
}
