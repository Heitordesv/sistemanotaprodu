<?php

namespace App\Services;

use App\Exceptions\CaixaMovimentacaoException;
use App\Models\AberturaCaixa;
use App\Models\Venda;
use Closure;
use Illuminate\Support\Facades\DB;

class VendaCaixaEdicaoService
{
    /**
     * Executa alteração/destruição de uma NFe enquanto mantém o mesmo lock da
     * AberturaCaixa disputado pelo fechamento.
     *
     * Ordem única de locks: AberturaCaixa -> Venda. Se a edição vencer, o
     * fechamento espera e consolida a versão nova. Se o fechamento vencer, a
     * edição acorda, revalida status=1 e é recusada.
     */
    public function executar(int $vendaId, int $empresaId, Closure $operacao)
    {
        if ($vendaId <= 0 || $empresaId <= 0) {
            throw new CaixaMovimentacaoException('Venda ou empresa inválida para edição.');
        }

        return DB::transaction(function () use ($vendaId, $empresaId, $operacao) {
            // Leitura inicial apenas para descobrir qual abertura deve ser
            // bloqueada. A venda é relida com FOR UPDATE somente depois do lock
            // da abertura, preservando a ordem global e evitando deadlocks.
            $snapshot = Venda::query()
                ->whereKey($vendaId)
                ->where('empresa_id', $empresaId)
                ->firstOrFail(['id', 'empresa_id', 'usuario_id', 'abertura_caixa_id']);

            $aberturaId = (int) ($snapshot->abertura_caixa_id ?? 0);
            $vinculoLegado = false;

            if ($aberturaId <= 0) {
                $aberturaId = $this->resolverAberturaLegada($snapshot);
                $vinculoLegado = $aberturaId > 0;
            }

            if ($aberturaId > 0) {
                $abertura = AberturaCaixa::query()
                    ->whereKey($aberturaId)
                    ->where('empresa_id', $empresaId)
                    ->lockForUpdate()
                    ->first();

                if (!$abertura) {
                    throw new CaixaMovimentacaoException(
                        'A sessão de caixa vinculada a esta venda não foi encontrada.'
                    );
                }

                $venda = Venda::query()
                    ->whereKey($vendaId)
                    ->where('empresa_id', $empresaId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->validarVinculoDepoisDoLock($venda, $abertura, $vinculoLegado);

                if ((int) $abertura->status !== 0) {
                    throw new CaixaMovimentacaoException(
                        'Esta venda pertence a um caixa já fechado e não pode mais ser alterada.'
                    );
                }

                $resultado = $operacao($venda, $abertura);

                // VendaController legado sobrescreve usuario_id com o usuário
                // que está editando. Isso não pode fazer uma venda sumir do
                // resumo do operador original. Revalidamos antes do commit e,
                // se qualquer vínculo financeiro mudou, toda a edição volta.
                $this->validarInvariantesDepoisDaOperacao(
                    $vendaId,
                    $empresaId,
                    $abertura,
                    $vinculoLegado
                );

                return $resultado;
            }

            // Venda realmente fora de caixa: ainda bloqueamos a própria linha
            // para evitar duas edições concorrentes dentro deste fluxo seguro.
            $venda = Venda::query()
                ->whereKey($vendaId)
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) ($venda->abertura_caixa_id ?? 0) > 0) {
                throw new CaixaMovimentacaoException(
                    'O vínculo de caixa da venda mudou durante a edição. Tente novamente.'
                );
            }

            $resultado = $operacao($venda, null);

            $vendaAtual = Venda::query()
                ->whereKey($vendaId)
                ->where('empresa_id', $empresaId)
                ->first();

            if ($vendaAtual && (int) ($vendaAtual->abertura_caixa_id ?? 0) > 0) {
                throw new CaixaMovimentacaoException(
                    'Uma venda fora de caixa não pode receber vínculo de caixa durante a edição.'
                );
            }

            return $resultado;
        }, 3);
    }

    /**
     * Compatibilidade com vendas antigas, criadas antes de abertura_caixa_id.
     * O CaixaResumoService ainda as associa por primeira/última venda; portanto
     * elas também precisam respeitar a imutabilidade de um fechamento antigo.
     */
    private function resolverAberturaLegada(Venda $venda): int
    {
        $vendaId = (int) $venda->id;
        $empresaId = (int) $venda->empresa_id;
        $usuarioId = (int) $venda->usuario_id;

        $fechadaId = (int) (AberturaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where('status', 1)
            ->where('primeira_venda_nfe', '<', $vendaId)
            ->where('ultima_venda_nfe', '>=', $vendaId)
            ->orderByDesc('id')
            ->value('id') ?? 0);

        if ($fechadaId > 0) {
            return $fechadaId;
        }

        return (int) (AberturaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where('status', 0)
            ->where('primeira_venda_nfe', '<', $vendaId)
            ->orderByDesc('id')
            ->value('id') ?? 0);
    }

    private function validarVinculoDepoisDoLock(
        Venda $venda,
        AberturaCaixa $abertura,
        bool $vinculoLegado
    ): void {
        if ((int) $abertura->empresa_id !== (int) $venda->empresa_id
            || (int) $abertura->usuario_id !== (int) $venda->usuario_id) {
            throw new CaixaMovimentacaoException(
                'A venda possui um vínculo de caixa inconsistente e a alteração foi bloqueada.'
            );
        }

        if (!$vinculoLegado) {
            if ((int) ($venda->abertura_caixa_id ?? 0) !== (int) $abertura->id) {
                throw new CaixaMovimentacaoException(
                    'O vínculo de caixa da venda mudou durante a edição. Tente novamente.'
                );
            }

            return;
        }

        if ($venda->abertura_caixa_id !== null) {
            throw new CaixaMovimentacaoException(
                'O vínculo de caixa da venda mudou durante a edição. Tente novamente.'
            );
        }

        $vendaId = (int) $venda->id;
        $primeira = (int) $abertura->primeira_venda_nfe;
        $pertence = $vendaId > $primeira;

        if ((int) $abertura->status !== 0) {
            $pertence = $pertence && $vendaId <= (int) $abertura->ultima_venda_nfe;
        }

        if (!$pertence) {
            throw new CaixaMovimentacaoException(
                'A associação histórica da venda com o caixa mudou durante a edição. Tente novamente.'
            );
        }
    }

    private function validarInvariantesDepoisDaOperacao(
        int $vendaId,
        int $empresaId,
        AberturaCaixa $abertura,
        bool $vinculoLegado
    ): void {
        // DELETE intencional é permitido enquanto o caixa está aberto. Nesse
        // caso não existe linha para revalidar; o fechamento posterior verá a
        // exclusão porque ainda está esperando o lock da mesma abertura.
        $venda = Venda::query()
            ->whereKey($vendaId)
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$venda) {
            return;
        }

        if ((int) $venda->usuario_id !== (int) $abertura->usuario_id) {
            throw new CaixaMovimentacaoException(
                'A edição tentou alterar o operador histórico de uma venda vinculada ao caixa.'
            );
        }

        if ($vinculoLegado) {
            if ($venda->abertura_caixa_id !== null) {
                throw new CaixaMovimentacaoException(
                    'A edição tentou alterar o vínculo histórico de caixa da venda.'
                );
            }

            return;
        }

        if ((int) ($venda->abertura_caixa_id ?? 0) !== (int) $abertura->id) {
            throw new CaixaMovimentacaoException(
                'A edição tentou alterar o vínculo histórico de caixa da venda.'
            );
        }
    }
}
