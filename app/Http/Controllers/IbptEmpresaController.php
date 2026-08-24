<?php

namespace App\Http\Controllers;

use App\Models\ConfigNota;
use App\Models\Produto;
use App\Models\ProdutoIbpt;
use App\Services\IbptEmpresaSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IbptEmpresaController extends Controller
{
    private const NCM_VALIDO_SQL = "REPLACE(REPLACE(REPLACE(NCM, '.', ''), '-', ''), ' ', '') REGEXP '^[0-9]{8}$'";

    public function __construct(private IbptEmpresaSyncService $sync)
    {
    }

    public function index(Request $request)
    {
        $empresaId = (int) $request->empresa_id;
        $config = ConfigNota::where('empresa_id', $empresaId)->first();
        $produtos = Produto::where('empresa_id', $empresaId);

        return view('config_nota.ibpt', [
            'tokenCadastrado' => $config && trim((string) $config->getRawOriginal('token_ibpt')) !== '',
            'total' => (clone $produtos)->count(),
            'elegiveis' => (clone $produtos)->whereRaw(self::NCM_VALIDO_SQL)->count(),
            'atualizados' => ProdutoIbpt::whereHas('produto', fn ($query) => $query->where('empresa_id', $empresaId))->count(),
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $empresaId = (int) $request->empresa_id;
        $config = ConfigNota::where('empresa_id', $empresaId)->firstOrFail();
        $cursor = max(0, (int) $request->input('cursor', 0));
        $produtos = Produto::where('empresa_id', $empresaId)->where('id', '>', $cursor)
            ->whereRaw(self::NCM_VALIDO_SQL)->orderBy('id')->limit(10)->get();
        $atualizados = 0;
        $ultimoId = $cursor;

        foreach ($produtos as $produto) {
            $ultimoId = $produto->id;
            try {
                $this->sync->sync($config, $produto);
                $atualizados++;
            } catch (\Throwable $e) {
                report($e);
                return response()->json([
                    'message' => $e->getMessage(), 'cursor' => $ultimoId, 'atualizados' => $atualizados,
                ], 422);
            }
        }

        $hasMore = Produto::where('empresa_id', $empresaId)->where('id', '>', $ultimoId)
            ->whereRaw(self::NCM_VALIDO_SQL)->exists();

        return response()->json([
            'cursor' => $ultimoId, 'atualizados' => $atualizados, 'has_more' => $hasMore,
        ]);
    }
}
