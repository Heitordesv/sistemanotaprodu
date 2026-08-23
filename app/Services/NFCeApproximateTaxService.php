<?php

namespace App\Services;

class NFCeApproximateTaxService
{
    public function calcularItem(
        float $valorProduto,
        float $aliquotaFederal,
        float $aliquotaEstadual,
        float $aliquotaMunicipal
    ): array {
        $federalCentavos = $this->calcularCentavos($valorProduto, $aliquotaFederal);
        $estadualCentavos = $this->calcularCentavos($valorProduto, $aliquotaEstadual);
        $municipalCentavos = $this->calcularCentavos($valorProduto, $aliquotaMunicipal);

        return [
            'federal_centavos' => $federalCentavos,
            'estadual_centavos' => $estadualCentavos,
            'municipal_centavos' => $municipalCentavos,
            'total_centavos' => $federalCentavos + $estadualCentavos + $municipalCentavos,
        ];
    }

    public function formatarCentavos(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }

    private function calcularCentavos(float $valorProduto, float $aliquota): int
    {
        return (int) round($valorProduto * $aliquota, 0, PHP_ROUND_HALF_UP);
    }
}
