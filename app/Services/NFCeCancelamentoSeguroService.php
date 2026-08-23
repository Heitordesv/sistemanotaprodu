<?php

namespace App\Services;

use App\Models\ConfigNota;
use App\Models\Contigencia;
use App\Models\VendaCaixa;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapCurl;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use NFePHP\NFe\Factories\Contingency;
use NFePHP\NFe\Tools;
use RuntimeException;

class NFCeCancelamentoSeguroService
{
    public function cancelar(VendaCaixa $venda, string $motivo): array
    {
        $config = ConfigNota::query()
            ->where('empresa_id', (int) $venda->empresa_id)
            ->firstOrFail();

        $tools = $this->tools($config);
        $chave = preg_replace('/\D/', '', (string) $venda->chave);

        if (strlen($chave) !== 44) {
            throw new RuntimeException('A NFC-e não possui uma chave de acesso válida para cancelamento.');
        }

        // A consulta antes do evento torna o retry seguro e também recupera o
        // protocolo de autorização diretamente da SEFAZ, sem confiar em dado do browser.
        $consultaXml = $tools->sefazConsultaChave($chave);
        $consulta = (new Standardize($consultaXml))->toArray();
        $infProt = $consulta['protNFe']['infProt'] ?? [];
        $statusAtual = (string) ($infProt['cStat'] ?? '');

        // Se a própria consulta já informa documento cancelado, tratamos como
        // sucesso idempotente e não disparamos um segundo evento.
        if (in_array($statusAtual, ['101', '151', '155'], true)) {
            $retorno = [
                'retEvento' => [
                    'infEvento' => [
                        'cStat' => $statusAtual,
                        'xMotivo' => (string) ($infProt['xMotivo'] ?? 'Cancelamento já homologado.'),
                        'nProt' => $infProt['nProt'] ?? null,
                        'chNFe' => $chave,
                    ],
                ],
            ];

            return [
                'ok' => true,
                'data' => $retorno,
                'cstat' => $statusAtual,
                'protocolo' => $infProt['nProt'] ?? null,
                'mensagem' => (string) ($infProt['xMotivo'] ?? 'Cancelamento já homologado.'),
                'xml_salvo' => is_file(public_path('xml_nfce_cancelada/' . $chave . '.xml')),
                'ja_cancelada' => true,
            ];
        }

        $protocoloAutorizacao = (string) ($infProt['nProt'] ?? '');
        if ($protocoloAutorizacao === '') {
            throw new RuntimeException('A SEFAZ não retornou o protocolo de autorização desta NFC-e.');
        }

        $response = $tools->sefazCancela($chave, trim($motivo), $protocoloAutorizacao);
        $standardize = new Standardize($response);
        $std = $standardize->toStd();
        $arr = $standardize->toArray();

        if ((string) ($std->cStat ?? '') !== '128') {
            return [
                'ok' => false,
                'data' => $arr,
                'cstat' => (string) ($std->cStat ?? ''),
                'protocolo' => null,
                'mensagem' => (string) ($std->xMotivo ?? 'A SEFAZ recusou o cancelamento.'),
            ];
        }

        $infEvento = $arr['retEvento']['infEvento'] ?? [];
        $cStat = (string) ($infEvento['cStat'] ?? '');
        $mensagem = (string) ($infEvento['xMotivo'] ?? 'Retorno da SEFAZ sem descrição.');
        $protocoloEvento = $infEvento['nProt'] ?? null;

        if (!in_array($cStat, ['101', '135', '155'], true)) {
            return [
                'ok' => false,
                'data' => $arr,
                'cstat' => $cStat,
                'protocolo' => $protocoloEvento,
                'mensagem' => $mensagem,
            ];
        }

        $xmlSalvo = $this->salvarXmlCancelamento($tools, $response, $chave);

        return [
            'ok' => true,
            'data' => $arr,
            'cstat' => $cStat,
            'protocolo' => $protocoloEvento,
            'mensagem' => $mensagem,
            'xml_salvo' => $xmlSalvo,
            'ja_cancelada' => false,
        ];
    }

    private function tools(ConfigNota $config): Tools
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

    private function salvarXmlCancelamento(Tools $tools, string $response, string $chave): bool
    {
        $diretorio = public_path('xml_nfce_cancelada');
        if (!is_dir($diretorio) && !mkdir($diretorio, 0775, true) && !is_dir($diretorio)) {
            return false;
        }

        try {
            $xml = Complements::toAuthorize($tools->lastRequest, $response);
            return file_put_contents($diretorio . DIRECTORY_SEPARATOR . $chave . '.xml', $xml) !== false;
        } catch (\Throwable $e) {
            // O cancelamento fiscal já pode ter sido homologado; falha ao escrever
            // arquivo local não pode disparar um segundo cancelamento. O ledger
            // registrará a SEFAZ como concluída e permitirá reconciliação posterior.
            report($e);
            return false;
        }
    }
}
