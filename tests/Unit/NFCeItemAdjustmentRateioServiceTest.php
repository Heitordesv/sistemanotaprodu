<?php

namespace Tests\Unit;

use App\Services\NFCeItemAdjustmentRateioService;
use InvalidArgumentException;
use Tests\TestCase;

class NFCeItemAdjustmentRateioServiceTest extends TestCase
{
    private NFCeItemAdjustmentRateioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NFCeItemAdjustmentRateioService();
    }

    public function test_rateia_cenario_do_bloqueador_sem_desconto_explodir(): void
    {
        $itens = $this->service->ratear([50, 50], 90, 0, 20);

        $this->assertEquals(45.0, $itens[0]['desconto']);
        $this->assertEquals(45.0, $itens[1]['desconto']);
        $this->assertEquals(10.0, $itens[0]['frete']);
        $this->assertEquals(10.0, $itens[1]['frete']);
        $this->assertSame(90.0, $this->soma($itens, 'desconto'));
        $this->assertSame(20.0, $this->soma($itens, 'frete'));
        $this->assertSame(30.0, 100.0 + 20.0 - 90.0);
    }

    public function test_rateia_frete_desconto_e_acrescimo_com_ajuste_de_centavos(): void
    {
        $itens = $this->service->ratear(
            [33.33, 33.33, 33.34],
            10.01,
            5.02,
            7.01
        );

        $this->assertSame(10.01, $this->soma($itens, 'desconto'));
        $this->assertSame(5.02, $this->soma($itens, 'acrescimo'));
        $this->assertSame(7.01, $this->soma($itens, 'frete'));
        $this->assertSame(102.02, 100 + 7.01 + 5.02 - 10.01);
    }

    public function test_desconto_alto_nunca_supera_o_valor_do_item(): void
    {
        $valores = [0.01, 0.01, 0.01];
        $itens = $this->service->ratear($valores, 0.02, 0, 0);

        foreach ($itens as $indice => $item) {
            $this->assertLessThanOrEqual(
                $valores[$indice],
                $item['desconto']
            );
        }

        $this->assertSame(0.02, $this->soma($itens, 'desconto'));
    }

    public function test_rateio_mantem_identidade_do_total_fiscal(): void
    {
        $valores = [19.99, 30.01, 50.00];
        $itens = $this->service->ratear($valores, 99.99, 12.34, 8.76);

        $vProd = array_sum($valores);
        $vDesc = $this->soma($itens, 'desconto');
        $vOutro = $this->soma($itens, 'acrescimo');
        $vFrete = $this->soma($itens, 'frete');
        $vNF = round($vProd + $vFrete + $vOutro - $vDesc, 2);

        $this->assertSame(99.99, $vDesc);
        $this->assertSame(12.34, $vOutro);
        $this->assertSame(8.76, $vFrete);
        $this->assertSame(21.11, $vNF);
    }

    public function test_bloqueia_desconto_maior_que_os_produtos(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->ratear([50, 50], 100.01, 0, 0);
    }

    private function soma(array $itens, string $campo): float
    {
        return round(array_sum(array_column($itens, $campo)), 2);
    }
}
