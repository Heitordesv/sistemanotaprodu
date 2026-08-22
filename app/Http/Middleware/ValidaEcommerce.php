<?php

namespace App\Http\Middleware;

use App\Models\ConfigEcommerce;
use Closure;

class ValidaEcommerce
{
    public function handle($request, Closure $next)
    {
        $link = $request->route('link');

        if (!$link) {
            return redirect('/lojainexistente');
        }

        $config = ConfigEcommerce::where('link', strtolower($link))->first();

        if (!$config) {
            return redirect('/lojainexistente');
        }

        if ($config->usar_api) {
            return redirect('/habilitadoApi');
        }

        $empresaId = (int) $config->empresa_id;

        $usuarioLoja = session('user_ecommerce');
        if ($usuarioLoja && (int) ($usuarioLoja['empresa_id'] ?? 0) !== $empresaId) {
            session()->forget('user_ecommerce');
        }

        $usuarioTemporario = session('user_temp');
        if ($usuarioTemporario && (int) ($usuarioTemporario['empresa_id'] ?? 0) !== $empresaId) {
            session()->forget('user_temp');
        }

        session(['ecommerce_empresa_id' => $empresaId]);
        $request->attributes->set('ecommerce_config', $config);
        $request->attributes->set('ecommerce_empresa_id', $empresaId);

        return $next($request);
    }
}