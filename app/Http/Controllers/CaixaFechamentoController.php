<?php

namespace App\Http\Controllers;

use App\Services\CaixaFechamentoService;
use DomainException;
use Illuminate\Http\Request;

class CaixaFechamentoController extends Controller
{
    public function fechar(Request $request, CaixaFechamentoService $service)
    {
        $user = session('user_logged');
        $empresaId = (int) (is_object($user)
            ? ($user->empresa_id ?? 0)
            : ($user['empresa'] ?? $user['empresa_id'] ?? 0));
        $usuarioId = (int) (is_object($user)
            ? ($user->id ?? 0)
            : ($user['id'] ?? $user['usuario_id'] ?? 0));
        $aberturaId = (int) $request->input('abertura_id');

        try {
            $service->fechar($aberturaId, $empresaId, $usuarioId);
            session()->flash('flash_sucesso', 'Caixa fechado com sucesso!');
        } catch (DomainException $e) {
            // DomainException neste serviço representa apenas regra de negócio
            // controlada (caixa já fechado, usuário/empresa inválidos etc.).
            session()->flash('flash_warning', $e->getMessage());
        } catch (\Throwable $e) {
            // Nunca devolver mensagem de SQL/driver/stack para o navegador.
            session()->flash('flash_erro', 'Não foi possível fechar o caixa. Tente novamente.');
            __saveLogError($e, $empresaId);
        }

        return $this->redirectSeguro($request);
    }

    private function redirectSeguro(Request $request)
    {
        $target = trim((string) $request->input('redirect', ''));

        if ($this->caminhoInternoValido($target)) {
            return redirect()->to($target);
        }

        return redirect()->route('frenteCaixa.list');
    }

    private function caminhoInternoValido(string $target): bool
    {
        if (
            $target === ''
            || !str_starts_with($target, '/')
            || str_starts_with($target, '//')
            || str_contains($target, '\\')
        ) {
            return false;
        }

        return parse_url($target, PHP_URL_SCHEME) === null
            && parse_url($target, PHP_URL_HOST) === null;
    }
}
