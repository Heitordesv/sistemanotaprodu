<?php

namespace App\Http\Controllers;

use App\Http\Middleware\LimiteMDFe;
use App\Models\Cidade;
use App\Models\Ciot;
use App\Models\CTeDescarga;
use App\Models\InfoDescarga;
use App\Models\LacreTransporte;
use App\Models\LacreUnidadeCarga;
use App\Models\Mdfe;
use App\Models\MunicipioCarregamento;
use App\Models\NFeDescarga;
use App\Models\Percurso;
use App\Models\UnidadeCarga;
use App\Models\ValePedagio;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ConfigNota;
use App\Models\Venda;
use App\Services\MDFeService;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use NFePHP\DA\MDFe\Damdfe;

class MdfeController extends Controller
{
    public function __construct()
    {
        $this->middleware(LimiteMDFe::class)->only('create');
        if (!is_dir(public_path('xml_mdfe'))) {
            mkdir(public_path('xml_mdfe'), 0777, true);
        }
        if (!is_dir(public_path('xml_mdfe_cancelada'))) {
            mkdir(public_path('xml_mdfe_cancelada'), 0777, true);
        }
    }

    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $estado = $request->get('estado');
        $filial_id = $request->get('filial_id');
        $local_padrao = __get_local_padrao();
        if (!$filial_id && $local_padrao) {
            $filial_id = $local_padrao;
        }
        $data = Mdfe::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($estado), function ($query) use ($estado) {
            return $query->where('estado_emissao', $estado);
        })
        ->when($filial_id != 'todos', function ($query) use ($filial_id) {
            $filial_id = $filial_id == -1 ? null : $filial_id;
            return $query->where('filial_id', $filial_id);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('mdfe.index', compact('data', 'filial_id'));
    }

    public function create(Request $request)
    {
        $lastMdfe = Mdfe::lastMdfe();
        $veiculos = Veiculo::where('empresa_id', $request->empresa_id)->get();
        if (sizeof($veiculos) == 0) {
            session()->flash("flash_erro", "Cadastre um veiculo para criar uma MDFe!");
            return redirect()->route('veiculos.create');
        }
        $cidades = Cidade::all();
        return view('mdfe.create', compact('veiculos', 'cidades', 'lastMdfe'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            DB::transaction(function () use ($request) {
                $request->merge([
                    'seguradora_nome' => $request->seguradora_nome ?? '',
                    'seguradora_cnpj' => $request->seguradora_cnpj ?? '',
                    'numero_apolice' => $request->numero_apolice ?? '',
                    'numero_averbacao' => $request->numero_averbacao ?? '',
                    'numero_compra' => $request->numero_compra ?? 0,
                    'valor' => $request->valor ?? 0,
                    'encerrado' => false,
                    'estado_emissao' => 'novo',
                    'chave' => '',
                    'seg_cod_barras' => '',
                    'mdfe_numero' => 0,
                    'protocolo' => '',
                    'valor_carga' => __convert_value_bd($request->valor_carga),
                    'latitude_carregamento' => $request->latitude_carregamento ?? '',
                    'longitude_carregamento' => $request->longitude_carregamento ?? '',
                    'cep_descarrega' => $request->cep_descarrega ? str_replace("-", "", $request->cep_descarrega) : '',
                    'latitude_descarregamento' => $request->latitude_descarregamento ?? '',
                    'longitude_descarregamento' => $request->longitude_descarregamento ?? '',
                    'quantidade_rateio' => __convert_value_bd($request->quantidade_rateio),
                    'quantidade_rateio_carga' => __convert_value_bd($request->quantidade_rateio_carga),
                    'quantidade_carga' => __convert_value_bd($request->quantidade_carga),
                    'produto_pred_nome' => $request->produto_pred_nome ?? '',
                    'produto_pred_ncm' => $request->produto_pred_ncm ?? '',
                    'produto_pred_cod_barras' => $request->produto_pred_cod_barras ?? '',
                    'cep_carrega' => $request->cep_carrega ? str_replace("-", "", $request->cep_carrega) : '',
                    'tp_carga' => $request->tp_carga ?? '',
                    'info_complementar' => $request->info_complementar ?? '',
                    'info_adicional_fisco' => $request->info_adicional_fisco ?? '',
                    'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                ]);

                $mdfe = Mdfe::create($request->all());

                for ($i = 0; $i < sizeof($request->municipiosCarregamento); $i++) {
                    MunicipioCarregamento::create([
                        'mdfe_id' => $mdfe->id,
                        'cidade_id' => $request->municipiosCarregamento[$i]
                    ]);
                }

                for ($i = 0; $i < sizeof($request->codigo_ciot); $i++) {
                    if ($request->codigo_ciot[$i] != null) {
                        Ciot::create([
                            'mdfe_id' => $mdfe->id,
                            'cpf_cnpj' => $request->cpf_cnpj[$i],
                            'codigo' => $request->codigo_ciot[$i]
                        ]);
                    }
                }

                for ($i = 0; $i < sizeof($request->uf); $i++) {
                    if ($request->uf[$i]) {
                        Percurso::create([
                            'uf' => $request->uf[$i],
                            'mdfe_id' => $mdfe->id
                        ]);
                    }
                }

                for ($i = 0; $i < sizeof($request->cnpj_fornecedor); $i++) {
                    if ($request->cnpj_fornecedor[$i] != null) {
                        ValePedagio::create([
                            'mdfe_id' => $mdfe->id,
                            'cnpj_fornecedor' => $request->cnpj_fornecedor[$i],
                            'cnpj_fornecedor_pagador' => $request->cnpj_fornecedor_pagador[$i],
                            'numero_compra' => $request->numero_compra[$i],
                            'valor' => $request->valor_pedagio[$i]
                        ]);
                    }
                }

                for ($i = 0; $i < sizeof($request->tp_und_transp_row); $i++) {
                    $info = InfoDescarga::create([
                        'mdfe_id' => $mdfe->id,
                        'tp_unid_transp' => $request->tp_und_transp_row[$i],
                        'id_unid_transp' => $request->id_und_transp_row[$i],
                        'quantidade_rateio' => $request->quantidade_rateio_row[$i],
                        'cidade_id' => $request->municipio_descarregamento_row[$i]
                    ]);

                    if ($request->chave_cte_row[$i]) {

                        CTeDescarga::create([
                            'info_id' => $info->id,
                            'chave' => $request->chave_cte[$i][$i],
                            'seg_cod_barras' => ''
                        ]);
                    }

                    if ($request->chave_nfe_row[$i]) {
                        NFeDescarga::create([
                            'info_id' => $info->id,
                            'chave' =>  $request->chave_nfe_row[$i],
                            'seg_cod_barras' => ''
                        ]);
                    }

                    $lacres = json_decode($request->lacres_transporte_row[$i]);
                    foreach ($lacres as $l) {
                        LacreTransporte::create([
                            'info_id' => $info->id,
                            'numero' => $l
                        ]);
                    }

                    $lacres = json_decode($request->lacres_unidade_row[$i]);
                    foreach ($lacres as $l) {
                        LacreUnidadeCarga::create([
                            'info_id' => $info->id,
                            'numero' => $l
                        ]);
                    }

                    if ($request->quantidade_rateio_carga_row[$i] != "") {
                        UnidadeCarga::create([
                            'info_id' => $info->id,
                            'id_unidade_carga' => $request->id_und_transp_row[$i],
                            'quantidade_rateio' => __convert_value_bd($request->quantidade_rateio_carga_row[$i])
                        ]);
                    }
                }
            });
session()->flash("flash_sucesso", "MDFe adicionada com sucesso!");
} catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
    session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
    __saveLogError($e, request()->empresa_id);
}
return redirect()->route('mdfe.index');
}

public function edit($id)
{
    $item = Mdfe::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    $lastMdfe = Mdfe::lastMdfe();
    $veiculos = Veiculo::where('empresa_id', request()->empresa_id)->get();
    $cidades = Cidade::all();
    return view('mdfe.edit', compact('item', 'lastMdfe', 'veiculos', 'cidades'));
}

public function update(Request $request, $id)
{
    $this->_validate($request);
    $item = Mdfe::findOrFail($id);
    try {
        $request->merge([
            'seguradora_nome' => $request->seguradora_nome ?? '',
            'seguradora_cnpj' => $request->seguradora_cnpj ?? '',
            'numero_apolice' => $request->numero_apolice ?? '',
            'numero_averbacao' => $request->numero_averbacao ?? '',
            'numero_compra' => $request->numero_compra ?? 0,
            'valor' => $request->valor ?? 0,
            'encerrado' => false,
            'chave' => '',
            'seg_cod_barras' => '',
            'mdfe_numero' => 0,
            'protocolo' => '',
            'valor_carga' => __convert_value_bd($request->valor_carga),
            'latitude_carregamento' => $request->latitude_carregamento ?? '',
            'longitude_carregamento' => $request->longitude_carregamento ?? '',
            'cep_descarrega' => $request->cep_descarrega ? str_replace("-", "", $request->cep_descarrega) : '',
            'latitude_descarregamento' => $request->latitude_descarregamento ?? '',
            'longitude_descarregamento' => $request->longitude_descarregamento ?? '',
            'quantidade_rateio' => __convert_value_bd($request->quantidade_rateio),
            'quantidade_rateio_carga' => __convert_value_bd($request->quantidade_rateio_carga),
            'quantidade_carga' => __convert_value_bd($request->quantidade_carga),
            'produto_pred_nome' => $request->produto_pred_nome ?? '',
            'produto_pred_ncm' => $request->produto_pred_ncm ?? '',
            'produto_pred_cod_barras' => $request->produto_pred_cod_barras ?? '',
            'cep_carrega' => $request->cep_carrega ? str_replace("-", "", $request->cep_carrega) : '',
            'tp_carga' => $request->tp_carga ?? '',
            'info_complementar' => $request->info_complementar ?? '',
            'info_adicional_fisco' => $request->info_adicional_fisco ?? '',
            'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
        ]);
        $item->fill($request->all())->save();

        $item->municipiosCarregamento()->delete();
        $item->ciots()->delete();
        $item->percurso()->delete();
        $item->valesPedagio()->delete();
        $item->infoDescarga()->delete();

        for ($i = 0; $i < sizeof($request->municipiosCarregamento); $i++) {
            MunicipioCarregamento::create([
                'mdfe_id' => $item->id,
                'cidade_id' => $request->municipiosCarregamento[$i]
            ]);
        }

        for ($i = 0; $i < sizeof($request->codigo_ciot); $i++) {
            if ($request->codigo_ciot[$i] != null) {
                Ciot::create([
                    'mdfe_id' => $item->id,
                    'cpf_cnpj' => $request->cpf_cnpj[$i],
                    'codigo' => $request->codigo_ciot[$i]
                ]);
            }
        }

        if ($request->uf != null) {
            for ($i = 0; $i < sizeof($request->uf); $i++) {
                if ($request->uf[$i]) {
                    Percurso::create([
                        'uf' => $request->uf[$i],
                        'mdfe_id' => $item->id
                    ]);
                }
            }
        }

        for ($i = 0; $i < sizeof($request->cnpj_fornecedor); $i++) {
            if ($request->cnpj_fornecedor[$i] != null) {
                ValePedagio::create([
                    'mdfe_id' => $item->id,
                    'cnpj_fornecedor' => $request->cnpj_fornecedor[$i],
                    'cnpj_fornecedor_pagador' => $request->cnpj_fornecedor_pagador[$i],
                    'numero_compra' => $request->numero_compra[$i],
                    'valor' => __convert_value_bd($request->valor_pedagio[$i])
                ]);
            }
        }

        for ($i = 0; $i < sizeof($request->tp_und_transp_row); $i++) {

            $info = InfoDescarga::create([
                'mdfe_id' => $item->id,
                'tp_unid_transp' => $request->tp_und_transp_row[$i],
                'id_unid_transp' => $request->id_und_transp_row[$i],
                'quantidade_rateio' => $request->quantidade_rateio_row[$i],
                'cidade_id' => $request->municipio_descarregamento_row[$i]
            ]);

            if ($request->chave_cte_row[$i]) {
                CTeDescarga::create([
                    'info_id' => $info->id,
                    'chave' => $request->chave_cte[$i],
                    'seg_cod_barras' => ''
                ]);
            }

            if ($request->chave_nfe_row[$i]) {
                NFeDescarga::create([
                    'info_id' => $info->id,
                    'chave' =>  $request->chave_nfe_row[$i],
                    'seg_cod_barras' => ''
                ]);
            }

            $lacres = json_decode($request->lacres_transporte_row[$i]);
            foreach ($lacres as $l) {
                LacreTransporte::create([
                    'info_id' => $info->id,
                    'numero' => $l
                ]);
            }

            $lacres = json_decode($request->lacres_unidade_row[$i]);
            foreach ($lacres as $l) {
                LacreUnidadeCarga::create([
                    'info_id' => $info->id,
                    'numero' => $l
                ]);
            }

            if ($request->quantidade_rateio_carga_row[$i] != "") {
                UnidadeCarga::create([
                    'info_id' => $info->id,
                    'id_unidade_carga' => $request->id_und_transp_row[$i],
                    'quantidade_rateio' => __convert_value_bd($request->quantidade_rateio_carga_row[$i])
                ]);
            }
        }

        session()->flash("flash_sucesso", "Mdfe atualizada com sucesso!");
    } catch (\Exception $e) {
        echo $e->getMessage() . '<br>' . $e->getLine();
        die;
        session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
        __saveLogError($e, request());
    }
    return redirect()->route('mdfe.index');
}

public function destroy($id)
{
    $item = Mdfe::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    try {
        $item->delete();
        session()->flash('flash_sucesso', 'Apagado com sucesso!');
    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }
    return redirect()->route('mdfe.index');
}

private function _validate(Request $request)
{
    $rules = [
        'data_inicio_viagem' => 'required',
        'lac_rodo' => 'required',
        'cnpj_contratante' => 'required',
        'quantidade_carga' => 'required|max:10',
        'valor_carga' => 'required',
        'uf' => 'required',
        'condutor_nome' => 'required',
        'condutor_cpf' => 'required',
        'tp_transp' => 'required',
    ];
    $messages = [
        'data_inicio_viagem.required' => 'Data é Obrigatória',
        'lac_rodo.required' => 'Campo Obrigatório',
        'cnpj_contratante.required' => 'Campo Obrigatório',
        'quantidade_carga.required' => 'Campo Obrigatório',
        'quantidade_carga.max' => 'Máximo 8 caracteres',
        'valor_carga.required' => 'Campo Obrigatório',
        'uf.required' => 'Campo Obrigatório',
        'condutor_nome.required' => 'Campo Obrigatório',
        'condutor_cpf.required' => 'Campo Obrigatório',
        'tp_transp.required' => 'Campo Obrigatório',
        'seg_cod_barras.required' => 'Campo Obrigatório',
    ];
    $this->validate($request, $rules, $messages);
}

public function naoEncerrados()
{
    $config = ConfigNota::where('empresa_id', request()->empresa_id)
    ->first();

    if ($config->arquivo == null) {
        session()->flash("flash_erro", "Configure o certificado!");
        return redirect()->back();
    }

    $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

    $mdfe_service = new MDFeService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$config->ambiente,
        "razaosocial" => $config->razao_social,
        "siglaUF" => $config->cidade->uf,
        "cnpj" => $cnpj,
        "inscricaomunicipal" => $config->inscricao_municipal,
        "codigomunicipio" => $config->cidade->codigo,
        "schemes" => "PL_MDFe_300a",
        "versao" => '3.00'
    ], $config);
    $resultados = $mdfe_service->naoEncerrados();
    $naoEncerrados = [];
    if ($resultados['xMotivo'] != 'Consulta não encerrados não localizou MDF-e nessa situação') {
        if (isset($resultados['infMDFe'])) {
                // if(sizeof($resultados['infMDFe']) == 2){
            if (!isset($resultados['infMDFe'][1])) {
                $array = [
                    'chave' => $resultados['infMDFe']['chMDFe'],
                    'protocolo' => $resultados['infMDFe']['nProt'],
                    'numero' => 0,
                    'data' => '',
                    'local' => ''
                ];
                array_push($naoEncerrados, $array);
            } else {
                foreach ($resultados['infMDFe'] as $inf) {

                    $array = [
                        'chave' => $inf['chMDFe'],
                        'protocolo' => $inf['nProt'],
                        'numero' => 0,
                        'data' => '',
                        'local' => ''
                    ];
                    array_push($naoEncerrados, $array);
                }
            }
        }
    }
    $data = $this->percorreDatabaseNaoEncerrados($naoEncerrados);
    return view('mdfe.nao_encerrados', compact('data'));
}

private function percorreDatabaseNaoEncerrados($naoEncerrados)
{
    for ($aux = 0; $aux < count($naoEncerrados); $aux++) {
        $mdfe = Mdfe::where('chave', $naoEncerrados[$aux]['chave'])
        ->where('empresa_id', request()->empresa_id)
        ->first();

        if ($mdfe != null) {

            $naoEncerrados[$aux]['data'] = $mdfe->created_at;
            $naoEncerrados[$aux]['numero'] = $mdfe->mdfe_numero;
            $naoEncerrados[$aux]['local'] = $mdfe->filial ? $mdfe->filial->descricao : 'Matriz';
        }
    }
    return $naoEncerrados;
}

public function xmlTemp($id)
{
    $item = Mdfe::findOrFail($id);
    $config = ConfigNota::where('empresa_id', request()->empresa_id)
    ->first();
    $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
    $mdfe_service = new MDFeService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$config->ambiente,
        "razaosocial" => $config->razao_social,
        "siglaUF" => $config->cidade->uf,
        "cnpj" => $cnpj,
        "inscricaomunicipal" => $config->inscricao_municipal,
        "codigomunicipio" => $config->cidade->codigo,
        "schemes" => "PL_MDFe_300a",
        "versao" => '3.00'
    ], $config);
    $mdfe = $mdfe_service->gerar($item);
    if (!isset($mdfe['erros_xml'])) {
        $xml = $mdfe['xml'];
        return response($xml)
        ->header('Content-Type', 'application/xml');
    } else {

        foreach ($mdfe['erros_xml'] as $err) {
            echo $err;
        }
    }
}

public function estadoFiscal($id)
{
    $item = Mdfe::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    return view('mdfe.estado_fiscal', compact('item'));
}

public function estadoFiscalStore(Request $request)
{
    try {
        $mdfe = Mdfe::findOrFail($request->mdfe_id);
        $estado_emissao = $request->estado_emissao;
        $mdfe->estado_emissao = $estado_emissao;

        if ($request->hasFile('xml')) {
            $xml = simplexml_load_file($request->xml);
            $chave = substr($xml->infMDFe->attributes()->Id, 4, 44);
            $file = $request->xml;
            $file->move(public_path('xml_mdfe'), $chave . '.xml');
            $mdfe->chave = $chave;
            $mdfe->mdfe_numero = $xml->infMDFe->ide->nMDF;
        }
        $mdfe->save();
        session()->flash("flash_sucesso", "Estado alterado");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Aldo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }
    return redirect()->back();
}


public function encerrar(Request $request)
{
    $config = ConfigNota::where('empresa_id', request()->empresa_id)
    ->first();
    $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
    $mdfe_service = new MDFeService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$config->ambiente,
        "razaosocial" => $config->razao_social,
        "siglaUF" => $config->cidade->uf,
        "cnpj" => $cnpj,
        "inscricaomunicipal" => $config->inscricao_municipal,
        "codigomunicipio" => $config->cidade->codigo,
        "schemes" => "PL_MDFe_300a",
        "versao" => '3.00'
    ], $config);
    $mdfe = Mdfe::where('chave', $request->chave)
    ->where('empresa_id', request()->empresa_id)
    ->first();
    $resp = $mdfe_service->encerrar($request->chave, $request->protocolo);
        // dd($resp->infEvento);
        // dd($resp->infEvento->cStat);
    if ($resp->infEvento->cStat != 135) {
        session()->flash("flash_erro", $resp->infEvento->xMotivo);
        return redirect()->back();
    }
    if ($mdfe != null) {
        $mdfe->encerrado = true;
        $mdfe->save();
    }
    session()->flash("flash_sucesso", $resp->infEvento->xMotivo);
    return redirect()->back();
}


public function imprimir($id)
{
    $item = Mdfe::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    if (file_exists(public_path('xml_mdfe/') . $item->chave . '.xml')) {
        $xml = file_get_contents(public_path('xml_mdfe/') . $item->chave . '.xml');
        try {
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
            if ($config->logo) {
                $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('uploads/configEmitente/') . $config->logo));
            } else {
                $logo = null;
            }
            try {
                    // dd($xml);
                $damdfe = new Damdfe($xml);
                $damdfe->debugMode(true);
                // $damdfe->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
                // $damdfe->printParameters('L');
                $pdf = $damdfe->render();
                // header('Content-Type: application/pdf');
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (Exception $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } catch (InvalidArgumentException $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    } else {
        echo "Arquivo não encontrado!";
    }
}

public function createByVendas($id)
{
    $item = Venda::find($id);
    $lastMdfe = Mdfe::lastMdfe();
    $veiculos = Veiculo::where('empresa_id', request()->empresa_id)->get();
    if (sizeof($veiculos) == 0) {
        session()->flash("flash_erro", "Cadastre um veiculo para criar uma MDFe!");
        return redirect()->route('veiculos.create');
    }
    $cidades = Cidade::all();
    return view('mdfe.importaNfe.create', compact('id', 'lastMdfe', 'veiculos', 'cidades'));
}

public function enviarXml(Request $request)
{
    $email = $request->email;
    $id = $request->mdfe_id;
    $mdfe = Mdfe::find($id);
    if (!file_exists(public_path('xml_mdfe/') . $mdfe->chave . '.xml')) {
        session()->flash('flash_erro', 'Arquivo não encontrado');
        return redirect()->back();
    }
    $this->criarPdfParaEnvio($mdfe);
    $value = session('user_logged');
    Mail::send('mail.xml_send_mdfe', ['emissao' => $mdfe->created_at, 'mdfe' => $mdfe->numero, 'usuario' => $value['nome']], function ($m) use ($mdfe, $email) {
        $nomeEmpresa = env('MAIL_NAME');
        $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
        $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
        $emailEnvio = env('MAIL_USERNAME');
        $m->from($emailEnvio, $nomeEmpresa);
        $m->subject('Envio de XML MDF-e ' . $mdfe->numero);
        $m->attach(public_path('xml_mdfe/') . $mdfe->chave . '.xml');
        $m->attach(public_path('pdf/') . 'MDFe.pdf');
        $m->to($email);
    });
    return "ok";
}

private function criarPdfParaEnvio($mdfe)
{
    $xml = file_get_contents(public_path('xml_mdfe/') . $mdfe->chave . '.xml');
        // $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('') . 'imgs/logo.jpg'));
        // $docxml = FilesFolders::readFile($xml);
    try {
        $damdfe = new Damdfe($xml);
        $damdfe->debugMode(true);
        $damdfe->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
        $pdf = $damdfe->render();
        header('Content-Type: application/pdf');
        file_put_contents(public_path('pdf/') . 'MDFe.pdf', $pdf);
    } catch (InvalidArgumentException $e) {
        echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
    }
}

public function baixarXml($id)
{
    $mdfe = Mdfe::find($id);
    if ($mdfe) {
            // $public = env('SERVIDOR_WEB') ? 'public/' : '';
        if (file_exists(public_path('xml_mdfe/') . $mdfe->chave . '.xml')) {
            return response()->download(public_path('xml_mdfe/') . $mdfe->chave . '.xml');
        } else {
            echo "Arquivo XML não encontrado!!";
        }
    } else {
        return redirect('/403');
    }
}
}
