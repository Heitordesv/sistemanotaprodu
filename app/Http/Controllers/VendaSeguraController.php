<?php

namespace App\Http\Controllers;

use App\Exceptions\CaixaMovimentacaoException;
use App\Services\VendaCaixaEdicaoService;
use App\Services\VendaTenantGuardService;
use Illuminate\Http\Request;

class VendaSeguraController extends VendaController
{
    public function __construct(
        private VendaTenantGuardService $tenantGuard,
        private VendaCaixaEdicaoService $caixaEdicao
    ) {
    }

    public function store(Request $request)
    {
        if ((string) $request->input('type') === 'venda') {
            $this->tenantGuard->validar($request);
        }

        return parent::store($request);
    }

    public function update(Request $request, $id)
    {
        if ((string) $request->input('type') !== 'venda') {
            return parent::update($request, $id);
        }

        try {
            return $this->caixaEdicao->executar(
                (int) $id,
                (int) $request->empresa_id,
                function () use ($request, $id) {
                    // A validação e todo o update legado ficam dentro da mesma
                    // transação que mantém o lock da abertura até o commit.
                    $this->tenantGuard->prepararUpdate($request, (int) $id);

                    return parent::update($request, $id);
                }
            );
        } catch (CaixaMovimentacaoException $e) {
            return $this->respostaConflitoCaixa($request, $e);
        }
    }

    public function destroy($id)
    {
        $request = request();

        try {
            return $this->caixaEdicao->executar(
                (int) $id,
                (int) $request->empresa_id,
                function () use ($id) {
                    return parent::destroy($id);
                }
            );
        } catch (CaixaMovimentacaoException $e) {
            return $this->respostaConflitoCaixa($request, $e);
        }
    }

    private function respostaConflitoCaixa(Request $request, CaixaMovimentacaoException $e)
    {
        // O controller legado pode ter preparado flash de sucesso antes de a
        // camada externa detectar uma quebra de invariantes e fazer rollback.
        session()->forget('flash_sucesso');

        if ($request->expectsJson()) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return redirect()->back()->with('flash_erro', $e->getMessage());
    }
}
