<?php

namespace Tests\Unit;

use App\Services\PdvTotalService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PdvTotalServiceTest extends TestCase
{
    private PdvTotalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PdvTotalService();
    }

    public function test_calcula_taxa_desconto_e_acrescimo_fixos(): void
    {
        $resultado = $this->service->calcular(100, [
            'desconto_tipo' => 'fixo',
            'desconto_valor' => 5,
            'acrescimo_tipo' => 'fixo',
            'acrescimo_valor' => 10,
            'taxa_entrega' => 15,
        ]);

        $this->assertSame(5.0, $resultado['desconto']);
        $this->assertSame(10.0, $resultado['acrescimo']);
        $this->assertSame(15.0, $resultado['taxa_entrega']);
        $this->assertSame(120.0, $resultado['valor_total']);
    }

    public function test_calcula_percentuais_sobre_subtotal_dos_produtos(): void
    {
        $resultado = $this->service->calcular(100, [
            'desconto_tipo' => 'percentual',
            'desconto_valor' => '10,00',
            'acrescimo_tipo' => 'percentual',
            'acrescimo_valor' => 20,
            'taxa_entrega' => 12,
        ]);

        $this->assertSame(10.0, $resultado['desconto']);
        $this->assertSame(20.0, $resultado['acrescimo']);
        $this->assertSame(122.0, $resultado['valor_total']);
        $this->assertSame(10.0, $resultado['desconto_percentual']);
        $this->assertSame(20.0, $resultado['acrescimo_percentual']);
    }

    public function test_mantem_compatibilidade_com_cliente_antigo(): void
    {
        $resultado = $this->service->calcular(100, [
            'desconto' => 7,
            'acrescimo' => 3,
        ]);

        $this->assertSame('fixo', $resultado['desconto_tipo']);
        $this->assertSame('fixo', $resultado['acrescimo_tipo']);
        $this->assertSame(96.0, $resultado['valor_total']);
    }

    public function test_bloqueia_desconto_acima_do_limite_da_empresa(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->calcular(100, [
            'desconto_tipo' => 'percentual',
            'desconto_valor' => 11,
        ], 10);
    }

    public function test_bloqueia_percentual_acima_de_cem(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->calcular(100, [
            'acrescimo_tipo' => 'percentual',
            'acrescimo_valor' => 101,
        ]);
    }
}
