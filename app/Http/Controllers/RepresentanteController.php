<?php

namespace App\Http\Controllers;

use App\Helpers\Menu;
use App\Models\Empresa;
use App\Models\PerfilAcesso;
use App\Models\Representante;
use App\Models\Usuario;
use Illuminate\Http\Request;

class RepresentanteController extends Controller
{
    public function index(Request $request)
    {
        $data = Representante::when(!empty($request->nome), function ($q) use ($request) {
            return $q->where(function ($quer) use ($request) {
                return $quer->where('nome', 'LIKE', "%$request->nome%");
            });
        })
        ->paginate(env("PAGINACAO"));
        return view('representantes.index', compact('data'));
    }



   public function create()
{
    // Busca apenas empresas representantes para o select
    $empresas = Empresa::where('tipo_representante', 1)
        ->orderBy('razao_social', 'asc')
        ->pluck('razao_social', 'id') // Já transforma em [id => razao_social]
        ->all();

    if (empty($empresas)) {
        session()->flash("flash_erro", "Cadastre uma empresa do tipo representante!");
        return redirect()->back();
    }

    return view('representantes.create', compact('empresas'));
}

public function store(Request $request)
{
    $this->_validate($request);
    try {
        // Criar o Usuário Primeiro
        $usuario = Usuario::create([
            'nome' => $request->nome_usuario,
            'senha' => md5($request->senha), // Dica: Use Hash::make($request->senha) se possível
            'login' => $request->login,
            'adm' => 1,
            'ativo' => 1,
            'permissao' => json_encode($this->validaPermissao()),
            'img' => '',
            'empresa_id' => $request->empresa, // ID vindo do select
            'status' => 1
        ]);

        if ($usuario) {
            Representante::create([
                'usuario_id' => $usuario->id,
                'nome' => $request->nome,
                'rua' => $request->rua,
                'numero' => $request->numero,
                'bairro' => $request->bairro,
                'cidade_id' => $request->cidade_id,
                'telefone' => $request->telefone,
                'email' => $request->email,
                'cpf_cnpj' => $request->cpf_cnpj,
                'status' => 1,
                'comissao' => __convert_value_bd($request->comissao),
                'acesso_xml' => $request->acesso_xml ? 1 : 0,
                'bloquear_empresa' => $request->bloquear_empresa ? 1 : 0
            ]);
        }

        session()->flash("flash_sucesso", "Cadastro com sucesso");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, $request->empresa);
    }
    return redirect()->route('representantes.index');
}

    public function update(Request $request, $id)
    {
        $item = Representante::findOrFail($id);
        try {
            $request->merge([
                'senha' => md5($request->senha),
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "atualizado com sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('representantes.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required',
            'cpf_cnpj' => 'required',
            'rua' => 'required',
            'numero' => 'required',
            'bairro' => 'required',
            'login' => 'required|min:5|unique:usuarios',
            'login' => 'required|unique:usuarios',
            'senha' => 'required|max:10',
            'telefone' => 'required',
            'comissao' => 'required',
            'limite_cadastros' => 'required',
            'email' => 'required',
            'nome_usuario' => 'required',
            'empresa' => 'required',
        ];
        $messages = [
            'nome.required' => 'Campo obrigatório.',
            'empresa.required' => 'Campo obrigatório.',
            'cpf_cnpj.required' => 'Campo obrigatório.',
            'rua.required' => 'Campo obrigatório.',
            'numero.required' => 'Campo obrigatório.',
            'bairro.required' => 'Campo obrigatório.',
            'login.required' => 'Campo obrigatório.',
            'telefone.required' => 'Campo obrigatório.',
            'email.required' => 'Campo obrigatório.',
            'limite_cadastros.required' => 'Campo obrigatório.',
            'senha.required' => 'Campo obrigatório.',
            'senha.max' => 'Máx. 10 caracteres',
            'nome_usuario.required' => 'Campo obrigatório.',
            'comissao.required' => 'Informe a comissão.',
            'login.unique' => 'Usuário já cadastrado no sistema.'
        ];
        $this->validate($request, $rules, $messages);
    }

    private function validaPermissao()
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

    public function show($id)
    {
        $item = Representante::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $empresas = Empresa::where('tipo_representante', 1)->orderBy('razao_social', 'desc')->get();
        $planoExpirado = false;
        $permissoesAtivas = $item->usuario->permissao;
        $permissoesAtivas = json_decode($permissoesAtivas);
        $perfis = PerfilAcesso::all();
        $menu = new Menu();
        $menu = $menu->getMenu();
        $temp = [];
        foreach ($menu as $m) {
            foreach ($m['subs'] as $s) {
                array_push($temp, $s['rota']);
            }
        }

        return view(
            'representantes.show',
            compact('item', 'empresas', 'planoExpirado', 'permissoesAtivas', 'perfis', 'menu')
        );
    }
}
