<?php

namespace App\Http\Middleware;

use App\Http\Controllers\API\NFCeController;
use App\Http\Controllers\API\NFeController;
use App\Http\Controllers\API\ProdutoController as ApiProdutoController;
use App\Http\Controllers\AppFiscal\ConfigEmitenteController as AppConfigEmitenteController;
use App\Http\Controllers\AppFiscal\NaturezaController as AppNaturezaController;
use App\Http\Controllers\AppFiscal\NfceAppController;
use App\Http\Controllers\AppFiscal\NotaFiscalAppController;
use App\Http\Controllers\AppFiscal\ProdutoController as AppProdutoController;
use App\Http\Controllers\AppFiscal\VendaCaixaController as AppVendaCaixaController;
use App\Http\Controllers\AppFiscal\VendaController as AppVendaController;
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

    /**
     * Ações utilitárias que não leem nem gravam qualquer dado empresarial.
     * Toda nova ação de Produto nasce protegida por padrão.
     */
    private const API_PRODUCT_TENANT_EXEMPT_METHODS = [
        'getBarcode',
        'linhaParcelaCompra',
    ];

    private const APP_CONTROLLERS = [
        AppProdutoController::class,
        AppConfigEmitenteController::class,
        AppNaturezaController::class,
        AppVendaController::class,
        AppVendaCaixaController::class,
        NotaFiscalAppController::class,
        NfceAppController::class,
    ];

    public function __construct(private FiscalTenantGuardService $guard)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        [$controller, $method] = $this->action($request);

        if (
            $controller === ApiProdutoController::class &&
            in_array($method, self::API_PRODUCT_TENANT_EXEMPT_METHODS, true)
        ) {
            return $next($request);
        }

        if (in_array($controller, self::HASH_CONTROLLERS, true)) {
            $empresaId = $this->guard->empresaIdPorHash($request);
            $this->validarRecursoFiscal($request, $controller, $empresaId);

            return $next($request);
        }

        if (in_array($controller, self::APP_CONTROLLERS, true)) {
            $empresaId = $this->guard->empresaIdPorTokenApp($request);
            $this->validarAppFiscal($request, $controller, $method, $empresaId);

            return $next($request);
        }

        return $next($request);
    }

    private function validarAppFiscal(Request $request, string $controller, string $method, int $empresaId): void
    {
        if ($controller === AppProdutoController::class) {
            $produtoId = $this->resourceId($request, ['id', 'product_id', 'produto_id']);

            if ($produtoId !== null) {
                $this->guard->produto($empresaId, $produtoId);
            }

            return;
        }

        if ($controller === AppConfigEmitenteController::class) {
            if ($method === 'salvar') {
                $this->validarNaturezaInput($request, $empresaId, 'nat_op_padrao');
            }

            return;
        }

        if ($controller === AppNaturezaController::class) {
            $naturezaId = $this->resourceId($request, ['id', 'natureza_id']);

            if ($naturezaId !== null) {
                $this->guard->natureza($empresaId, $naturezaId);
            }

            return;
        }

        if ($controller === AppVendaController::class) {
            $this->validarVendaApp($request, $method, $empresaId);
            return;
        }

        if ($controller === AppVendaCaixaController::class) {
            $this->validarVendaCaixaApp($request, $method, $empresaId);
            return;
        }

        if ($controller === NotaFiscalAppController::class) {
            $vendaId = $this->resourceId($request, ['venda_id', 'id']);

            if ($vendaId !== null) {
                $this->guard->venda($empresaId, $vendaId);
            }

            return;
        }

        if ($controller === NfceAppController::class) {
            $vendaCaixaId = $this->resourceId($request, ['venda_id', 'id']);

            if ($vendaCaixaId !== null) {
                $this->guard->vendaCaixa($empresaId, $vendaCaixaId);
            }
        }
    }

    private function validarVendaApp(Request $request, string $method, int $empresaId): void
    {
        if (in_array($method, ['salvar', 'salvarOrcamento'], true)) {
            $this->validarNaturezaInput($request, $empresaId, 'natureza');
            $this->guard->produtos($empresaId, $this->produtoIdsDosItens($request));
            return;
        }

        $vendaId = $this->resourceId($request, ['id', 'venda_id']);

        if ($vendaId !== null) {
            $this->guard->venda($empresaId, $vendaId);
        }
    }

    private function validarVendaCaixaApp(Request $request, string $method, int $empresaId): void
    {
        if ($method === 'salvar') {
            $this->guard->produtos($empresaId, $this->produtoIdsDosItens($request));
            $this->guard->naturezaPadraoDaConfig($empresaId);
            return;
        }

        $vendaCaixaId = $this->resourceId($request, ['id', 'venda_id']);

        if ($vendaCaixaId !== null) {
            $this->guard->vendaCaixa($empresaId, $vendaCaixaId);
        }
    }

    private function validarNaturezaInput(Request $request, int $empresaId, string $campo): void
    {
        $naturezaId = $request->input($campo);

        if ($this->positiveInteger($naturezaId)) {
            $this->guard->natureza($empresaId, (int) $naturezaId);
        }
    }

    private function produtoIdsDosItens(Request $request): array
    {
        return collect((array) $request->input('itens', []))
            ->map(fn ($item) => is_array($item) ? ($item['item_id'] ?? null) : null)
            ->filter(fn ($id) => $this->positiveInteger($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function validarRecursoFiscal(Request $request, string $controller, int $empresaId): void
    {
        $isProduto = $controller === ApiProdutoController::class;
        $resourceId = $this->resourceId(
            $request,
            $isProduto ? ['id', 'product_id', 'produto_id'] : ['id']
        );

        if ($resourceId === null) {
            return;
        }

        if ($controller === NFeController::class) {
            $this->guard->venda($empresaId, $resourceId);
        } elseif ($controller === NFCeController::class) {
            $this->guard->vendaCaixa($empresaId, $resourceId);
        } elseif ($isProduto) {
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

    private function resourceId(Request $request, array $candidateKeys = ['id']): ?int
    {
        foreach ($candidateKeys as $key) {
            $value = $request->input($key);
            if ($this->positiveInteger($value)) {
                return (int) $value;
            }
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
