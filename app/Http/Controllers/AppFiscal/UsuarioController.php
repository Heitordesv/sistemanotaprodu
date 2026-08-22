<?php

namespace App\Http\Controllers\AppFiscal;

use Illuminate\Http\Request;
use App\Models\Usuario;

class UsuarioController extends Controller
{
	public function index(Request $request){
		$keyENV = env('KEY_APP');
		$login = $request->login;
		$senha = $request->senha;
		$key_app = $request->key_app;

		$usuario = Usuario::
		where('login', $login)
		->where('senha', md5($senha))
		->first();

		if($usuario == null) return response()->json(null, 401);

		if($keyENV != $key_app) return response()->json(null, 401);

		$credenciais = [
			'nome' => $usuario->nome,
			'token' => base64_encode($usuario->id . ';' . $usuario->login . ';' . $key_app),
			'id' => $usuario->id,
			'img' => $usuario->img,
			'empresa_id' => $usuario->empresa_id
		];

		return response()->json($credenciais, 200);
	}

	public function salvarImagem(Request $request){
		try{
			$imagem = $request->file;
			$usuarioId = $request->usuario_id;
			$public = env('SERVIDOR_WEB') ? 'public/' : '';

			$nome = md5(rand(100000, 99999999999));

			$usuario = Usuario::find($usuarioId);

			if($usuario->img != ''){
				if(file_exists($public.'foto_usuario/'.$usuario->img)){
					unlink($public.'foto_usuario/'.$usuario->img);
				}
			}

			$imgData = str_replace('data:image/jpeg;base64,', '', $imagem);
			$imgData = str_replace('data:image/jpg;base64,', '', $imgData);
			$imgData = str_replace(' ', '+', $imgData);
			$imgData = base64_decode($imgData);

			$usuario->img = $nome.'.jpg';
			$usuario->save();
			file_put_contents($public.'foto_usuario/'.$nome.'.jpg', $imgData);

			return response()->json($nome.'.jpg', 201);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

}