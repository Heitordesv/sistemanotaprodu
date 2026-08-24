<?php

namespace App\Services;

use App\Models\VendaCaixa;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdvReceiptPdfService
{
    public function breakdown(VendaCaixa $venda): array
    {
        $venda->loadMissing(['itens.produto', 'fatura', 'usuario']);

        $subtotal = (float) $venda->itens->sum(function ($item) {
            return (float) $item->valor * (float) $item->quantidade;
        });

        return [
            'subtotal' => round($subtotal, 2),
            'taxa_entrega' => round((float) ($venda->taxa_entrega ?? 0), 2),
            'acrescimo' => round((float) ($venda->acrescimo ?? 0), 2),
            'desconto' => round((float) ($venda->desconto ?? 0), 2),
            'total' => round((float) $venda->valor_total, 2),
            'acrescimo_label' => $this->adjustmentLabel(
                'Acréscimo',
                $venda->acrescimo_tipo,
                $venda->acrescimo_percentual
            ),
            'desconto_label' => $this->adjustmentLabel(
                'Desconto',
                $venda->desconto_tipo,
                $venda->desconto_percentual
            ),
        ];
    }

    public function render(VendaCaixa $venda, $config, ?string $logo = null, int $paperWidth = 80): string
    {
        $paperWidth = max(58, min(120, $paperWidth));
        $totals = $this->breakdown($venda);
        $paymentRows = max(1, $venda->fatura->count());
        $heightMm = 105 + ($venda->itens->count() * 8) + ($paymentRows * 5);

        $html = view('frontBox.receipt', compact(
            'venda',
            'config',
            'logo',
            'totals',
            'paperWidth'
        ))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper([
            0,
            0,
            $this->millimetersToPoints($paperWidth),
            $this->millimetersToPoints($heightMm),
        ]);
        $pdf->render();

        return $pdf->output();
    }

    private function adjustmentLabel(string $label, ?string $type, $percentage): string
    {
        if ($type !== 'percentual' || $percentage === null || $percentage === '') {
            return $label;
        }

        $formatted = rtrim(rtrim(number_format((float) $percentage, 2, ',', ''), '0'), ',');

        return sprintf('%s (%s%%)', $label, $formatted);
    }

    private function millimetersToPoints(float $millimeters): float
    {
        return $millimeters * 72 / 25.4;
    }
}
