<?php

namespace App\Services;

use App\Exceptions\ContaReceberPagamentoException;
use App\Models\AberturaCaixa;
use App\Models\ContaReceber;
use App\Models\ContaReceberPagamento;
use Illuminate\Support\Facades\DB;

class ContaReceberPagamentoService
{
    private const FORMAS_NAO_RECEBIMENTO = ['06', '90'];

    public function registrarMultiplos(ContaReceber $conta, array $pagamentos, string $dataPagamento, ?string $loteUuid = null): ContaReceber
    {
        return DB::transaction(function () use ($conta, $pagamentos, $dataPagamento, $loteUuid) {
            if ($loteUuid && $this->loteIndividualRegistrado(
                (int) $conta->id,
                (int) $conta->empresa_id,
                $loteUuid
            )) {
                return $this->marcarComoIdempotente(
                    ContaReceber::query()->whereKey($conta->id)->firstOrFail()
                );
            }

            $contexto = $this->resolverCaixaAbertoComLock(
                (int) $conta->empresa_id,
                $conta->filial_id ? (int) $conta->filial_id : null
            );

            $conta = ContaReceber::query()
                ->whereKey($conta->id)
                ->where('empresa_id', $contexto['empresa_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($loteUuid && $this->loteIndividualRegistrado(
                (int) $conta->id,
                (int) $conta->empresa_id,
                $loteUuid,
                true
            )) {
                return $this->marcarComoIdempotente($conta);
            }

            $this->validarFilialDaConta($conta, $contexto['abertura']);

            if ((int) $conta->status === 1 || (float) $conta->valor_recebido >= (float) $conta->valor_integral) {
                throw new ContaReceberPagamentoException('Esta conta já está quitada.');
            }

            $normalizados = [];
            $totalNovo = 0.0;

            foreach ($pagamentos as $pagamento) {
                $forma = trim((string) ($pagamento['forma_pagamento'] ?? ''));
                $valor = $this->converterValor($pagamento['valor'] ?? 0);
                $observacao = trim((string) ($pagamento['observacao'] ?? ''));

                if ($forma === '' || $valor <= 0) {
                    continue;
                }

                $this->validarFormaRecebimento($forma);

                $normalizados[] = [
                    'forma_pagamento' => $forma,
                    'valor' => $valor,
                    'observacao' => $observacao !== '' ? $observacao : null,
                ];
                $totalNovo += $valor;
            }

            if (!$normalizados || $totalNovo <= 0) {
                throw new ContaReceberPagamentoException('Informe pelo menos uma forma de pagamento com valor maior que zero.');
            }

            $recebidoAtual = round((float) $conta->valor_recebido, 2);
            $valorIntegral = round((float) $conta->valor_integral, 2);
            $saldoRestante = round(max(0, $valorIntegral - $recebidoAtual), 2);
            $totalNovo = round($totalNovo, 2);

            if ($totalNovo > $saldoRestante + 0.009) {
                throw new ContaReceberPagamentoException(
                    'O total informado (R$ ' . number_format($totalNovo, 2, ',', '.') .
                    ') é maior que o saldo restante (R$ ' . number_format($saldoRestante, 2, ',', '.') . ').'
                );
            }

            $recebidoEm = now();

            foreach ($normalizados as $pagamento) {
                ContaReceberPagamento::create([
                    'conta_receber_id' => $conta->id,
                    'empresa_id' => $conta->empresa_id,
                    'valor' => $pagamento['valor'],
                    'forma_pagamento' => $pagamento['forma_pagamento'],
                    'data_pagamento' => $dataPagamento,
                    'origem' => 'manual',
                    'provedor' => null,
                    'external_id' => null,
                    'lote_uuid' => $loteUuid,
                    'status' => 'confirmado',
                    'observacao' => $pagamento['observacao'],
                ]);

                $this->registrarHistoricoCaixa(
                    $conta,
                    $contexto,
                    $pagamento['valor'],
                    $pagamento['forma_pagamento'],
                    $recebidoEm
                );
            }

            $novoRecebido = round($recebidoAtual + $totalNovo, 2);
            $quitado = $novoRecebido >= ($valorIntegral - 0.009);

            $conta->valor_recebido = min($novoRecebido, $valorIntegral);
            $conta->status = $quitado ? 1 : 0;
            $conta->data_recebimento = $dataPagamento;
            $conta->tipo_pagamento = count($normalizados) > 1 ? '99' : $normalizados[0]['forma_pagamento'];
            $conta->received_by_user_id = $contexto['usuario_id'];
            $conta->abertura_caixa_id = $contexto['abertura']->id;
            $conta->received_at = $recebidoEm;
            $conta->saveQuietly();

            return $conta->fresh();
        });
    }

    public function registrarMassa(
        array $ids,
        int $empresaId,
        string $formaPagamento,
        string $dataPagamento,
        ?string $loteUuid = null
    ): array {
        $ids = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            throw new ContaReceberPagamentoException('Nenhuma conta válida foi selecionada.');
        }

        $formaPagamento = trim($formaPagamento);
        $this->validarFormaRecebimento($formaPagamento);

        return DB::transaction(function () use ($ids, $empresaId, $formaPagamento, $dataPagamento, $loteUuid) {
            if ($loteUuid && $this->loteMassaRegistrado($empresaId, $loteUuid)) {
                return ['quantidade' => 0, 'total' => 0.0, 'idempotente' => true];
            }

            $contexto = $this->resolverCaixaAbertoComLock($empresaId);

            $contas = ContaReceber::query()
                ->where('empresa_id', $empresaId)
                ->whereIn('id', $ids->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($contas->count() !== $ids->count()) {
                throw new ContaReceberPagamentoException('Uma ou mais contas selecionadas não pertencem à empresa atual ou não existem.');
            }

            if ($loteUuid && $this->loteMassaRegistrado($empresaId, $loteUuid, true)) {
                return ['quantidade' => 0, 'total' => 0.0, 'idempotente' => true];
            }

            $quantidade = 0;
            $total = 0.0;
            $recebidoEm = now();

            foreach ($contas as $conta) {
                $this->validarFilialDaConta($conta, $contexto['abertura']);

                $valorIntegral = round((float) $conta->valor_integral, 2);
                $recebidoAtual = round((float) $conta->valor_recebido, 2);
                $saldo = round(max(0, $valorIntegral - $recebidoAtual), 2);

                if ($saldo <= 0 || (int) $conta->status === 1) {
                    continue;
                }

                ContaReceberPagamento::create([
                    'conta_receber_id' => $conta->id,
                    'empresa_id' => $conta->empresa_id,
                    'valor' => $saldo,
                    'forma_pagamento' => $formaPagamento,
                    'data_pagamento' => $dataPagamento,
                    'origem' => 'manual',
                    'provedor' => null,
                    'external_id' => null,
                    'lote_uuid' => $loteUuid,
                    'status' => 'confirmado',
                    'observacao' => 'Recebimento em massa',
                ]);

                $this->registrarHistoricoCaixa(
                    $conta,
                    $contexto,
                    $saldo,
                    $formaPagamento,
                    $recebidoEm
                );

                $conta->valor_recebido = $valorIntegral;
                $conta->status = 1;
                $conta->data_recebimento = $dataPagamento;
                $conta->tipo_pagamento = $formaPagamento;
                $conta->received_by_user_id = $contexto['usuario_id'];
                $conta->abertura_caixa_id = $contexto['abertura']->id;
                $conta->received_at = $recebidoEm;
                $conta->saveQuietly();

                $quantidade++;
                $total += $saldo;
            }

            if ($quantidade === 0) {
                throw new ContaReceberPagamentoException('As contas selecionadas já estão quitadas.');
            }

            return [
                'quantidade' => $quantidade,
                'total' => round($total, 2),
                'idempotente' => false,
            ];
        });
    }

    private function resolverCaixaAbertoComLock(int $empresaId, ?int $filialId = null): array
    {
        $user = session('user_logged');

        if (!$user) {
            throw new ContaReceberPagamentoException('Usuário da sessão não identificado para registrar o recebimento.');
        }

        $empresaSessao = (int) (is_object($user)
            ? ($user->empresa_id ?? 0)
            : ($user['empresa'] ?? $user['empresa_id'] ?? 0));

        $usuarioId = (int) (is_object($user)
            ? ($user->id ?? 0)
            : ($user['id'] ?? $user['usuario_id'] ?? 0));

        if ($empresaSessao <= 0 || $usuarioId <= 0 || $empresaSessao !== $empresaId) {
            throw new ContaReceberPagamentoException('Empresa ou usuário da sessão inválido para registrar o recebimento.');
        }

        $query = AberturaCaixa::query()
            ->where('empresa_id', $empresaSessao)
            ->where('usuario_id', $usuarioId)
            ->where('status', 0);

        if ($filialId) {
            $query->where('filial_id', $filialId);
        }

        $abertura = $query
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if (!$abertura) {
            throw new ContaReceberPagamentoException('Nenhum caixa aberto foi encontrado. O caixa pode ter sido fechado por outra operação.');
        }

        return [
            'empresa_id' => $empresaSessao,
            'usuario_id' => $usuarioId,
            'abertura' => $abertura,
        ];
    }

    private function validarFilialDaConta(ContaReceber $conta, AberturaCaixa $abertura): void
    {
        if ($conta->filial_id && (int) $conta->filial_id !== (int) $abertura->filial_id) {
            throw new ContaReceberPagamentoException('A conta pertence a uma filial diferente do caixa atualmente aberto.');
        }
    }

    private function validarFormaRecebimento(string $formaPagamento): void
    {
        $formaPagamento = trim($formaPagamento);
        $formas = ContaReceber::tiposPagamento();

        if (
            $formaPagamento === ''
            || !array_key_exists($formaPagamento, $formas)
            || in_array($formaPagamento, self::FORMAS_NAO_RECEBIMENTO, true)
        ) {
            throw new ContaReceberPagamentoException('Forma de pagamento inválida para recebimento.');
        }
    }

    private function loteIndividualRegistrado(
        int $contaId,
        int $empresaId,
        string $loteUuid,
        bool $lockForUpdate = false
    ): bool {
        $query = ContaReceberPagamento::query()
            ->where('conta_receber_id', $contaId)
            ->where('empresa_id', $empresaId)
            ->where('lote_uuid', $loteUuid);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first(['id']) !== null;
    }

    private function loteMassaRegistrado(int $empresaId, string $loteUuid, bool $lockForUpdate = false): bool
    {
        $query = ContaReceberPagamento::query()
            ->where('empresa_id', $empresaId)
            ->where('lote_uuid', $loteUuid);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first(['id']) !== null;
    }

    private function marcarComoIdempotente(ContaReceber $conta): ContaReceber
    {
        $conta->setAttribute('recebimento_idempotente', true);

        return $conta;
    }

    private function registrarHistoricoCaixa(
        ContaReceber $conta,
        array $contexto,
        float $valor,
        string $formaPagamento,
        $recebidoEm
    ): void {
        DB::table('conta_receber_recebimentos')->insert([
            'conta_receber_id' => (int) $conta->id,
            'empresa_id' => (int) $conta->empresa_id,
            'abertura_caixa_id' => (int) $contexto['abertura']->id,
            'usuario_id' => (int) $contexto['usuario_id'],
            'valor' => round($valor, 7),
            'tipo_pagamento' => $formaPagamento,
            'received_at' => $recebidoEm,
            'created_at' => $recebidoEm,
            'updated_at' => $recebidoEm,
        ]);
    }

    private function converterValor($valor): float
    {
        if (is_numeric($valor)) {
            return round((float) $valor, 2);
        }

        $valor = trim((string) $valor);
        $valor = preg_replace('/[^0-9,.-]/', '', $valor);

        if (str_contains($valor, ',') && str_contains($valor, '.')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (str_contains($valor, ',')) {
            $valor = str_replace(',', '.', $valor);
        }

        return round((float) $valor, 2);
    }
}
