<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Nfse;
use App\Models\NfseServico;
use App\Models\OrdemServico;
use App\Models\Servico;
use App\Models\ConfigNota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NfseController extends Controller
{ 

    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $estado = $request->get('estado_emissao');
        $data = Nfse::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($estado), function ($query) use ($estado) {
            return $query->where('estado_emissao', $estado);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('nfse.index', compact('data'));
    }

    public function create()
    {
        $cidades = Cidade::all();
        $servicos = Servico::where('empresa_id', request()->empresa_id)->get();

        $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
        return view('nfse.create', compact('cidades', 'servicos', 'config'));
    }

    public function edit($id)
    {
        $item = Nfse::findOrFail($id);
        $servicos = Servico::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        return view('nfse.edit', compact('item', 'servicos', 'cidades'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $result = DB::transaction(function () use ($request) {
                $totalServico = __convert_value_bd($request->valor_servico);
                $nfse = Nfse::create([
                    'empresa_id' => $request->empresa_id,
                    // 'filial_id' => NULL,
                    'valor_total' => $totalServico,
                    'estado_emissao' => 'novo',
                    'serie' => '',
                    'codigo_verificacao' => '',
                    'numero_nfse' => 0,
                    'url_xml' => '',
                    'url_pdf_nfse' => '',
                    'url_pdf_rps' => '',
                    'cliente_id' => $request->cliente_id,
                    'natureza_operacao' => $request->natureza_operacao,
                    'documento' => $request->documento,
                    'razao_social' => $request->razao_social,
                    'im' => $request->im ?? '',
                    'ie' => $request->ie ?? '',
                    'cep' => $request->cep ?? '',
                    'rua' => $request->rua,
                    'numero' => $request->numero,
                    'bairro' => $request->bairro,
                    'complemento' => $request->complemento ?? '',
                    'cidade_id' => $request->cidade_id,
                    'email' => $request->email ?? '',
                    'telefone' => $request->telefone ?? ''
                ]);
                NfseServico::create([
                    'nfse_id' => $nfse->id,
                    'discriminacao' => $request->discriminacao,
                    'valor_servico' => __convert_value_bd($request->valor_servico),
                    'servico_id' => $request->servico_id,
                    'codigo_cnae' => $request->codigo_cnae ?? '',
                    'codigo_servico' => $request->codigo_servico ?? '',
                    'codigo_tributacao_municipio' => $request->codigo_tributacao_municipio ?? '',
                    'exigibilidade_iss' => $request->exigibilidade_iss,
                    'iss_retido' => $request->iss_retido,
                    'data_competencia' => $request->data_competencia ?? null,
                    'estado_local_prestacao_servico' => $request->estado_local_prestacao_servico ?? '',
                    'cidade_local_prestacao_servico' => $request->cidade_local_prestacao_servico ?? '',
                    'valor_deducoes' => $request->valor_deducoes ? __convert_value_bd($request->valor_deducoes) : 0,
                    'desconto_incondicional' => $request->desconto_incondicional ? __convert_value_bd($request->desconto_incondicional) : 0,
                    'desconto_condicional' => $request->desconto_condicional ? __convert_value_bd($request->desconto_condicional) : 0,
                    'outras_retencoes' => $request->outras_retencoes ? __convert_value_bd($request->outras_retencoes) : 0,
                    'aliquota_iss' => $request->aliquota_iss ? __convert_value_bd($request->aliquota_iss) : 0,
                    'aliquota_pis' => $request->aliquota_pis ? __convert_value_bd($request->aliquota_pis) : 0,
                    'aliquota_cofins' => $request->aliquota_cofins ? __convert_value_bd($request->aliquota_cofins) : 0,
                    'aliquota_inss' => $request->aliquota_inss ? __convert_value_bd($request->aliquota_inss) : 0,
                    'aliquota_ir' => $request->aliquota_ir ? __convert_value_bd($request->aliquota_ir) : 0,
                    'aliquota_csll' => $request->aliquota_csll ? __convert_value_bd($request->aliquota_csll) : 0,
                    'intermediador' => $request->intermediador ?? 'n',
                    'documento_intermediador' => $request->documento_intermediador ?? '',
                    'nome_intermediador' => $request->nome_intermediador ?? '',
                    'im_intermediador' => $request->im_intermediador ?? '',
                    'responsavel_retencao_iss' => $request->responsavel_retencao_iss ?? '',
                ]);

                if (isset($request->os_id)) {
                    $ordem = OrdemServico::findOrFail($request->os_id);
                    $ordem->nfse_id = $nfse->id;
                    $ordem->save();
                }
            });
session()->flash('flash_sucesso', 'Nfse criada');
} catch (\Exception $e) {
    __saveLogError($e, request()->empresa_id);
    // echo $e->getMessage();
    // die;
    session()->flash('flash_erro', 'Algo deu errado!', $e->getMessage());
}
return redirect()->route('nfse.index');
}

public function update(Request $request, $id){
    $this->_validate($request);
    $item = Nfse::findOrFail($id);
    try{
        $result = DB::transaction(function () use ($request, $item) {

            $totalServico = (float)__convert_value_bd($request->valor_servico);

            $request->merge([
                'valor_total' => $totalServico
            ]);

            $item->fill($request->all())->update();

            $item->servico->delete();
            NfseServico::create([
                'nfse_id' => $item->id,
                'discriminacao' => $request->discriminacao,
                'valor_servico' => __convert_value_bd($request->valor_servico),
                'servico_id' => $request->servico_id,
                'codigo_cnae' => $request->codigo_cnae ?? '',
                'codigo_servico' => $request->codigo_servico ?? '',
                'codigo_tributacao_municipio' => $request->codigo_tributacao_municipio ?? '',
                'exigibilidade_iss' => $request->exigibilidade_iss,
                'iss_retido' => $request->iss_retido,
                'data_competencia' => $request->data_competencia ?? null,
                'estado_local_prestacao_servico' => $request->estado_local_prestacao_servico ?? '',
                'cidade_local_prestacao_servico' => $request->cidade_local_prestacao_servico ?? '',
                'valor_deducoes' => $request->valor_deducoes ? __convert_value_bd($request->valor_deducoes) : 0,
                'desconto_incondicional' => $request->desconto_incondicional ? __convert_value_bd($request->desconto_incondicional) : 0,
                'desconto_condicional' => $request->desconto_condicional ? __convert_value_bd($request->desconto_condicional) : 0,
                'outras_retencoes' => $request->outras_retencoes ? __convert_value_bd($request->outras_retencoes) : 0,
                'aliquota_iss' => $request->aliquota_iss ? __convert_value_bd($request->aliquota_iss) : 0,
                'aliquota_pis' => $request->aliquota_pis ? __convert_value_bd($request->aliquota_pis) : 0,
                'aliquota_cofins' => $request->aliquota_cofins ? __convert_value_bd($request->aliquota_cofins) : 0,
                'aliquota_inss' => $request->aliquota_inss ? __convert_value_bd($request->aliquota_inss) : 0,
                'aliquota_ir' => $request->aliquota_ir ? __convert_value_bd($request->aliquota_ir) : 0,
                'aliquota_csll' => $request->aliquota_csll ? __convert_value_bd($request->aliquota_csll) : 0,
                'intermediador' => $request->intermediador ?? 'n',
                'documento_intermediador' => $request->documento_intermediador ?? '',
                'nome_intermediador' => $request->nome_intermediador ?? '',
                'im_intermediador' => $request->im_intermediador ?? '',
                'responsavel_retencao_iss' => $request->responsavel_retencao_iss ?? 1,

            ]);
        });
        session()->flash('mensagem_sucesso', 'Nfse atualizada!');

    }catch(\Exception $e){
        __saveLogError($e, $request->empresa_id);
            // echo $e->getLine();
            // die;
        session()->flash('mensagem_erro', 'Algo deu errado!');
    }
    return redirect()->route('nfse.index');
}

private function _validate(Request $request)
{
    $rules = [
        'cliente_id' => 'required',
        'natureza_operacao' => 'required',
        'razao_social' => 'required|max:80',
        'documento' => ['required'],
        'rua' => 'required|max:80',
        'numero' => 'required|max:10',
        'bairro' => 'required|max:50',
        'telefone' => 'max:20',
        'celular' => 'max:20',
        'email' => 'max:40',
        'cep' => 'required',
        'cidade_id' => 'required',
        'discriminacao' => 'required',
        'valor_servico' => 'required',
        'codigo_servico' => 'required',
    ];
    $messages = [
        'cliente.required' => 'Selecione',
        'razao_social.required' => 'O campo Razão social é obrigatório.',
        'natureza_operacao.required' => 'O campo Natureza de Operação é obrigatório.',
        'razao_social.max' => '100 caracteres maximos permitidos.',
        'nome_fantasia.required' => 'O campo Nome Fantasia é obrigatório.',
        'nome_fantasia.max' => '80 caracteres maximos permitidos.',
        'documento.required' => 'O campo CPF/CNPJ é obrigatório.',
        'rua.required' => 'O campo Rua é obrigatório.',
        'ie_rg.max' => '20 caracteres maximos permitidos.',
        'rua.max' => '80 caracteres maximos permitidos.',
        'numero.required' => 'O campo Numero é obrigatório.',
        'cep.required' => 'O campo CEP é obrigatório.',
        'cidade_id.required' => 'O campo Cidade é obrigatório.',
        'numero.max' => '10 caracteres maximos permitidos.',
        'bairro.required' => 'O campo Bairro é obrigatório.',
        'bairro.max' => '50 caracteres maximos permitidos.',
        'telefone.required' => 'O campo Celular é obrigatório.',
        'telefone.max' => '20 caracteres maximos permitidos.',
        'celular.required' => 'O campo Celular 2 é obrigatório.',
        'celular.max' => '20 caracteres maximos permitidos.',
        'email.required' => 'O campo Email é obrigatório.',
        'email.max' => '40 caracteres maximos permitidos.',
        'email.email' => 'Email inválido.',
        'discriminacao.required' => 'Campo obrigatório.',
        'valor_servico.required' => 'Campo obrigatório.',
        'codigo_servico.required' => 'Campo obrigatório.',
    ];
    $this->validate($request, $rules, $messages);
}

public function destroy($id)
{
    $item = Nfse::findOrFail($id);
    try {
        $item->servico()->delete();
        $item->delete();
        session()->flash('flash_sucesso', 'Nfse removida!');
    } catch (\Exception $e) {
        __saveLogError($e, request()->empresa_id);
        session()->flash('flash_erro', 'Algo deu errado!');
    }
    return redirect()->back();
}

public function imprimir($id){
    $item = Nfse::findOrFail($id);
    if(valida_objeto($item)){
        return redirect($item->url_pdf_nfse);
    }else{
        return redirect('/403');
    }
}

}
