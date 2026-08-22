<?php

namespace App\Http\Middleware;

use App\Models\AberturaCaixa;
use App\Models\Filial;
use Closure;
use Illuminate\Http\Request;

class ResolveCashTenantContext
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $action = $route ? (string) $route->getActionName() : '';

        if (!$this->isCashAction($action)) {
            return $next($request);
        }

        $user = session('user_logged');

        if (!$user) {
            return redirect('/login')
                ->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $empresaId = (int) (is_object($user)
            ? ($user->empresa_id ?? 0)
            : ($user['empresa'] ?? $user['empresa_id'] ?? 0));

        $usuarioId = (int) (is_object($user)
            ? ($user->id ?? 0)
            : ($user['id'] ?? $user['usuario_id'] ?? 0));

        if ($empresaId <= 0 || $usuarioId <= 0) {
            abort(403, 'Contexto de autenticação inválido.');
        }

        // Tenant e operador nunca são aceitos do navegador no módulo de caixa.
        $request->merge([
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
        ]);

        $filialId = $this->normalizeFilialId($request->input('filial_id'));

        if ($filialId !== null) {
            $filialValida = Filial::query()
                ->whereKey($filialId)
                ->where('empresa_id', $empresaId)
                ->exists();

            if (!$filialValida) {
                abort(403, 'Filial inválida para a empresa autenticada.');
            }
        }

        if ($this->requiresOpenCash($action, $request)) {
            $abertura = AberturaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('status', 0)
                ->when(
                    $filialId !== null,
                    fn ($query) => $query->where('filial_id', $filialId)
                )
                ->orderByDesc('id')
                ->first();

            if (!$abertura) {
                return redirect()->route('caixa.index')
                    ->with('flash_erro', 'Abra o seu caixa antes de realizar esta operação.');
            }

            // A abertura encontrada no servidor é a fonte de verdade da sessão.
            $request->merge([
                'abertura_caixa_id' => (int) $abertura->id,
                'filial_id' => $abertura->filial_id,
            ]);
        }

        return $next($request);
    }

    private function isCashAction(string $action): bool
    {
        foreach ([
            'FrontBoxController@',
            'AberturaCaixaController@',
            'SangriaCaixaController@',
            'SuprimentoCaixaController@',
        ] as $controller) {
            if (str_contains($action, $controller)) {
                return true;
            }
        }

        return false;
    }

    private function requiresOpenCash(string $action, Request $request): bool
    {
        if (!$request->isMethod('post')) {
            return false;
        }

        return str_contains($action, 'FrontBoxController@store')
            || str_contains($action, 'SangriaCaixaController@store')
            || str_contains($action, 'SuprimentoCaixaController@store');
    }

    private function normalizeFilialId($value): ?int
    {
        if ($value === null || $value === '' || (int) $value === -1) {
            return null;
        }

        $value = (int) $value;

        return $value > 0 ? $value : null;
    }
}
