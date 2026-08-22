<?php

namespace App\Http\Controllers;

use App\Models\ClienteEcommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClienteEcommerceController extends Controller
{
    public function index(Request $request)
    {
        $data = ClienteEcommerce::where('empresa_id', $request->empresa_id)
            ->paginate(env('PAGINACAO'));

        return view('cliente_ecommerce.index', compact('data'));
    }

    public function create()
    {
        return view('cliente_ecommerce.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);

        try {
            $data = $request->only([
                'nome', 'sobre_nome', 'email', 'telefone', 'cpf', 'ie', 'empresa_id'
            ]);

            $data['senha'] = Hash::make($request->senha);
            $data['status'] = 1;
            $data['token'] = Str::random(40);

            ClienteEcommerce::create($data);
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('clienteEcommerce.index');
    }

    public function edit($id)
    {
        $item = $this->clienteDaEmpresa($id);
        return view('cliente_ecommerce.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = $this->clienteDaEmpresa($id);
        $this->_validate($request, true, $item->id);

        try {
            $data = $request->only([
                'nome', 'sobre_nome', 'email', 'telefone', 'cpf', 'ie', 'status'
            ]);

            if ($request->filled('senha')) {
                $data['senha'] = Hash::make($request->senha);
            }

            $item->fill($data)->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('clienteEcommerce.index');
    }

    private function clienteDaEmpresa($id): ClienteEcommerce
    {
        $empresaId = (int) request()->empresa_id;

        return ClienteEcommerce::where('id', $id)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();
    }

    private function _validate(Request $request, bool $update = false, ?int $clienteId = null)
    {
        $empresaId = (int) $request->empresa_id;

        $rules = [
            'nome' => ['required', 'string', 'max:80'],
            'sobre_nome' => ['required', 'string', 'max:80'],
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('cliente_ecommerces', 'email')
                    ->where(fn ($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($clienteId),
            ],
            'telefone' => ['required', 'string', 'max:20'],
            'cpf' => ['required', 'string', 'max:20'],
            'senha' => $update
                ? ['nullable', 'string', 'min:8', 'max:72']
                : ['required', 'string', 'min:8', 'max:72'],
        ];

        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'sobre_nome.required' => 'O campo sobrenome é obrigatório.',
            'senha.required' => 'O campo senha é obrigatório.',
            'senha.min' => 'A senha deve possuir pelo menos 8 caracteres.',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Já existe um cliente com este e-mail nesta loja.',
            'telefone.required' => 'O campo telefone é obrigatório.',
            'cpf.required' => 'O campo documento é obrigatório.',
        ];

        $this->validate($request, $rules, $messages);
    }
}