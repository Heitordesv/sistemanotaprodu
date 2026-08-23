<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use Closure;

class HashEmpresa
{
    public function handle($request, Closure $next)
    {
        // O empresa_id recebido do cliente nunca é identidade de tenant.
        // Preferimos o header emitido pela sessão web e mantemos o campo hash
        // apenas por compatibilidade com clientes legados que já o enviavam.
        $hash = trim((string) ($request->header('X-Empresa-Hash') ?: $request->input('hash')));

        if ($hash === '') {
            return response()->json([
                'message' => 'Empresa não identificada.'
            ], 403);
        }

        $empresa = Empresa::query()
            ->where('hash', $hash)
            ->first();

        if (!$empresa) {
            return response()->json([
                'message' => 'Empresa não identificada.'
            ], 403);
        }

        $request->merge(['empresa_id' => (int) $empresa->id]);

        return $next($request);
    }
}
