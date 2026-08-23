<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;

class AutorizaMercadoPagoFinanceiro
{
    public function handle(Request $request, Closure $next)
    {
        $sessao = session('user_logged');

        if (!$sessao) {
            return response()->json([
                'erro' => 'Sessão expirada. Entre novamente no sistema.',
            ], 401);
        }

        $usuarioId = (int) (is_object($sessao)
            ? ($sessao->id ?? 0)
            : ($sessao['id'] ?? $sessao['usuario_id'] ?? 0));

        $empresaId = (int) (is_object($sessao)
            ? ($sessao->empresa_id ?? 0)
            : ($sessao['empresa'] ?? $sessao['empresa_id'] ?? 0));

        if ($usuarioId <= 0 || $empresaId <= 0) {
            return response()->json(['erro' => 'Sessão inválida.'], 401);
        }

        $usuario = Usuario::whereKey($usuarioId)
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$usuario) {
            return response()->json(['erro' => 'Usuário não autorizado para esta empresa.'], 403);
        }

        $super = (bool) (is_object($sessao)
            ? ($sessao->super ?? false)
            : ($sessao['super'] ?? false));

        if ($super || (bool) ($usuario->super ?? false)) {
            return $next($request);
        }

        $permissoes = json_decode((string) $usuario->permissao, true);
        if (!is_array($permissoes)) {
            $permissoes = [];
        }

        $permitido = collect($permissoes)->contains(function ($permissao) {
            $path = '/' . ltrim((string) $permissao, '/');
            return rtrim($path, '/') === '/conta-receber';
        });

        if (!$permitido) {
            return response()->json([
                'erro' => 'Você não possui permissão para operar Contas a Receber.',
            ], 403);
        }

        return $next($request);
    }
}
