<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OperacionalSeguroController extends Controller
{
    public function limparCache(Request $request)
    {
        $this->autorizarAdministrador($request);

        Artisan::call('optimize:clear');

        return response()->json([
            'status' => 'ok',
            'mensagem' => 'Cache da aplicação limpo com sucesso.',
        ]);
    }

    public function enviarCobranca(Request $request)
    {
        $contexto = $this->autorizarAdministrador($request);

        try {
            Artisan::call('notificacao:empresa-vencimento');

            return response()->json([
                'status' => 'ok',
                'mensagem' => 'Processamento de cobranças concluído.',
            ]);
        } catch (Throwable $e) {
            Log::error('Falha ao executar processamento administrativo de cobranças.', [
                'empresa_id' => $contexto['empresa_id'],
                'usuario_id' => $contexto['usuario_id'],
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Não foi possível executar o processamento de cobranças.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function autorizarAdministrador(Request $request): array
    {
        $user = session('user_logged');

        if (!$user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Sessão expirada.');
        }

        $empresaId = (int) (is_object($user)
            ? ($user->empresa_id ?? 0)
            : ($user['empresa'] ?? $user['empresa_id'] ?? 0));
        $usuarioId = (int) (is_object($user)
            ? ($user->id ?? 0)
            : ($user['id'] ?? $user['usuario_id'] ?? 0));
        $admin = (bool) (is_object($user)
            ? (($user->adm ?? false) || ($user->super ?? false))
            : (($user['adm'] ?? false) || ($user['super'] ?? false)));

        if ($empresaId <= 0 || $usuarioId <= 0 || !$admin) {
            abort(Response::HTTP_FORBIDDEN, 'Operação não autorizada.');
        }

        return [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
        ];
    }
}
