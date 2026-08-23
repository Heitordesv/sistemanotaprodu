<?php

namespace App\Services;

use App\Models\AberturaCaixa;
use App\Models\ComissaoVenda;
use App\Models\ContaReceber;
use App\Models\FaturaFrenteCaixa;
use App\Models\PdvDevolucao;
use App\Models\SangriaCaixa;
use App\Models\Usuario;
use App\Models\VendaCaixa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PdvDevolucaoFinanceiroService
{
    private const STATUS_CANCELADO_POR_DEVOLUCAO = 2;

    public function validarPreCondicoes(VendaCaixa $venda, Usuario $operador): array
    {
        $snapshot = $this->snapshot($venda, false);
        $this->validarLiquidacoes($snapshot);
        $this->validarCaixaParaReembolso($venda, $operador, $snapshot['valor_dinheiro'], false);

        return $snapshot;
    }

    public function processar(
        VendaCaixa $venda,
        PdvDevolucao $devolucao,
        Usuario $operador,
        bool $fiscalJaCancelado = false
    ): array {
        $snapshot = $this->snapshot($venda, true);

        try {
            $this->validarLiquidacoes($snapshot);
        } catch (ValidationException $e) {
            if (!$fiscalJaCancelado) {
                throw $e;
            }

            return [
                'pendente' => true,
                'motivo_pendencia' => $e->getMessage(),
                'snapshot' => $snapshot,
                'abertura_original_id' => $this->aberturaOriginal($venda)?->id,
                'abertura_compensacao_id' => null,
                'valor_dinheiro' => (float) $snapshot['valor_dinheiro'],
            ];
        }

        $caixa = $this->validarCaixaParaReembolso(
            $venda,
            $operador,
            (float) $snapshot['valor_dinheiro'],
            $fiscalJaCancelado
        );

        if ($caixa['pendente']) {
            return [
                'pendente' => true,
                'motivo_pendencia' => $caixa['motivo_pendencia'],
                'snapshot' => $snapshot,
                'abertura_original_id' => $caixa['abertura_original_id'],
                'abertura_compensacao_id' => null,
                'valor_dinheiro' => (float) $snapshot['valor_dinheiro'],
            ];
        }

        $this->estornarPendencias($venda, $devolucao, $operador, $snapshot);

        $aberturaCompensacaoId = null;
        if ($caixa['criar_sangria']) {
            /** @var AberturaCaixa $aberturaAtual */
            $aberturaAtual = $caixa['abertura_atual'];

            SangriaCaixa::create([
                'usuario_id' => (int) $aberturaAtual->usuario_id,
                'valor' => round((float) $snapshot['valor_dinheiro'], 2),
                'empresa_id' => (int) $venda->empresa_id,
                'abertura_caixa_id' => (int) $aberturaAtual->id,
                'observacao' => mb_substr(sprintf(
                    'DEVOLUÇÃO VENDA #%d - compensação do caixa original #%s. Operador: %s.',
                    $venda->id,
                    $caixa['abertura_original_id'] ?: 'legado',
                    $operador->nome
                ), 0, 255),
            ]);

            $aberturaCompensacaoId = (int) $aberturaAtual->id;
        }

        return [
            'pendente' => false,
            'motivo_pendencia' => null,
            'snapshot' => $snapshot,
            'abertura_original_id' => $caixa['abertura_original_id'],
            'abertura_compensacao_id' => $aberturaCompensacaoId,
            'valor_dinheiro' => (float) $snapshot['valor_dinheiro'],
        ];
    }

    private function estornarPendencias(
        VendaCaixa $venda,
        PdvDevolucao $devolucao,
        Usuario $operador,
        array $snapshot
    ): void {
        $this->garantirColunasAuditoria();

        $auditoria = [
            'status' => self::STATUS_CANCELADO_POR_DEVOLUCAO,
            'pdv_devolucao_id' => (int) $devolucao->id,
            'cancelado_em' => now(),
            'cancelado_por_usuario_id' => (int) $operador->id,
            'updated_at' => now(),
        ];

        if (!empty($snapshot['conta_ids'])) {
            ContaReceber::query()
                ->where('empresa_id', (int) $venda->empresa_id)
                ->whereIn('id', $snapshot['conta_ids'])
                ->where('valor_recebido', '<=', 0)
                ->where('status', 0)
                ->update($auditoria);
        }

        if (!empty($snapshot['comissao_ids'])) {
            ComissaoVenda::query()
                ->where('empresa_id', (int) $venda->empresa_id)
                ->whereIn('id', $snapshot['comissao_ids'])
                ->where('status', 0)
                ->update($auditoria);
        }
    }

    private function garantirColunasAuditoria(): void
    {
        foreach (['conta_recebers', 'comissao_vendas'] as $tabela) {
            if (!Schema::hasTable($tabela)) {
                continue;
            }

            foreach (['pdv_devolucao_id', 'cancelado_em', 'cancelado_por_usuario_id'] as $coluna) {
                if (!Schema::hasColumn($tabela, $coluna)) {
                    throw ValidationException::withMessages([
                        'financeiro' => 'A estrutura de auditoria da devolução financeira ainda não foi aplicada. Execute as migrations antes de processar devoluções.',
                    ]);
                }
            }
        }
    }

    private function snapshot(VendaCaixa $venda, bool $lock): array
    {
        $contasQuery = ContaReceber::query()
            ->where('empresa_id', (int) $venda->empresa_id)
            ->where('venda_caixa_id', (int) $venda->id);

        $comissoesQuery = ComissaoVenda::query()
            ->where('empresa_id', (int) $venda->empresa_id)
            ->where('venda_id', (int) $venda->id)
            ->where('tabela', 'venda_caixas');

        $faturasQuery = FaturaFrenteCaixa::query()
            ->where('venda_caixa_id', (int) $venda->id);

        if ($lock) {
            $contasQuery->lockForUpdate();
            $comissoesQuery->lockForUpdate();
            $faturasQuery->lockForUpdate();
        }

        $contas = $contasQuery->get();
        $comissoes = $comissoesQuery->get();
        $faturas = $faturasQuery->get();

        $historicoRecebido = 0.0;
        $contaIds = $contas->pluck('id')->map(fn ($id) => (int) $id)->values();

        if ($contaIds->isNotEmpty() && Schema::hasTable('conta_receber_recebimentos')) {
            $historicoRecebido = (float) DB::table('conta_receber_recebimentos')
                ->where('empresa_id', (int) $venda->empresa_id)
                ->whereIn('conta_receber_id', $contaIds->all())
                ->sum('valor');
        }

        $valorDinheiro = 0.0;
        if ($faturas->isNotEmpty()) {
            $valorDinheiro = (float) $faturas
                ->filter(fn ($fatura) => str_pad(trim((string) $fatura->forma_pagamento), 2, '0', STR_PAD_LEFT) === '01')
                ->sum('valor');
        } elseif (str_pad((string) $venda->tipo_pagamento, 2, '0', STR_PAD_LEFT) === '01') {
            $valorDinheiro = (float) $venda->valor_total;
        }

        return [
            'conta_ids' => $contaIds->all(),
            'comissao_ids' => $comissoes->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'valor_recebido' => (float) $contas->sum('valor_recebido'),
            'historico_recebido' => $historicoRecebido,
            'comissao_liquidada' => $comissoes->contains(fn ($comissao) => (int) $comissao->status === 1),
            'valor_dinheiro' => round(max(0, $valorDinheiro), 2),
            'contas' => $contas->map(fn ($conta) => [
                'id' => (int) $conta->id,
                'valor_integral' => (float) $conta->valor_integral,
                'valor_recebido' => (float) $conta->valor_recebido,
                'status' => $conta->status,
                'tipo_pagamento' => $conta->tipo_pagamento,
            ])->values()->all(),
            'comissoes' => $comissoes->map(fn ($comissao) => [
                'id' => (int) $comissao->id,
                'funcionario_id' => (int) $comissao->funcionario_id,
                'valor' => (float) $comissao->valor,
                'status' => $comissao->status,
            ])->values()->all(),
            'faturas' => $faturas->map(fn ($fatura) => [
                'id' => (int) $fatura->id,
                'forma_pagamento' => (string) $fatura->forma_pagamento,
                'valor' => (float) $fatura->valor,
            ])->values()->all(),
        ];
    }

    private function validarLiquidacoes(array $snapshot): void
    {
        if ((float) $snapshot['valor_recebido'] > 0.00001 || (float) $snapshot['historico_recebido'] > 0.00001) {
            throw ValidationException::withMessages([
                'financeiro' => 'Esta venda possui conta a receber já liquidada, total ou parcialmente. O recebimento precisa ser estornado/reembolsado por um fluxo financeiro auditável antes da devolução.',
            ]);
        }

        if ($snapshot['comissao_liquidada']) {
            throw ValidationException::withMessages([
                'financeiro' => 'Esta venda possui comissão já liquidada. Estorne a comissão antes de concluir a devolução.',
            ]);
        }
    }

    private function validarCaixaParaReembolso(
        VendaCaixa $venda,
        Usuario $operador,
        float $valorDinheiro,
        bool $permitirPendente
    ): array {
        $original = $this->aberturaOriginal($venda);
        $originalId = $original ? (int) $original->id : null;

        if ($valorDinheiro <= 0.00001) {
            return [
                'pendente' => false,
                'motivo_pendencia' => null,
                'criar_sangria' => false,
                'abertura_original_id' => $originalId,
                'abertura_atual' => null,
            ];
        }

        // A autorização devolve como solicitante o usuário logado que executa a
        // devolução. É exatamente o mesmo usuário usado pelo middleware de caixa
        // para localizar a AberturaCaixa; não existe uma segunda tradução de ID.
        $usuarioCaixaId = (int) $operador->id;

        if ($original && (int) $original->status === 0) {
            if ((int) $original->usuario_id !== $usuarioCaixaId) {
                throw ValidationException::withMessages([
                    'caixa' => 'A venda pertence a outro caixa que ainda está aberto. Faça a devolução no mesmo caixa ou após o fechamento para não misturar gavetas.',
                ]);
            }

            return [
                'pendente' => false,
                'motivo_pendencia' => null,
                'criar_sangria' => false,
                'abertura_original_id' => $originalId,
                'abertura_atual' => $original,
            ];
        }

        $aberturaAtual = AberturaCaixa::query()
            ->where('empresa_id', (int) $venda->empresa_id)
            ->where('usuario_id', $usuarioCaixaId)
            ->where('status', 0)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if (!$aberturaAtual) {
            $mensagem = 'Abra um caixa antes de devolver uma venda em dinheiro de um caixa já fechado; o reembolso precisa ser registrado na gaveta atual.';

            if ($permitirPendente) {
                return [
                    'pendente' => true,
                    'motivo_pendencia' => $mensagem,
                    'criar_sangria' => false,
                    'abertura_original_id' => $originalId,
                    'abertura_atual' => null,
                ];
            }

            throw ValidationException::withMessages(['caixa' => $mensagem]);
        }

        return [
            'pendente' => false,
            'motivo_pendencia' => null,
            'criar_sangria' => true,
            'abertura_original_id' => $originalId,
            'abertura_atual' => $aberturaAtual,
        ];
    }

    private function aberturaOriginal(VendaCaixa $venda): ?AberturaCaixa
    {
        if ((int) ($venda->abertura_caixa_id ?? 0) > 0) {
            return AberturaCaixa::query()
                ->where('id', (int) $venda->abertura_caixa_id)
                ->where('empresa_id', (int) $venda->empresa_id)
                ->first();
        }

        return AberturaCaixa::query()
            ->where('empresa_id', (int) $venda->empresa_id)
            ->where('usuario_id', (int) $venda->usuario_id)
            ->where('created_at', '<=', $venda->created_at)
            ->where(function ($query) use ($venda) {
                $query->where('status', 0)
                    ->orWhere('updated_at', '>=', $venda->created_at);
            })
            ->orderByDesc('id')
            ->first();
    }
}
