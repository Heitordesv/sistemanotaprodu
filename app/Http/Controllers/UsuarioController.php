<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Menu;
use App\Http\Middleware\LimiteUsuarios;
use App\Models\UsuarioAcesso;
use App\Models\Usuario;
use App\Models\ConfigCaixa;
use App\Utils\UploadUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
	protected $util;

	public function __construct(UploadUtil $util)
	{
		$this->middleware(LimiteUsuarios::class)->only('create');
        $this->util = $util;

	}
	public function bemVindo()
{
    $session = session('user_logged');

    // Busca o usuário completo no banco
    $usuario = Usuario::where('id', $session['id'])
        ->where('empresa_id', $session['empresa_id'])
        ->first();

    return view('default.use', [
        'title' => 'Bem-vindo',
        'usuario' => $usuario
    ]);
}


	public function index()
	{
		$data = Usuario::where('empresa_id', request()->empresa_id)
			->paginate(env("PAGINACAO"));
		return view('usuarios.index', compact('data'));
	}

	public function setLocation(Request $request)
	{
		try {
			$usuario = Usuario::find(get_id_user());
			$usuario->local_padrao = $request->filial_id;
			$usuario->save();
			return response()->json($usuario, 200);
		} catch (\Exception $e) {
			return response()->json($e->getMessage(), 401);
		}
	}

	public function create()
	{
		$value = session('user_logged');
		$usuario = Usuario::findOrFail($value['id']);
		$permissoesAtivas = $usuario->empresa->permissao;
		$permissoesDoUsuario = [];
		$permissoesAtivas = json_decode($permissoesAtivas);
		$permissoesUsuario = [];
		if ($value['super']) {
			$permissoesAtivas = $this->detalhesMaster();
		}
		$menu = new Menu();
		$menu = $menu->getMenu();

		for ($i = 0; $i < sizeof($menu); $i++) {
			$temp = false;
			foreach ($menu[$i]['subs'] as $s) {
				if (in_array($s['rota'], $permissoesAtivas)) {
					$temp = true;
				}
			}
			$menu[$i]['ativo'] = $temp;
		}

		$menuAux = $menu;

		return view('usuarios.create', compact(
			'permissoesAtivas',
			'permissoesUsuario',
			'menu',
			'permissoesDoUsuario',
			// 'usuario',
			'menuAux'
		));
	}

	private function detalhesMaster()
	{
		$menu = new Menu();
		$menu = $menu->getMenu();
		$temp = [];
		foreach ($menu as $m) {
			foreach ($m['subs'] as $s) {
				array_push($temp, $s['rota']);
			}
		}
		return $temp;
	}

	private function validaPermissao($request)
	{
		$menu = new Menu();
		$arr = $request->all();
		$arr = (array) ($arr);
		$menu = $menu->getMenu();
		$temp = [];

		foreach ($menu as $m) {
			foreach ($m['subs'] as $s) {
				if (isset($arr[str_replace(".", "_", $s['rota'])])) {
					array_push($temp, $s['rota']);
				}
			}
		}
		return $temp;
	}

	public function store(Request $request)
	{
		$this->_validate($request);
		$permissao = $this->validaPermissao($request);

		$locais = json_encode($request->local);

		try {
			$file_name = '';
			if ($request->hasFile('image')) {
				$file_name = $this->util->uploadImage($request, '/usuarios');
			}
			$result = Usuario::create([
				'nome' => $request->nome,
				'login' => $request->login,
				'senha' => md5($request->senha),
				'adm' => $request->adm ? true : false,
				'somente_fiscal' => $request->somente_fiscal ? true : false,
				'caixa_livre' => $request->caixa_livre ? true : false,
				'ativo' => $request->ativo ? true : false,
				'email' => $request->email,
				'locais' => $locais,
				'local_padrao' => $request->local_padrao,
				'menu_representante' => isset($request->menu_representante) ? ($request->menu_representante ? true : false) : false,
				'rota_acesso' => $request->rota_acesso ?? '',
				'permissao' => json_encode($permissao),
				'empresa_id' => $request->empresa_id,
				'img' => $file_name
			]);

			$this->criaConfigCaixa($result);

			session()->flash("flash_sucesso", "Usuário Cadastrado com Sucesso");
		} catch (\Exception $e) {
			session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
			__saveLogError($e, request()->empresa_id);
		}
		return redirect()->route('usuarios.index');
	}

	private function criaConfigCaixa($usuario)
	{
		$data = [
			'finalizar' => '',
			'reiniciar' => '',
			'editar_desconto' => '',
			'editar_acrescimo' => '',
			'editar_observacao' => '',
			'setar_valor_recebido' => '',
			'forma_pagamento_dinheiro' => '',
			'forma_pagamento_debito' => '',
			'forma_pagamento_credito' => '',
			'setar_quantidade' => '',
			'forma_pagamento_pix' => '',
			'setar_leitor' => '',
			'finalizar_fiscal' => '',
			'finalizar_nao_fiscal' => '',
			'valor_recebido_automatico' => 0,
			'modelo_pdv' => 2,
			'balanca_valor_peso' => 0,
			'balanca_digito_verificador' => 5,
			'valor_recebido_automatico' => 0,
			'impressora_modelo' => 80,
			'usuario_id' => $usuario->id,
			'mercadopago_public_key' => '',
			'mercadopago_access_token' => '',
			'tipos_pagamento' => '["01","02","03","04","05","06","10","11","12","13","14","15","16","17","90","99"]',
			'tipo_pagamento_padrao' => '01'
		];
		ConfigCaixa::create($data);
	}

public function historico($id)
{
    $usuario = Usuario::findOrFail($id);

    if (valida_objeto($usuario)) {

        $acessos = UsuarioAcesso::where('usuario_id', $id)
            ->orderBy('created_at', 'desc') // últimos primeiro
            ->paginate(50);

        return view('usuarios.historico', compact('usuario', 'acessos'));
    } else {
        return redirect('/403');
    }
}

	public function edit($id)
	{
		$value = session('user_logged');
		$usuario = Usuario::findOrFail($id);
		if (valida_objeto($usuario)) {
			$permissoesAtivas = $usuario->empresa->permissao;
			$permissoesUsuario = $usuario->permissao;
			$permissoesDoUsuario = [];
			$permissoesAtivas = json_decode($permissoesAtivas);
			$permissoesUsuario = json_decode($permissoesUsuario);
			if ($value['super']) {
				$permissoesAtivas = $this->detalhesMaster();
			}
			$menu = new Menu();
			$menu = $menu->getMenu();
			for ($i = 0; $i < sizeof($menu); $i++) {
				$temp = false;
				foreach ($menu[$i]['subs'] as $s) {
					if (in_array($s['rota'], $permissoesAtivas)) {
						$temp = true;
					}
				}
				$menu[$i]['ativo'] = $temp;
			}
			$menuAux = $menu;

			return view('usuarios.edit', compact(
				'usuario',
				'permissoesAtivas',
				'permissoesUsuario',
				'menuAux',
				'menu'
			));
		}
	}

	private function _validate(Request $request, $id = 0)
	{
		$rules = [
			'nome' => 'required',
			'email' => 'required|email',
			'login' => $id == 0  ? ['required', Rule::unique('usuarios')->ignore($id)] : '',
			'senha' => $id == 0 ? 'required' : '',
		];
		$messages = [
			'nome.required' => 'O campo nome é obrigatório.',
			'email.required' => 'O campo email é obrigatório.',
			'email.email' => 'Email inválido',
			'login.required' => 'O campo login é obrigatório.',
			'senha.required' => 'O campo senha é obrigatório',
			'login.unique' => 'Login já utilizado no sistema.'
		];
		$this->validate($request, $rules, $messages);
	}

	public function update(Request $request, $id)
	{
		// dd($request);
		$this->_validate($request, $id);
		$permissao = $this->validaPermissao($request);
		$usuario = Usuario::findOrFail($id);
		try {
			$file_name = '';
			if ($request->hasFile('image')) {
				$this->util->unlinkImage($usuario, '/usuarios');
				$file_name = $this->util->uploadImage($request, '/usuarios');
			};
			$locais = $request->local ? json_encode($request->local) : NULL;
			$request->merge([
				'permissao' => json_encode($permissao),
				'local_padrao' => $request->local_padrao,
				'locais' => $locais,
				'img' => $file_name
			]);

			if ($request->senha) {
				$request->merge([
					'senha' => md5($request->senha)
				]);
			} else {
				$request->merge([
					'senha' => $usuario->senha
				]);
			}
			$usuario->fill($request->all())->save();
			session()->flash("flash_sucesso", "Cadastro Atualizado");
		} catch (\Exception $e) {
			session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
			__saveLogError($e, request()->empresa_id);
		}
		return redirect()->route('usuarios.index');
	}

	public function destroy($id)
	{
		$usuario = Usuario::where('id', $id)->first();
		$usuarios = Usuario::where('empresa_id', request()->empresa_id)->get();
		if (sizeof($usuarios) == 1) {
			session()->flash('flash_erro', 'Não é possivel remover o ultimo usuário!');
			return redirect()->back();
		}
		if (valida_objeto($usuario)) {
			$usuario->config()->delete();
			if ($usuario->delete()) {
				session()->flash("flash_sucesso", "Usuário removido!");
			} else {
				session()->flash('flash_erro', 'Erro ao remover usuário!');
			}
			return redirect()->route('usuarios.index');
		} else {
			return redirect('/403');
		}
	}
}
