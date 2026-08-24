<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ConfigNotaController;
use App\Http\Controllers\DfeController;
use App\Http\Controllers\NaturezaController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\API\GraficoController;
use App\Http\Controllers\API\CategoriaController as ApiCategoriaController;
use App\Http\Controllers\API\NFCeController as ApiNFCeController;
use App\Http\Controllers\API\NFeController as ApiNFeController;
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
        DfeController::class,
        ApiCategoriaController::class,
        ApiProdutoController::class,
        ApiNFeController::class,
        ApiNFCeController::class,
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
        $resourceId = $this->resourceId($request);

        if ($resourceId !== null) {
            if ($controller === ProductController::class) {
                $this->guard->produto($empresaId, $resourceId);
            } elseif ($controller === ApiNFeController::class) {
                $this->guard->venda($empresaId, $resourceId);
            } elseif ($controller === ApiNFCeController::class) {
                $this->guard->vendaCaixa($empresaId, $resourceId);
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

    private function resourceId(Request $request): ?int
    {
        $inputId = $request->input('id');
        if (is_scalar($inputId) && ctype_digit((string) $inputId) && (int) $inputId > 0) {
            return (int) $inputId;
        }

        foreach ((array) optional($request->route())->parameters() as $value) {
            if (is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }
}
