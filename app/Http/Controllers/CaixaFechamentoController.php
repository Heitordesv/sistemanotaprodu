<?php

namespace App\Http\Controllers;

use App\Services\CaixaFechamentoService;
use DomainException;
use Illuminate\Http\Request;

class CaixaFechamentoController extends Controller
{
    public function fechar(Request $request, CaixaFechamentoService $service)
    {
        $empresaId = (int) request()->empresa_id;
        $usuarioId = (int) get_id_user();
        $aberturaId = (int) $request->abertura_id;

        try {
            $service->fechar($aberturaId, $empresaId, $usuarioId);
            session()->flash('flash_sucesso', 'Caixa fechado com sucesso!');
        } catch (DomainException $e) {
            session()->flash('flash_warning', $e->getMessage());
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Não foi possível fechar o caixa: ' . $e->getMessage());
            __saveLogError($e, $empresaId);
        }

        if ($request->filled('redirect')) {
            return redirect($request->redirect);
        }

        return redirect()->route('frenteCaixa.list');
    }
}
