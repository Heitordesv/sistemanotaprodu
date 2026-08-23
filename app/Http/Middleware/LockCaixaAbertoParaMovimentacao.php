<?php

namespace App\Http\Middleware;

use App\Exceptions\CaixaMovimentacaoException;
use App\Models\AberturaCaixa;
use App\Services\CaixaMovimentacaoService;
use Closure;
use Illuminate\Http\Request;

class LockCaixaAbertoParaMovimentacao
{
    public function __construct(private CaixaMovimentacaoService $service)
    {
    }

    public function handle(Request $request, Closure $next, string $modo = 'obrigatorio')
    {
        // VendaController também salva orçamento pelo mesmo endpoint. Orçamento
        // não movimenta o caixa e portanto não precisa disputar o lock.
        if ($modo === 'venda-opcional' && (string) $request->input('type') !== 'venda') {
            return $next($request);
        }

        $user = session('user_logged');
        $empresaId = (int) (is_object($user)
            ? ($user->empresa_id ?? 0)
            : ($user['empresa'] ?? $user['empresa_id'] ?? 0));
        $usuarioId = (int) (is_object($user)
            ? ($user->id ?? 0)
            : ($user['id'] ?? $user['usuario_id'] ?? 0));

        // A NFe pode existir fora de uma sessão de caixa. Quando houver uma
        // abertura ativa, ela é bloqueada; se o fechamento vencer primeiro, a
        // venda passa a ser uma venda fora de caixa e não contamina o fechamento.
        $caixaObrigatorio = $modo !== 'venda-opcional';

        try {
            return $this->service->executar(
                $empresaId,
                $usuarioId,
                function (?AberturaCaixa $abertura) use ($request, $next) {
                    if ($abertura) {
                        // Nunca aceitar abertura_caixa_id enviada pelo navegador.
                        $request->merge(['abertura_caixa_id' => (int) $abertura->id]);
                        $request->attributes->set('abertura_caixa_bloqueada', $abertura);
                    }

                    return $next($request);
                },
                $caixaObrigatorio
            );
        } catch (CaixaMovimentacaoException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 409);
            }

            return redirect()->back()->with('flash_erro', $e->getMessage());
        }
    }
}
