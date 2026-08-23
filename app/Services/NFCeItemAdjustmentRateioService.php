<?php

namespace App\Services;

use InvalidArgumentException;

class NFCeItemAdjustmentRateioService
{
    public function ratear(
        array $valoresProdutos,
        float $desconto,
        float $acrescimo,
        float $frete
    ): array {
        $valoresCentavos = array_map(
            fn ($valor) => max(0, (int) round((float) $valor * 100)),
            array_values($valoresProdutos)
        );

        if ($valoresCentavos === []) {
            return [];
        }

        $subtotalCentavos = array_sum($valoresCentavos);
        $descontoCentavos = max(0, (int) round($desconto * 100));
        $acrescimoCentavos = max(0, (int) round($acrescimo * 100));
        $freteCentavos = max(0, (int) round($frete * 100));

        if (
            $subtotalCentavos <= 0 &&
            ($descontoCentavos + $acrescimoCentavos + $freteCentavos) > 0
        ) {
            throw new InvalidArgumentException(
                'Não é possível ratear ajustes fiscais sem subtotal de produtos.'
            );
        }

        if ($descontoCentavos > $subtotalCentavos) {
            throw new InvalidArgumentException(
                'O desconto fiscal não pode superar o subtotal dos produtos.'
            );
        }

        $descontos = $this->ratearCentavos(
            $descontoCentavos,
            $valoresCentavos,
            true
        );
        $acrescimos = $this->ratearCentavos(
            $acrescimoCentavos,
            $valoresCentavos
        );
        $fretes = $this->ratearCentavos(
            $freteCentavos,
            $valoresCentavos
        );

        return array_map(
            fn ($indice) => [
                'desconto' => $descontos[$indice] / 100,
                'acrescimo' => $acrescimos[$indice] / 100,
                'frete' => $fretes[$indice] / 100,
            ],
            array_keys($valoresCentavos)
        );
    }

    private function ratearCentavos(
        int $totalCentavos,
        array $pesosCentavos,
        bool $limitarAoProduto = false
    ): array {
        $quantidade = count($pesosCentavos);
        $rateio = array_fill(0, $quantidade, 0);

        if ($totalCentavos === 0 || $quantidade === 0) {
            return $rateio;
        }

        $subtotalCentavos = array_sum($pesosCentavos);
        if ($subtotalCentavos <= 0) {
            return $rateio;
        }

        $acumulado = 0;
        $ultimo = $quantidade - 1;

        for ($indice = 0; $indice < $ultimo; $indice++) {
            $valor = intdiv(
                $totalCentavos * $pesosCentavos[$indice],
                $subtotalCentavos
            );
            $rateio[$indice] = $valor;
            $acumulado += $valor;
        }

        $rateio[$ultimo] = $totalCentavos - $acumulado;

        if ($limitarAoProduto && $rateio[$ultimo] > $pesosCentavos[$ultimo]) {
            $excesso = $rateio[$ultimo] - $pesosCentavos[$ultimo];
            $rateio[$ultimo] = $pesosCentavos[$ultimo];

            for ($indice = $ultimo - 1; $indice >= 0 && $excesso > 0; $indice--) {
                $capacidade = $pesosCentavos[$indice] - $rateio[$indice];
                $ajuste = min($capacidade, $excesso);
                $rateio[$indice] += $ajuste;
                $excesso -= $ajuste;
            }

            if ($excesso > 0) {
                throw new InvalidArgumentException(
                    'Não foi possível distribuir o desconto entre os produtos.'
                );
            }
        }

        return $rateio;
    }
}
