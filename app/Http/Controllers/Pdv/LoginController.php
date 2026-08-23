<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\PdvTokenService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(private PdvTokenService $tokens)
    {
    }

    public function login(Request $request)
    {
        $login = trim((string) $request->login);
        $senha = (string) $request->senha;

        if ($login === '' || $senha === '') {
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
            return response()->json([
                'message' => 'Login ou senha inválidos.'
            ], 401);
        }

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
}
