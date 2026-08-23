<?php

namespace App\Services;

class NFCeConsultaCancelamentoParser
{
    private const TIPO_EVENTO_CANCELAMENTO = '110111';

    /**
     * cStats aceitos pelo próprio ecossistema NFePHP/DANFCE para evento de
     * cancelamento já homologado/vinculado. 151 aparece em retornos históricos
     * de situação e é mantido por compatibilidade.
     */
    private const CSTATS_EVENTO_CANCELADO = ['101', '135', '151', '155'];

    /**
     * Detecta cancelamento pela SITUAÇÃO da consulta e, principalmente, pelos
     * eventos protocolados. Não usa protNFe.infProt.cStat como prova de
     * cancelamento porque esse protocolo representa a autorização da nota.
     */
    public function detectar(array $consulta, string $chave): ?array
    {
        $chave = preg_replace('/\D/', '', $chave);

        $evento = $this->localizarEventoCancelamento($consulta, $chave);
        if ($evento !== null) {
            return $evento;
        }

        $cStatConsulta = (string) ($consulta['cStat'] ?? '');
        if (in_array($cStatConsulta, ['101', '151', '155'], true)) {
            return [
                'cstat' => $cStatConsulta,
                'mensagem' => (string) ($consulta['xMotivo'] ?? 'Cancelamento já homologado.'),
                'protocolo' => null,
                'chave' => $chave,
                'origem' => 'consulta',
            ];
        }

        return null;
    }

    private function localizarEventoCancelamento(array $node, string $chave): ?array
    {
        if ($this->pareceInfEvento($node)) {
            $tipoEvento = (string) ($node['tpEvento'] ?? '');
            $cStat = (string) ($node['cStat'] ?? '');
            $chaveEvento = preg_replace('/\D/', '', (string) ($node['chNFe'] ?? ''));

            if (
                $tipoEvento === self::TIPO_EVENTO_CANCELAMENTO
                && in_array($cStat, self::CSTATS_EVENTO_CANCELADO, true)
                && ($chaveEvento === '' || $chaveEvento === $chave)
            ) {
                return [
                    'cstat' => $cStat,
                    'mensagem' => (string) ($node['xMotivo'] ?? $node['xEvento'] ?? 'Cancelamento já homologado.'),
                    'protocolo' => $node['nProt'] ?? null,
                    'chave' => $chaveEvento !== '' ? $chaveEvento : $chave,
                    'origem' => 'evento',
                ];
            }
        }

        foreach ($node as $value) {
            if (!is_array($value)) {
                continue;
            }

            $evento = $this->localizarEventoCancelamento($value, $chave);
            if ($evento !== null) {
                return $evento;
            }
        }

        return null;
    }

    private function pareceInfEvento(array $node): bool
    {
        return array_key_exists('tpEvento', $node)
            && array_key_exists('cStat', $node);
    }
}
