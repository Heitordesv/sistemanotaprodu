<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EscritorioContabil;
use App\Models\Cidade;

class EscritorioController extends Controller
{
    public function index(Request $request)
    {
        $item = EscritorioContabil::where('empresa_id', $request->empresa_id)
            ->first();
        $cidades = Cidade::all();
        return view('escritorio_contabil.index', compact('item', 'cidades'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        $item = EscritorioContabil::where('empresa_id', $request->empresa_id)
            ->first();
        $request->merge(['envio_automatico_xml_contador' => $request->envio_automatico_xml_contador ? 1 : 0]);
        $request->merge(['token_sieg' => $request->token_sieg ?? '']);
        try {
            if ($item == null) {
                EscritorioContabil::create($request->all());
                session()->flash("flash_sucesso", "Escritório cadastrado!");
            } else {
                $item->fill($request->all())->save();
                session()->flash("flash_sucesso", "Escritório atualizado!");
            }
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('escritorio.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'razao_social' => 'required|max:100',
            'nome_fantasia' => 'required|max:80',
            'cnpj' => 'required',
            'ie' => 'required|max:20',
            'logradouro' => 'required|max:80',
            'numero' => 'required|max:10',
            'bairro' => 'required|max:50',
            'fone' => 'required',
            'cep' => 'required',
            'email' => 'required|max:80|email',
            'cidade_id' => 'required',
        ];
        $messages = [
            'razao_social.required' => 'Campo obrigatório.',
            'nome_fantasia.required' => 'Campo obrigatório.',
            'cnpj.required' => 'Campo obrigatório.',
            'ie.required' => 'Campo obrigatório.',
            'logradouro.required' => 'Campo obrigatório.',
            'numero.required' => 'Campo obrigatório.',
            'bairro.required' => 'Campo obrigatório.',
            'fone.required' => 'Campo obrigatório.',
            'cep.required' => 'Campo obrigatório.',
            'email.required' => 'Campo obrigatório.',
            'cidade_id.required' => 'Campo obrigatório.',
            'email.max' => '100 caracteres permitidos.',
            'nome_fantasia.max' => '80 caracteres permitidos.',
            'ie.max' => '20 caracteres permitidos.',
            'logradouro.max' => '80 caracteres permitidos.',
            'numero.max' => '10 caracteres permitidos.',
            'bairro.max' => '50 caracteres permitidos.',
            'email.max' => '80 caracteres permitidos.',
            'email.email' => 'Informe um email valido.',
        ];
        $this->validate($request, $rules, $messages);
    }
}
