<?php

namespace Tests\Unit;

use App\Models\ItemVendaCaixa;
use App\Models\VendaCaixa;
use App\Services\PdvReceiptPdfService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class PdvReceiptPdfServiceTest extends TestCase
{
    public function test_breakdown_separates_delivery_addition_discount_and_total(): void
    {
        $venda = new VendaCaixa([
            'valor_total' => 122,
            'taxa_entrega' => 20,
            'acrescimo' => 12,
            'acrescimo_tipo' => 'percentual',
            'acrescimo_percentual' => 12,
            'desconto' => 10,
            'desconto_tipo' => 'percentual',
            'desconto_percentual' => 10,
        ]);
        $firstItem = new ItemVendaCaixa(['quantidade' => 2, 'valor' => 25]);
        $firstItem->setRelation('produto', null);
        $secondItem = new ItemVendaCaixa(['quantidade' => 1, 'valor' => 50]);
        $secondItem->setRelation('produto', null);
        $venda->setRelation('itens', new Collection([$firstItem, $secondItem]));
        $venda->setRelation('fatura', new Collection());
        $venda->setRelation('usuario', null);

        $totals = app(PdvReceiptPdfService::class)->breakdown($venda);

        $this->assertSame(100.0, $totals['subtotal']);
        $this->assertSame(20.0, $totals['taxa_entrega']);
        $this->assertSame(12.0, $totals['acrescimo']);
        $this->assertSame(10.0, $totals['desconto']);
        $this->assertSame(122.0, $totals['total']);
        $this->assertSame('Acréscimo (12%)', $totals['acrescimo_label']);
        $this->assertSame('Desconto (10%)', $totals['desconto_label']);
    }

    public function test_fixed_adjustments_do_not_show_a_percentage(): void
    {
        $venda = new VendaCaixa([
            'valor_total' => 55,
            'taxa_entrega' => 10,
            'acrescimo' => 0,
            'acrescimo_tipo' => 'fixo',
            'desconto' => 0,
            'desconto_tipo' => 'fixo',
        ]);
        $item = new ItemVendaCaixa(['quantidade' => 1, 'valor' => 45]);
        $item->setRelation('produto', null);
        $venda->setRelation('itens', new Collection([$item]));
        $venda->setRelation('fatura', new Collection());
        $venda->setRelation('usuario', null);

        $totals = app(PdvReceiptPdfService::class)->breakdown($venda);

        $this->assertSame(45.0, $totals['subtotal']);
        $this->assertSame(10.0, $totals['taxa_entrega']);
        $this->assertSame(55.0, $totals['total']);
        $this->assertSame('Acréscimo', $totals['acrescimo_label']);
        $this->assertSame('Desconto', $totals['desconto_label']);
    }
}
