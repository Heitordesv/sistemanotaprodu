<?php

namespace App\Http\Middleware;

use App\Services\PdvTokenService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class AuthPdv
{
    public const USER_ID_ATTRIBUTE = 'pdv_authenticated_user_id';
    public const EMPRESA_ID_ATTRIBUTE = 'pdv_authenticated_empresa_id';

    public function __construct(private PdvTokenService $tokens)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken() ?: $request->header('token');

        try {
            $usuario = $this->tokens->authenticate($token);
        } catch (AuthenticationException $e) {
            return response()->json([
                'message' => 'Credencial PDV inválida ou expirada.'
            ], 401);
        }

        $empresaId = (int) $usuario->empresa_id;
        $usuarioId = (int) $usuario->id;

        // The client may still send empresa_id for backwards compatibility,
        // but it is never authoritative after authentication.
        $request->merge(['empresa_id' => $empresaId]);
        $request->attributes->set(self::EMPRESA_ID_ATTRIBUTE, $empresaId);
        $request->attributes->set(self::USER_ID_ATTRIBUTE, $usuarioId);

        // Legacy route keeps /caixa/{usuario_id}, but a token cannot inspect
        // another operator's cash register by changing the route parameter.
        $routeUsuarioId = $request->route('usuario_id');
        if ($routeUsuarioId !== null && (int) $routeUsuarioId !== $usuarioId) {
            return response()->json([
                'message' => 'Acesso não autorizado.'
            ], 403);
        }

        return $next($request);
    }
}
