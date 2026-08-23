<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class PdvTotalService
{
    public function calcular(
        float $valorItens,
        array $dados,
        float $percentualMaximoDesconto = 0
    ): array {
        $valorItens = round(max(0, $valorItens), 2);

        $descontoTipo = $this->tipo($dados['desconto_tipo'] ?? 'fixo');
        $descontoEntrada = $this->numero(
            array_key_exists('desconto_valor', $dados)
                ? $dados['desconto_valor']
                : ($dados['desconto'] ?? 0)
        );
        $desconto = $this->valorAjuste(
            $descontoTipo,
            $descontoEntrada,
            $valorItens,
            'desconto'
        );

        $acrescimoTipo = $this->tipo($dados['acrescimo_tipo'] ?? 'fixo');
        $acrescimoEntrada = $this->numero(
            array_key_exists('acrescimo_valor', $dados)
                ? $dados['acrescimo_valor']
                : ($dados['acrescimo'] ?? 0)
        );
        $acrescimo = $this->valorAjuste(
            $acrescimoTipo,
            $acrescimoEntrada,
            $valorItens,
            'acrescimo'
        );

        $taxaEntrega = round(max(0, $this->numero($dados['taxa_entrega'] ?? 0)), 2);

        if ($percentualMaximoDesconto > 0) {
            $descontoMaximo = round(
                $valorItens * ($percentualMaximoDesconto / 100),
                2
            );

            if ($desconto > $descontoMaximo + 0.001) {
                throw ValidationException::withMessages([
                    'desconto' => [
                        'O desconto ultrapassa o limite de ' .
                        number_format($percentualMaximoDesconto, 2, ',', '.') .
                        '% configurado para a empresa.',
                    ],
                ]);
            }
        }

        $valorTotal = round(
            $valorItens + $taxaEntrega + $acrescimo - $desconto,
            2
        );

        if ($valorTotal < 0) {
            throw ValidationException::withMessages([
                'desconto' => [
                    'O desconto não pode ser maior que o valor da venda.',
                ],
            ]);
        }

        return [
            'desconto' => $desconto,
            'desconto_tipo' => $descontoTipo,
            'desconto_percentual' =>
                $descontoTipo === 'percentual' ? $descontoEntrada : null,
            'acrescimo' => $acrescimo,
            'acrescimo_tipo' => $acrescimoTipo,
            'acrescimo_percentual' =>
                $acrescimoTipo === 'percentual' ? $acrescimoEntrada : null,
            'taxa_entrega' => $taxaEntrega,
            'valor_total' => $valorTotal,
        ];
    }

    private function valorAjuste(
        string $tipo,
        float $entrada,
        float $base,
        string $campo
    ): float {
        $entrada = max(0, $entrada);

        if ($tipo === 'percentual' && $entrada > 100) {
            throw ValidationException::withMessages([
                $campo => ['A porcentagem deve ficar entre 0% e 100%.'],
            ]);
        }

        return round(
            $tipo === 'percentual'
                ? $base * ($entrada / 100)
                : $entrada,
            2
        );
    }

    private function tipo($tipo): string
    {
        return $tipo === 'percentual' ? 'percentual' : 'fixo';
    }

    private function numero($valor): float
    {
        if (is_int($valor) || is_float($valor)) {
            return (float) $valor;
        }

        $valor = preg_replace('/[^0-9,.-]/', '', trim((string) $valor));

        if ($valor === '') {
            return 0.0;
        }

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float) $valor : 0.0;
    }
}
