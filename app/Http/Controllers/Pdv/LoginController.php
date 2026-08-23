<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\PdvTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 10;
    private const LOGIN_DECAY_SECONDS = 60;

    public function __construct(private PdvTokenService $tokens)
    {
    }

    public function login(Request $request)
    {
        $login = trim((string) $request->login);
        $senha = (string) $request->senha;
        $rateKey = $this->rateLimitKey($request, $login);

        if (RateLimiter::tooManyAttempts($rateKey, self::MAX_LOGIN_ATTEMPTS)) {
            return response()->json([
                'message' => 'Muitas tentativas de login. Tente novamente em instantes.'
            ], 429);
        }

        if ($login === '' || $senha === '') {
            RateLimiter::hit($rateKey, self::LOGIN_DECAY_SECONDS);
            return response()->json([
                'message' => 'Login ou senha inválidos.'
            ], 401);
        }

        $usuario = Usuario::query()
            ->where('login', $login)
            ->where('senha', md5($senha))
            ->first();

        if (
            !$usuario ||
            ($usuario->ativo !== null && (int) $usuario->ativo === 0) ||
            (int) $usuario->empresa_id <= 0
        ) {
            RateLimiter::hit($rateKey, self::LOGIN_DECAY_SECONDS);
            return response()->json([
                'message' => 'Login ou senha inválidos.'
            ], 401);
        }

        RateLimiter::clear($rateKey);
        $token = $this->tokens->issue($usuario);

        return response()->json([
            'nome' => $usuario->nome,
            'token' => $token['token'],
            'token_expires_at' => $token['expires_at'],
            'token_expires_in' => $token['expires_in'],
            'id' => (int) $usuario->id,
            'img' => $usuario->img,
            'empresa_id' => (int) $usuario->empresa_id,
        ], 200);
    }

    private function rateLimitKey(Request $request, string $login): string
    {
        return 'pdv-login|' . $request->ip() . '|' . Str::lower($login ?: 'vazio');
    }
}
