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

        $response->with([
            'sangrias' => $resumo['sangrias'],
            'suprimentos' => $resumo['suprimentos'],
            'vendas' => $vendasPdv,
        ]);

        return $response;
    }

    public function store(Request $request)
    {
        // O navegador pode indicar a filial operacional, mas ela só é aceita
        // depois da validação multi-tenant do servidor.
        $this->tenantGuard->validar($request);

        $filialInformada = $request->input('filial_id');
        $estoqueFilialId = (
            $filialInformada === null
            || $filialInformada === ''
            || (int) $filialInformada === -1
        ) ? null : (int) $filialInformada;

        // O mesmo valor é consumido pelo StockMove e mass-assigned em VendaCaixa
        // DENTRO da transação de FrontBoxController::store(). Assim, a venda e a
        // baixa de estoque nunca podem divergir quanto ao escopo histórico.
        $request->merge(['estoque_filial_id' => $estoqueFilialId]);

        return parent::store($request);
    }
}
