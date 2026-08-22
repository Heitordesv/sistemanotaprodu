<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transportadora;
use App\Models\Cidade;
use App\Rules\ValidaDocumento;
use Illuminate\Support\Facades\DB;

class TransportadoraController extends Controller
{
    public function index(Request $request)
    {
        $data = Transportadora::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->razao_social), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('razao_social', 'LIKE', "%$request->razao_social%");
                });
            })
            ->when(!empty($request->cnpj_cpf), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('cnpj_cpf', 'LIKE', "%$request->cnpj_cpf%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('transportadoras.index', compact('data'));
    }

    public function create()
    {
        $cidades = Cidade::all();
        return view(
            'transportadoras/create',
            compact('cidades')
        );
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'ie_rg' => $request->ie_rg ?? '',
                'email' => $request->email ?? '',
                'telefone' => $request->telefone ?? ''
            ]);
            DB::transaction(function () use ($request) {
                Transportadora::create($request->all());
            });
            session()->flash("flash_sucesso", "Transportadora cadastrada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('transportadoras.index');
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = Transportadora::findOrFail($id);
        try {
            $request->merge([
                'telefone' => $request->telefone ?? ''
            ]);
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Transportadora atualizado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('transportadoras.index');
    }

    public function edit($id)
    {
        $item = Transportadora::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $cidades = Cidade::all();
        return view(
            'transportadoras/edit',
            compact('cidades', 'item')
        );
    }

    private function _validate(Request $request)
    {
        $doc = $request->cpf_cnpj;
        $rules = [
            'razao_social' => 'required|max:80',
            'cnpj_cpf' => ['required', new ValidaDocumento],
            'logradouro' => 'required|max:80',
            'telefone' => 'max:20',
            'email' => 'max:40',
            'cidade_id' => 'required',
        ];
        $messages = [
            'razao_social.required' => 'O Razão social/Nome é obrigatório.',
            'razao_social.max' => '50 caracteres maximos permitidos.',
            'nome_fantasia.required' => 'O campo Nome Fantasia é obrigatório.',
            'nome_fantasia.max' => '80 caracteres maximos permitidos.',
            'cnpj_cpf.required' => 'O campo CPF/CNPJ é obrigatório.',
            'cnpj_cpf.min' => strlen($doc) > 14 ? 'Informe 14 números para CNPJ.' : 'Informe 14 números para CPF.',
            'rua.required' => 'O campo Rua é obrigatório.',
            'rua.max' => '80 caracteres maximos permitidos.',
            'logradouro.required' => 'Endereço é Obrigatório',
            'numero.required' => 'O campo Numero é obrigatório.',
            'cep.required' => 'O campo CEP é obrigatório.',
            'cep.min' => 'CEP inválido.',
            'cidade_id.required' => 'O campo Cidade é obrigatório.',
            'numero.max' => '10 caracteres maximos permitidos.',
            'bairro.required' => 'O campo Bairro é obrigatório.',
            'bairro.max' => '50 caracteres maximos permitidos.',
            'telefone.required' => 'O campo Telefone é obrigatório.',
            'telefone.max' => '20 caracteres maximos permitidos.',
            'consumidor_final.required' => 'O campo Consumidor final é obrigatório.',
            'contribuinte.required' => 'O campo Contribuinte é obrigatório.',
            'celular.max' => '20 caracteres maximos permitidos.',
            'email.required' => 'O campo Email é obrigatório.',
            'email.max' => '40 caracteres maximos permitidos.',
            'email.email' => 'Email inválido.',
            'rua_cobranca.max' => '80 caracteres maximos permitidos.',
            'numero_cobranca.max' => '10 caracteres maximos permitidos.',
            'bairro_cobranca.max' => '30 caracteres maximos permitidos.',
            'cep_cobranca.max' => '9 caracteres maximos permitidos.',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = Transportadora::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Transportadora removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('transportadoras.index');
    }
}
