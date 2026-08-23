<?php

namespace Tests\Unit;

use App\Services\NFCeTaxReceiptTextService;
use PHPUnit\Framework\TestCase;

class NFCeTaxReceiptTextServiceTest extends TestCase
{
    public function test_formata_tributos_em_linhas_para_o_danfc_e(): void
    {
        $texto = (new NFCeTaxReceiptTextService())->formatar([
            'federal' => 12.34,
            'estadual' => 5.67,
            'municipal' => 0,
            'total' => 18.01,
            'icms' => 3.21,
            'pis' => 0.65,
            'cofins' => 3.00,
            'ibs' => 0,
            'cbs' => 0,
            'is' => 0,
        ], ['IBPT-26.1.A', 'IBPT-26.1.A']);

        $this->assertSame(
            'TRIBUTOS APROXIMADOS (Lei Federal 12.741/2012);' .
            'Federal: R$ 12,34;' .
            'Estadual: R$ 5,67;' .
            'Total aproximado: R$ 18,01;' .
            'TRIBUTOS DESTACADOS NA NFC-e;' .
            'ICMS: R$ 3,21;' .
            'PIS: R$ 0,65;' .
            'COFINS: R$ 3,00;' .
            'Fonte: IBPT IBPT-26.1.A',
            $texto
        );
    }

    public function test_exibe_total_zero_sem_inventar_impostos(): void
    {
        $texto = (new NFCeTaxReceiptTextService())->formatar([]);

        $this->assertSame(
            'TRIBUTOS APROXIMADOS (Lei Federal 12.741/2012);Total aproximado: R$ 0,00',
            $texto
        );
    }
}
