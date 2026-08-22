<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;

class LoginController extends Controller
{
	public function login(Request $request)
	{
		$keyENV = env('KEY_APP');
		$login = $request->login;
		$senha = $request->senha;

		$usuario = Usuario::where('login', $login)
			->where('senha', md5($senha))
			->first();

		if ($usuario == null) return response()->json($senha, 401);

		$credenciais = [
			'nome' => $usuario->nome,
			'token' => base64_encode($usuario->id . ';' . $usuario->login . ';' . $keyENV),
			'id' => $usuario->id,
			'img' => $usuario->img,
			'empresa_id' => $usuario->empresa_id
		];

		return response()->json($credenciais, 200);
	}
}
