<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ConfigNotaController;
use App\Http\Controllers\NaturezaController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\API\GraficoController;
use App\Http\Controllers\API\ProdutoController as ApiProdutoController;
use App\Services\FiscalTenantGuardService;
use Closure;
use Illuminate\Http\Request;

class ResolveFiscalWebTenantContext
{
    private const CONTROLLERS = [
        ProductController::class,
        NaturezaController::class,
        ConfigNotaController::class,
        ApiProdutoController::class,
        GraficoController::class,
    ];

    private const CONFIG_ACTIONS_WITH_RESOURCE = [
        'edit',
        'update',
        'destroy',
        'removeSenha',
    ];

    public function __construct(private FiscalTenantGuardService $guard)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        [$controller, $method] = $this->action($request);

        if (!in_array($controller, self::CONTROLLERS, true)) {
            return $next($request);
        }

        $empresaId = $this->guard->empresaIdDaSessao($request);
        $resourceId = $this->firstNumericRouteParameter($request);

        if ($resourceId !== null) {
            if ($controller === ProductController::class) {
                $this->guard->produto($empresaId, $resourceId);
            } elseif ($controller === NaturezaController::class) {
                $this->guard->natureza($empresaId, $resourceId);
            } elseif (
                $controller === ConfigNotaController::class &&
                in_array($method, self::CONFIG_ACTIONS_WITH_RESOURCE, true)
            ) {
                $this->guard->configNota($empresaId, $resourceId);
            }
        }

        return $next($request);
    }

    private function action(Request $request): array
    {
        $actionName = (string) optional($request->route())->getActionName();

        if (!str_contains($actionName, '@')) {
            return ['', ''];
        }

        return explode('@', $actionName, 2);
    }

    private function firstNumericRouteParameter(Request $request): ?int
    {
        foreach ((array) optional($request->route())->parameters() as $value) {
            if (is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }
}
