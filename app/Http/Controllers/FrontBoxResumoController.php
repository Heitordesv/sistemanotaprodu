<?php

namespace App\Http\Controllers;

use App\Services\CaixaResumoService;
use App\Services\VendaTenantGuardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontBoxResumoController extends FrontBoxController
{
    public function __construct(
        private CaixaResumoService $caixaResumoService,
        private VendaTenantGuardService $tenantGuard
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $response = parent::index($request);

        if (!$response instanceof View) {
            return $response;
        }

        $dadosView = $response->getData();
        $abertura = $dadosView['abertura'] ?? null;

        if (!$abertura) {
            return $response;
        }

        $resumo = $this->caixaResumoService->resumir($abertura);
        $vendasPdv = collect($resumo['vendas'])
            ->filter(fn ($venda) => (string) ($venda->tipo ?? '') === 'PDV')
            ->values();

        // A tela principal do PDV passa a consumir exatamente a mesma fonte
        // consolidada usada no fechamento, eliminando divergência por timestamp.
        $response->with([
            'sangrias' => $resumo['sangrias'],
            'suprimentos' => $resumo['suprimentos'],
            'vendas' => $vendasPdv,
        ]);

        return $response;
    }

    public function store(Request $request)
    {
        // Antes de qualquer ItemVendaCaixa/StockMove, garante que produtos,
        // cliente e filial pertencem ao tenant autenticado.
        $this->tenantGuard->validar($request);

        return parent::store($request);
    }
}
