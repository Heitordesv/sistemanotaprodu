<?php

namespace App\Services;

use App\Exceptions\CaixaMovimentacaoException;
use App\Models\AberturaCaixa;
use Closure;
use Illuminate\Support\Facades\DB;

class CaixaMovimentacaoService
{
    /**
     * Executa uma movimentação enquanto mantém o lock da abertura do caixa.
     *
     * A ordem de concorrência é única para todo o financeiro:
     * AberturaCaixa -> registros específicos da operação.
     * O CaixaFechamentoService disputa a mesma linha com lockForUpdate().
     */
    public function executar(
        int $empresaId,
        int $usuarioId,
        Closure $operacao,
        bool $caixaObrigatorio = true
    ) {
        if ($empresaId <= 0 || $usuarioId <= 0) {
            throw new CaixaMovimentacaoException('Empresa ou usuário da sessão não identificado.');
        }

        return DB::transaction(function () use ($empresaId, $usuarioId, $operacao, $caixaObrigatorio) {
            // Resolve a abertura candidata sem range lock e depois bloqueia
            // exclusivamente sua PK. Assim, fechamento e movimentação disputam
            // a mesma linha sem introduzir next-key locks sobre status/usuário.
            $candidata = AberturaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('status', 0)
                ->orderByDesc('id')
                ->value('id');

            if (!$candidata) {
                if ($caixaObrigatorio) {
                    throw new CaixaMovimentacaoException(
                        'Nenhum caixa aberto foi encontrado. O caixa pode ter sido fechado por outra operação.'
                    );
                }

                return $operacao(null);
            }

            $abertura = AberturaCaixa::query()
                ->whereKey((int) $candidata)
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->lockForUpdate()
                ->first();

            // Revalida depois de adquirir o lock. Se o fechamento venceu a
            // corrida, não migra silenciosamente a operação para um caixa novo.
            if (!$abertura || (int) $abertura->status !== 0) {
                if ($caixaObrigatorio) {
                    throw new CaixaMovimentacaoException(
                        'Nenhum caixa aberto foi encontrado. O caixa pode ter sido fechado por outra operação.'
                    );
                }

                return $operacao(null);
            }

            return $operacao($abertura);
        }, 3);
    }
}
