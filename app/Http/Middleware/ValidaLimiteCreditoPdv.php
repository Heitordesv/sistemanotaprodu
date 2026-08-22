<?php

namespace App\Http\Middleware;

use App\Models\Cliente;
use App\Services\LimiteCreditoClienteService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ValidaLimiteCreditoPdv
{
    public function __construct(private LimiteCreditoClienteService $creditoService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->is('api/pdv/store')) {
            return $next($request);
        }

        $valorCrediario = $this->valorCrediarioSolicitado($request);
        if ($valorCrediario <= 0) {
            return $next($request);
        }

        $empresaId = (int) $request->input('empresa_id');
        $clienteId = (int) $request->input('cliente_id');

        if ($empresaId <= 0 || $clienteId <= 0) {
            return response()->json([
                'message' => 'Selecione um cliente para finalizar a venda no crediário.',
                'errors' => ['cliente_id' => ['Cliente obrigatório para venda no crediário.']],
            ], 422);
        }

        return DB::transaction(function () use ($request, $next, $empresaId, $clienteId, $valorCrediario) {
            $cliente = Cliente::query()
                ->where('id', $clienteId)
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->first();

            if (!$cliente) {
                return response()->json([
                    'message' => 'Cliente não encontrado para esta empresa.',
                    'errors' => ['cliente_id' => ['Cliente inválido.']],
                ], 422);
            }

            $resumo = $this->creditoService->resumo($cliente);

            if ($resumo['limite'] <= 0) {
                return response()->json([
                    'message' => 'Este cliente não possui limite de crédito cadastrado.',
                    'credito' => $resumo + ['solicitado' => round($valorCrediario, 2)],
                ], 422);
            }

            if (($resumo['utilizado'] + $valorCrediario) > ($resumo['limite'] + 0.009)) {
                return response()->json([
                    'message' => 'Venda não autorizada: o valor no crediário ultrapassa o limite disponível do cliente.',
                    'credito' => $resumo + ['solicitado' => round($valorCrediario, 2)],
                ], 422);
            }

            return $next($request);
        });
    }

    private function valorCrediarioSolicitado(Request $request): float
    {
        $tiposMistos = $request->input('tipo_pagamento_row', []);
        $valoresMistos = $request->input('valor_integral_row', []);

        if (is_array($tiposMistos) && count($tiposMistos) > 0) {
            $total = 0;
            foreach ($tiposMistos as $indice => $tipo) {
                if ((string) $tipo === '06') {
                    $total += $this->moedaParaFloat($valoresMistos[$indice] ?? 0);
                }
            }
            return round(max(0, $total), 2);
        }

        if ((string) $request->input('tipo_pagamento') !== '06') {
            return 0;
        }

        $subtotal = 0;
        foreach ((array) $request->input('subtotal_item', []) as $valor) {
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
}