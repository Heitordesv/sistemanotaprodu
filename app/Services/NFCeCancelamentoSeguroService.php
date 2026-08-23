<?php

namespace App\Services;

use App\Models\ConfigNota;
use App\Models\VendaCaixa;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use NFePHP\NFe\Tools;
use RuntimeException;

class NFCeCancelamentoSeguroService
{
    public function __construct(
        private NFCeConsultaCancelamentoParser $cancelamentoParser,
        private NFCeToolsFactory $toolsFactory
    ) {
    }

    public function cancelar(VendaCaixa $venda, string $motivo): array
    {
        $config = ConfigNota::query()
            ->where('empresa_id', (int) $venda->empresa_id)
            ->firstOrFail();

        $tools = $this->toolsFactory->make($config);
        $chave = preg_replace('/\D/', '', (string) $venda->chave);

        if (strlen($chave) !== 44) {
            throw new RuntimeException('A NFC-e não possui uma chave de acesso válida para cancelamento.');
        }

        // Consulta a situação atual antes de qualquer novo evento. protNFe é usado
        // somente para recuperar o protocolo de autorização; a detecção de um
        // cancelamento anterior é feita pelo status da consulta e pelos eventos
        // tpEvento=110111 retornados em procEventoNFe/retEvento.
        $consultaXml = $tools->sefazConsultaChave($chave);
        $consulta = (new Standardize($consultaXml))->toArray();

        $cancelamentoExistente = $this->cancelamentoParser->detectar($consulta, $chave);
        if ($cancelamentoExistente !== null) {
            $cStat = (string) $cancelamentoExistente['cstat'];
            $mensagem = (string) $cancelamentoExistente['mensagem'];
            $protocolo = $cancelamentoExistente['protocolo'] ?? null;

            return [
                'ok' => true,
                'data' => [
                    'retEvento' => [
                        'infEvento' => [
                            'cStat' => $cStat,
                            'xMotivo' => $mensagem,
                            'nProt' => $protocolo,
                            'chNFe' => $chave,
                            'tpEvento' => '110111',
                        ],
                    ],
                ],
                'cstat' => $cStat,
                'protocolo' => $protocolo,
                'mensagem' => $mensagem,
                'xml_salvo' => is_file(public_path('xml_nfce_cancelada/' . $chave . '.xml')),
                'ja_cancelada' => true,
            ];
        }

        $infProt = $consulta['protNFe']['infProt'] ?? [];
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
            report($e);
            return false;
        }
    }
}
