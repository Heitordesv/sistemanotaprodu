<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminArea
{
    /**
     * Prefixos exclusivos da area administrativa Master/Super.
     * Qualquer subrota abaixo destes prefixos tambem fica protegida.
     *
     * @var array<int, string>
     */
    private array $protectedPrefixes = [
        'dashboard',
        'empresas',
        'planos',
        'planosPendentes',
        'leads',
        'emails',
        'ticketsSuper',
        'relatorioSuper',
        'pesquisa',
        'alertas',
        'representantes',
        'consulta-multa',
        'consultar-veiculo',
        'veiculo',
        'ibpt',
        'cidades',
        'etiquetas',
        'videos',
        'errosLog',
    ];

    public function handle(Request $request, Closure $next)
    {
        $prefix = (string) $request->segment(1);

        if (!in_array($prefix, $this->protectedPrefixes, true)) {
            return $next($request);
        }

        $user = session('user_logged');

        if (!$user) {
            return redirect('/login');
        }

        if (!($user['super'] ?? false)) {
            abort(403, 'Acesso restrito ao administrador do sistema.');
        }

        return $next($request);
    }
}