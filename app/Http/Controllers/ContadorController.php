<?php

namespace App\Http\Controllers;

use App\Models\Contador;
use App\Models\Empresa;
use Illuminate\Http\Request;

class ContadorController extends Controller
{
    public function index()
    {
        $data = Contador::all();
        return view('contadores.index', compact('data'));
    }

    public function create()
    {
        return view('contadores.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'chave_pix' => $request->chave_pix ?? '',
                'conta' => $request->conta ?? '',
                'agencia' => $request->agencia ?? '',
                'banco' => $request->banco ?? '',
                'ie' => $request->ie ?? '',
                'dados_bancarios' => $request->dados_bancarios,
                'contador_parceiro' => $request->contador_parceiro,
            ]);
            Contador::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('contadores.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'razao_social' => 'required|max:100',
            'nome_fantasia' => 'required|max:80',
            'cnpj' => 'required',
            'percentual_comissao' => 'required',
            'logradouro' => 'required|max:80',
            'numero' => 'required|max:10',
            'bairro' => 'required|max:50',
            'fone' => 'required|max:20',
            'cep' => 'required',
            'cidade_id' => 'required',
            'email' => 'required|email|max:80'
        ];
        $messages = [
            'razao_social.required' => 'O Razão social nome é obrigatório.',
            'razao_social.max' => '100 caracteres maximos permitidos.',
            'nome_fantasia.required' => 'O campo Nome Fantasia é obrigatório.',
            'nome_fantasia.max' => '80 caracteres maximos permitidos.',
            'cnpj.required' => 'O campo CNPJ é obrigatório.',
            'logradouro.required' => 'O campo Logradouro é obrigatório.',
            'logradouro.max' => '80 caracteres maximos permitidos.',
            'numero.required' => 'O campo Numero é obrigatório.',
            'cep.required' => 'O campo CEP é obrigatório.',
            'municipio.required' => 'O campo Municipio é obrigatório.',
            'numero.max' => '10 caracteres maximos permitidos.',
            'bairro.required' => 'O campo Bairro é obrigatório.',
            'bairro.max' => '50 caracteres maximos permitidos.',
            'fone.required' => 'O campo Telefone é obrigatório.',
            'fone.max' => '20 caracteres maximos permitidos.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'Informe um email valido.',
            'email.max' => '80 caracteres maximos permitidos.',
            'cidade_id.required' => 'O campo cidade é obrigatório.',
            'percentual_comissao.required' => 'O campo % é obrigatório.',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function edit($id)
    {
        $item = Contador::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('contadores.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = Contador::findOrFail($id);
        try {
            $request->merge([
                'chave_pix' => $request->chave_pix ?? '',
                'conta' => $request->conta ?? '',
                'agencia' => $request->agencia ?? '',
                'banco' => $request->banco ?? '',
                'ie' => $request->ie ?? '',
                'dados_bancarios' => $request->dados_bancarios,
                'contador_parceiro' => $request->contador_parceiro,
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Editado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('contadores.index');
    }

    public function destroy($id)
    {
        $item = Contador::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com Sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('contadores.index');
    }

    public function show($id)
    {
        $item = Contador::findOrFail($id);
        $empresas = Empresa::where('contador_id', $id)->get();
        return view('contadores.show', compact('item', 'empresas'));
    }

    public function setEmpresa(Request $request)
    {
        $empresa = Empresa::findOrFail($request->empresa);
        $empresa->contador_id = $request->contador_id;
        $empresa->save();
        session()->flash('flash_sucesso', "Empresa atribuída");
        return redirect()->back();
    }
}
