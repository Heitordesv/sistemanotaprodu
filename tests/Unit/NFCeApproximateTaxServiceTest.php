<?php

namespace Tests\Unit;

use App\Services\NFCeApproximateTaxService;
use PHPUnit\Framework\TestCase;

class NFCeApproximateTaxServiceTest extends TestCase
{
    public function test_total_formatado_e_exatamente_a_soma_dos_itens(): void
    {
        $service = new NFCeApproximateTaxService();

        $itens = [
            $service->calcularItem(33.33, 12.34, 17.89, 0.57),
            $service->calcularItem(19.99, 12.34, 17.89, 0.57),
            $service->calcularItem(0.05, 17, 12, 0),
        ];

        $totalCentavos = array_sum(array_column($itens, 'total_centavos'));
        $somaItensFormatados = array_sum(array_map(
            fn (array $item) => (float) $service->formatarCentavos($item['total_centavos']),
            $itens
        ));

        $this->assertSame(
            $service->formatarCentavos($totalCentavos),
            number_format($somaItensFormatados, 2, '.', '')
        );
    }

    public function test_arredonda_componentes_em_centavos_antes_de_somar_item(): void
    {
        $tributos = (new NFCeApproximateTaxService())->calcularItem(0.05, 17, 12, 0);

        $this->assertSame(1, $tributos['federal_centavos']);
        $this->assertSame(1, $tributos['estadual_centavos']);
        $this->assertSame(2, $tributos['total_centavos']);
    }
}
