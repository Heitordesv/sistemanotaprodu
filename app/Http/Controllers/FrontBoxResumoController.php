<?php

namespace App\Http\Controllers;

use App\Models\VendaCaixa;
use App\Services\CaixaResumoService;
use App\Services\VendaTenantGuardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $empresaId = (int) $request->empresa_id;
        $usuarioId = (int) get_id_user();
        $filialInformada = $request->input('filial_id');
        $estoqueFilialId = (
            $filialInformada === null
            || $filialInformada === ''
            || (int) $filialInformada === -1
        ) ? null : (int) $filialInformada;

        // StockMove lê este campo apenas na rota frenteCaixa.store. O campo também
        // será persistido depois da criação para que uma devolução futura reverta
        // exatamente o mesmo estoque que esta venda baixou.
        $request->merge(['estoque_filial_id' => $estoqueFilialId]);

        $ultimoIdAntes = (int) (VendaCaixa::query()
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->max('id') ?? 0);

        $response = parent::store($request);

        if (Schema::hasColumn('venda_caixas', 'estoque_filial_id')) {
            $novaVendaId = (int) (VendaCaixa::query()
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuarioId)
                ->where('id', '>', $ultimoIdAntes)
                ->max('id') ?? 0);

            if ($novaVendaId > 0) {
                // DB::table evita ampliar o $fillable do model legado e deixa
                // explícito que esse valor é metadado calculado pelo servidor.
                DB::table('venda_caixas')
                    ->where('id', $novaVendaId)
                    ->where('empresa_id', $empresaId)
                    ->update(['estoque_filial_id' => $estoqueFilialId]);
            }
        }

        return $response;
    }
}
