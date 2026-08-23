<?php

namespace App\Http\Middleware;

use App\Http\Controllers\API\NFCeController;
use App\Http\Controllers\API\NFeController;
use App\Http\Controllers\API\ProdutoController as ApiProdutoController;
use App\Http\Controllers\AppFiscal\ProdutoController as AppProdutoController;
use App\Services\FiscalTenantGuardService;
use Closure;
use Illuminate\Http\Request;

class ResolveFiscalApiTenantContext
{
    private const HASH_CONTROLLERS = [
        NFeController::class,
        NFCeController::class,
        ApiProdutoController::class,
    ];

    public function __construct(private FiscalTenantGuardService $guard)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        [$controller] = $this->action($request);

        if (in_array($controller, self::HASH_CONTROLLERS, true)) {
            $empresaId = $this->guard->empresaIdPorHash($request);
            $this->validarRecursoFiscal($request, $controller, $empresaId);

            return $next($request);
        }

        if ($controller === AppProdutoController::class) {
            $empresaId = $this->guard->empresaIdPorTokenApp($request);
            $produtoId = $this->resourceId($request);

            if ($produtoId !== null) {
                $this->guard->produto($empresaId, $produtoId);
            }
        }

        return $next($request);
    }

    private function validarRecursoFiscal(Request $request, string $controller, int $empresaId): void
    {
        $resourceId = $this->resourceId($request);

        if ($resourceId === null) {
            return;
        }

        if ($controller === NFeController::class) {
            $this->guard->venda($empresaId, $resourceId);
        } elseif ($controller === NFCeController::class) {
            $this->guard->vendaCaixa($empresaId, $resourceId);
        } elseif ($controller === ApiProdutoController::class) {
            $this->guard->produto($empresaId, $resourceId);
        }
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
        if ($this->positiveInteger($inputId)) {
            return (int) $inputId;
        }

        foreach ((array) optional($request->route())->parameters() as $value) {
            if ($this->positiveInteger($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    private function positiveInteger($value): bool
    {
        return is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0;
    }
}
