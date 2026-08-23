<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Services\FiscalTenantGuardService;
use Closure;

class HashEmpresa
{
    public function handle($request, Closure $next)
    {
        $tenantVerificado = (int) $request->attributes->get(
            FiscalTenantGuardService::VERIFIED_TENANT_ATTRIBUTE,
            0
        );

        // Quando um middleware anterior já resolveu o tenant por credencial
        // confiável, nenhum hash/body posterior pode sobrescrevê-lo.
        if ($tenantVerificado > 0) {
            $request->merge(['empresa_id' => $tenantVerificado]);
            return $next($request);
        }

        // O empresa_id recebido do cliente nunca é identidade de tenant.
        // Preferimos o header emitido pela sessão web e mantemos o campo hash
        // apenas por compatibilidade com clientes legados que já o enviavam.
        $hash = trim((string) ($request->header('X-Empresa-Hash') ?: $request->input('hash')));

        if ($hash === '') {
            return response()->json([
                'message' => 'Empresa não identificada.'
            ], 403);
        }

        $empresaId = Empresa::query()
            ->where('hash', $hash)
            ->value('id');

        if (!$empresaId) {
            return response()->json([
                'message' => 'Empresa não identificada.'
            ], 403);
        }

        $empresaId = (int) $empresaId;
        $request->merge(['empresa_id' => $empresaId]);
        $request->attributes->set(
            FiscalTenantGuardService::VERIFIED_TENANT_ATTRIBUTE,
            $empresaId
        );

        return $next($request);
    }
}
