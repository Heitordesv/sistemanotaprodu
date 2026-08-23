<?php

namespace App\Http\Controllers;

use App\Services\CaixaResumoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontBoxResumoController extends FrontBoxController
{
    public function __construct(private CaixaResumoService $caixaResumoService)
    {
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
}
