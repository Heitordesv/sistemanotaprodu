<?php

namespace App\Services;

class NFCeTaxReceiptTextService
{
    public function formatar(array $totais, array $fontesIbpt = []): string
    {
        $linhas = ['TRIBUTOS APROXIMADOS (Lei Federal 12.741/2012)'];

        foreach ([
            'federal' => 'Federal',
            'estadual' => 'Estadual',
            'municipal' => 'Municipal',
        ] as $campo => $rotulo) {
            if (($totais[$campo] ?? 0) > 0) {
                $linhas[] = $rotulo . ': R$ ' . $this->moeda($totais[$campo]);
            }
        }

        $linhas[] = 'Total aproximado: R$ ' . $this->moeda($totais['total'] ?? 0);

        $destacados = [];
        foreach ([
            'icms' => 'ICMS',
            'pis' => 'PIS',
            'cofins' => 'COFINS',
            'ibs' => 'IBS',
            'cbs' => 'CBS',
            'is' => 'IS',
        ] as $campo => $rotulo) {
            if (($totais[$campo] ?? 0) > 0) {
                $destacados[] = $rotulo . ': R$ ' . $this->moeda($totais[$campo]);
            }
        }

        if ($destacados !== []) {
            $linhas[] = 'TRIBUTOS DESTACADOS NA NFC-e';
            array_push($linhas, ...$destacados);
        }

        $fontesIbpt = array_values(array_unique(array_filter(array_map(
            fn ($fonte) => trim((string) $fonte),
            $fontesIbpt
        ))));

        if ($fontesIbpt !== []) {
            $linhas[] = 'Fonte: IBPT ' . implode(', ', $fontesIbpt);
        }

        // O DANFC-e do NFePHP converte ponto e vírgula em quebra de linha.
        return implode(';', $linhas);
    }

    private function moeda($valor): string
    {
        return number_format((float) $valor, 2, ',', '.');
    }
}
