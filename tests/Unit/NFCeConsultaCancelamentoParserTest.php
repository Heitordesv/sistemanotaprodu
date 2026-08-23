<?php

namespace Tests\Unit;

use App\Services\NFCeConsultaCancelamentoParser;
use PHPUnit\Framework\TestCase;

class NFCeConsultaCancelamentoParserTest extends TestCase
{
    private string $chave = '35260812345678000199650010000001231000001234';

    public function test_protocolo_de_autorizacao_100_nao_e_tratado_como_cancelamento(): void
    {
        $consulta = [
            'cStat' => '100',
            'xMotivo' => 'Autorizado o uso da NF-e',
            'protNFe' => [
                'infProt' => [
                    'cStat' => '100',
                    'chNFe' => $this->chave,
                    'nProt' => '135260000000001',
                ],
            ],
        ];

        $this->assertNull((new NFCeConsultaCancelamentoParser())->detectar($consulta, $this->chave));
    }

    public function test_encontra_cancelamento_110111_em_proc_evento_mesmo_com_protocolo_de_autorizacao_100(): void
    {
        $consulta = [
            'cStat' => '100',
            'protNFe' => [
                'infProt' => [
                    'cStat' => '100',
                    'chNFe' => $this->chave,
                    'nProt' => '135260000000001',
                ],
            ],
            'procEventoNFe' => [
                'retEvento' => [
                    'infEvento' => [
                        'tpEvento' => '110111',
                        'cStat' => '135',
                        'xMotivo' => 'Evento registrado e vinculado a NF-e',
                        'chNFe' => $this->chave,
                        'nProt' => '135260000000999',
                    ],
                ],
            ],
        ];

        $resultado = (new NFCeConsultaCancelamentoParser())->detectar($consulta, $this->chave);

        $this->assertNotNull($resultado);
        $this->assertSame('135', $resultado['cstat']);
        $this->assertSame('135260000000999', $resultado['protocolo']);
        $this->assertSame('evento', $resultado['origem']);
    }

    public function test_ignora_evento_que_nao_e_cancelamento(): void
    {
        $consulta = [
            'cStat' => '100',
            'procEventoNFe' => [
                'retEvento' => [
                    'infEvento' => [
                        'tpEvento' => '110110',
                        'cStat' => '135',
                        'chNFe' => $this->chave,
                    ],
                ],
            ],
        ];

        $this->assertNull((new NFCeConsultaCancelamentoParser())->detectar($consulta, $this->chave));
    }

    public function test_aceita_status_geral_de_cancelamento_sem_reusar_protocolo_de_autorizacao(): void
    {
        $consulta = [
            'cStat' => '101',
            'xMotivo' => 'Cancelamento de NF-e homologado',
            'protNFe' => [
                'infProt' => [
                    'cStat' => '100',
                    'nProt' => '135260000000001',
                ],
            ],
        ];

        $resultado = (new NFCeConsultaCancelamentoParser())->detectar($consulta, $this->chave);

        $this->assertNotNull($resultado);
        $this->assertSame('101', $resultado['cstat']);
        $this->assertNull($resultado['protocolo']);
        $this->assertSame('consulta', $resultado['origem']);
    }
}
