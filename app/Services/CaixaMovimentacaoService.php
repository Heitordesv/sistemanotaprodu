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
            $abertura = AberturaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('status', 0)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$abertura && $caixaObrigatorio) {
                throw new CaixaMovimentacaoException(
                    'Nenhum caixa aberto foi encontrado. O caixa pode ter sido fechado por outra operação.'
                );
            }

            return $operacao($abertura);
        }, 3);
    }
}
