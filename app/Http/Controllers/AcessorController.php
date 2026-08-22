<?php

namespace App\Http\Controllers;

use App\Models\Acessor;
use Illuminate\Http\Request;
use App\Models\Cidade;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidaDocumento;


class AcessorController extends Controller
{
    public function index(Request $request)
    {
        $data = Acessor::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->razao_social), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('razao_social', 'LIKE', "%$request->razao_social%");
                });
            })
            ->when(!empty($request->cpf_cnpj), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('cpf_cnpj', 'LIKE', "%$request->cpf_cnpj%");
                });
            })
            ->paginate(env("PAGINACAO"));

        return view('acessores/index', compact('data'));
    }

    public function create()
    {
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        return view('acessores.create', compact('cidades', 'funcionarios'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'ie_rg' => $request->ie_rg ?? '',
                'percentual_comissao' => $request->percentual_comissao ? __convert_value_bd($request->percentual_comissao) : 0,
                'funcionario_id' => $request->funcionario_id ?? 0,
                'ie_rg' => $request->ie_rg ?? ''
            ]);
            Acessor::create($request->all());
            session()->flash("flash_sucesso", "Cadastro com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", " Erro:" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('acessores.index');
    }


    private function _validate(Request $request)
    {
        $doc = $request->cpf_cnpj;
        $rules = [
            'razao_social' => 'required|max:80',
            'cpf_cnpj' => ['required', new ValidaDocumento],
            'rua' => 'required|max:80',
            'numero' => 'required|max:10',
            'bairro' => 'required|max:50',
            'telefone' => 'required|max:20',
            'celular' => 'required|max:20',
            'email' => 'required|max:40',
            'cep' => 'required|min:9',
            'cidade_id' => 'required',
            'data_registro' => 'required'
        ];
        $messages = [
            'razao_social.required' => 'O Razão social/Nome é obrigatório.',
            'razao_social.max' => '50 caracteres maximos permitidos.',
            'cpf_cnpj.required' => 'O campo CPF/CNPJ é obrigatório.',
            'cpf_cnpj.min' => strlen($doc) > 14 ? 'Informe 14 números para CNPJ.' : 'Informe 14 números para CPF.',
            'rua.required' => 'O campo Rua é obrigatório.',
            'rua.max' => '80 caracteres maximos permitidos.',
            'numero.required' => 'O campo Numero é obrigatório.',
            'cep.required' => 'O campo CEP é obrigatório.',
            'cep.min' => 'CEP inválido.',
            'cidade_id.required' => 'O campo Cidade é obrigatório.',
            'numero.max' => '10 caracteres maximos permitidos.',
            'bairro.required' => 'O campo Bairro é obrigatório.',
            'bairro.max' => '50 caracteres maximos permitidos.',
            'telefone.required' => 'O campo Telefone é obrigatório.',
            'telefone.max' => '20 caracteres maximos permitidos.',
            'celular.max' => '20 caracteres maximos permitidos.',
            'email.required' => 'O campo Email é obrigatório.',
            'email.max' => '40 caracteres maximos permitidos.',
            'email.email' => 'Email inválido.',
            'rua_cobranca.max' => '80 caracteres maximos permitidos.',
            'numero_cobranca.max' => '10 caracteres maximos permitidos.',
            'bairro_cobranca.max' => '30 caracteres maximos permitidos.',
            'cep_cobranca.max' => '9 caracteres maximos permitidos.',
            'data_registro.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function edit($id)
    {
        $item = Acessor::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();

        return view('acessores.edit', compact('item', 'cidades', 'funcionarios'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = Acessor::findOrFail($id);
        try {
            $request->merge([
                'ie_rg' => $request->ie_rg ?? '',
                'percentual_comissao' => $request->percentual_comissao ? __convert_value_bd($request->percentual_comissao) : 0,
                'funcionario_id' => $request->funcionario_id ?? 0,
            ]);
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Acessor atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('acessores.index');
    }

    public function destroy($id)
    {
        $item = Acessor::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Deletado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('acessores.index');
    }
}
