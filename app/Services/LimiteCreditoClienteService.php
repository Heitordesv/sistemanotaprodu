<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ContaReceber;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class LimiteCreditoClienteService
{
    private const TIPO_PAGAMENTO_CREDIARIO = '06';

    public function buscarClienteComCredito(int $clienteId, ?int $empresaId = null): Cliente
    {
        $query = Cliente::with('cidade')->where('id', $clienteId);

        if ($empresaId !== null && $empresaId > 0) {
            $query->where('empresa_id', $empresaId);
        }

        $cliente = $query->firstOrFail();
        $cliente->setAttribute('credito', $this->resumo($cliente));

        return $cliente;
    }

    /**
     * Valida o limite dentro da mesma transação que grava a venda no PDV.
     *
     * Em pagamentos múltiplos, soma todas as linhas cujo tipo é crediário (06),
     * independentemente da quantidade de parcelas geradas para cada pagamento.
     */
    public function validarVendaPdv(Request $request): array
    {
        $valorCrediario = $this->valorCrediarioSolicitado($request);

        if ($valorCrediario <= 0) {
            return [
                'aplica_limite' => false,
                'solicitado' => 0.0,
            ];
        }

        $empresaId = (int) $request->input('empresa_id');
        $clienteId = (int) $request->input('cliente_id');

        if ($empresaId <= 0 || $clienteId <= 0) {
            $this->bloquearVenda(
                'Selecione um cliente para finalizar a venda no crediário.',
                ['cliente_id' => ['Cliente obrigatório para venda no crediário.']]
            );
        }

        $cliente = Cliente::query()
            ->where('id', $clienteId)
            ->where('empresa_id', $empresaId)
            ->lockForUpdate()
            ->first();

        if (!$cliente) {
            $this->bloquearVenda(
                'Cliente não encontrado para esta empresa.',
                ['cliente_id' => ['Cliente inválido.']]
            );
        }

        $resumo = $this->resumo($cliente);
        $credito = $resumo + ['solicitado' => round($valorCrediario, 2)];

        if ($resumo['limite'] <= 0) {
            $this->bloquearVenda(
                'Este cliente não possui limite de crédito cadastrado.',
                [],
                $credito
            );
        }

        if (!$this->possuiLimiteDisponivel($resumo, $valorCrediario)) {
            $this->bloquearVenda(
                'Venda não autorizada: as parcelas do crediário ultrapassam o limite disponível do cliente.',
                [],
                $credito
            );
        }

        return $credito + ['aplica_limite' => true];
    }

    public function resumo(Cliente $cliente): array
    {
        $limite = max(0, (float) $cliente->limite_venda);

        $resumoContas = ContaReceber::query()
            ->where('conta_recebers.empresa_id', $cliente->empresa_id)
            ->where('conta_recebers.status', 0)
            ->where(function ($query) use ($cliente) {
                $query->where('conta_recebers.cliente_id', $cliente->id)
                    ->orWhereExists(function ($subQuery) use ($cliente) {
                        $subQuery->select(DB::raw(1))
                            ->from('vendas')
                            ->whereColumn('vendas.id', 'conta_recebers.venda_id')
                            ->where('vendas.cliente_id', $cliente->id)
                            ->where('vendas.empresa_id', $cliente->empresa_id);
                    });
            })
            ->selectRaw(
                'COALESCE(SUM(GREATEST('
                . 'COALESCE(valor_integral, 0) + COALESCE(juros, 0) + COALESCE(multa, 0) '
                . '- COALESCE(valor_recebido, 0), 0)), 0) AS total'
            )
            ->first();

        $contasEmAberto = (float) ($resumoContas->total ?? 0);

        // Mantém compatibilidade com vendas antigas lançadas em credito_vendas.
        // Se a venda já possuir conta a receber aberta, ela não é somada novamente.
        $creditoLegado = (float) DB::table('credito_vendas as cv')
            ->join('vendas as v', 'v.id', '=', 'cv.venda_id')
            ->where('cv.empresa_id', $cliente->empresa_id)
            ->where('cv.cliente_id', $cliente->id)
            ->where('cv.status', 0)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('conta_recebers as cr')
                    ->whereColumn('cr.venda_id', 'v.id')
                    ->where('cr.status', 0);
            })
            ->sum('v.valor_total');

        $utilizado = round($contasEmAberto + $creditoLegado, 2);
        $disponivel = round(max(0, $limite - $utilizado), 2);

        return [
            'limite' => round($limite, 2),
            'utilizado' => $utilizado,
            'disponivel' => $disponivel,
        ];
    }

    private function possuiLimiteDisponivel(array $resumo, float $valorCrediario): bool
    {
        return ($resumo['utilizado'] + $valorCrediario) <= ($resumo['limite'] + 0.009);
    }

    private function valorCrediarioSolicitado(Request $request): float
    {
        $tiposMistos = Arr::wrap($request->input('tipo_pagamento_row', []));
        $valoresMistos = Arr::wrap($request->input('valor_integral_row', []));

        if ($tiposMistos !== []) {
            $total = 0.0;

            foreach ($tiposMistos as $indice => $tipo) {
                if ((string) $tipo !== self::TIPO_PAGAMENTO_CREDIARIO) {
                    continue;
                }

                $total += max(0, $this->moedaParaFloat($valoresMistos[$indice] ?? 0));
            }

            return round($total, 2);
        }

        if ((string) $request->input('tipo_pagamento') !== self::TIPO_PAGAMENTO_CREDIARIO) {
            return 0;
        }

        $subtotal = 0;

        foreach (Arr::wrap($request->input('subtotal_item', [])) as $valor) {
            $subtotal += $this->moedaParaFloat($valor);
        }

        $subtotal += $this->moedaParaFloat($request->input('acrescimo', 0));
        $subtotal -= $this->moedaParaFloat($request->input('desconto', 0));

        return round(max(0, $subtotal), 2);
    }

    private function moedaParaFloat($valor): float
    {
        if (is_int($valor) || is_float($valor)) {
            return (float) $valor;
        }

        $valor = trim((string) $valor);

        if ($valor === '') {
            return 0;
        }

        $valor = preg_replace('/[^0-9,.-]/', '', $valor);

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float) $valor : 0;
    }

    private function bloquearVenda(
        string $mensagem,
        array $erros = [],
        ?array $credito = null
    ): void {
        $dados = ['message' => $mensagem];

        if ($erros !== []) {
            $dados['errors'] = $erros;
        }

        if ($credito !== null) {
            $dados['credito'] = $credito;
        }

        throw new HttpResponseException(response()->json($dados, 422));
    }
}