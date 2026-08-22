<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDespesaCte;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ComponenteCte;
use App\Models\Cte;
use App\Models\CTeDescarga;
use App\Models\MedidaCte;
use App\Models\NaturezaOperacao;
use App\Models\Veiculo;
use App\Models\ChaveNfeCte;
use Illuminate\Http\Request;
use App\Models\ConfigNota;
use App\Models\DespesaCte;
use App\Models\ReceitaCte;
use App\Models\Tributacao;
use Illuminate\Support\Facades\DB;
use App\Services\CTeService;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use NFePHP\DA\CTe\Dacte;
use NFePHP\DA\CTe\Daevento;

use function PHPUnit\Framework\returnSelf;

class CteController extends Controller
{
    public function index(Request $request)
    {
        if (!is_dir(public_path('xml_cte'))) {
            mkdir(public_path('xml_cte'), 0777, true);
        }
        if (!is_dir(public_path('xml_cte_cancelada'))) {
            mkdir(public_path('xml_cte_cancelada'), 0777, true);
        }
        if (!is_dir(public_path('xml_cte_correcao'))) {
            mkdir(public_path('xml_cte_correcao'), 0777, true);
        }
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $estado = $request->get('estado');
        $filial_id = $request->get('filial_id');
        $local_padrao = __get_local_padrao();
        if (!$filial_id && $local_padrao) {
            $filial_id = $local_padrao;
        }
        $data = Cte::where('empresa_id', $request->empresa_id)
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
            ->orderBy('created_at', 'asc')
            ->paginate(env("PAGINACAO"));
        return view('cte.index', compact('data', 'filial_id'));
    }

    public function create()
    {
        $cidades = Cidade::all();
        $veiculos = Veiculo::where('empresa_id', request()->empresa_id)->get();
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        $unidadesMedida = Cte::unidadesMedida();
        $tiposMedida = Cte::tiposMedida();

        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();

        $lastCte = $config->ultimo_numero_cte++;

        return view('cte.create', compact(
            'naturezas',
            'clientes',
            'veiculos',
            'cidades',
            'unidadesMedida',
            'tiposMedida',
            'lastCte',
            'config'
        ));
    }

    public function store(Request $request)
    {
        if (!isset($request->validate)) {
            $this->_validate($request);
        }
        try {
            $result = DB::transaction(function () use ($request) {

                // $valorComponente = 0;
                // for ($i = 0; $i < sizeof($request->nome_componente); $i++) {
                //     $valorComponente += __convert_value_bd($request->valor_componente[$i]);
                // }

                // $qtdCarga = 0;
                // for ($i = 0; $i < sizeof($request->tipo_medida); $i++) {
                //     $qtdCarga += __convert_value_bd($request->quantidade_carga[$i]);
                // }

                $request->merge([
                    'usuario_id' => get_id_user(),
                    'sequencia_cce' => 0,
                    'chave' => '',
                    'tpDoc' => $request->tpDoc ?? '',
                    'estado_emissao' => 'novo',
                    'descOutros' => $request->descOutros ?? '',
                    'nDoc' => $request->nDoc ?? '',
                    'vDocFisc' => $request->vDocFisc ? __convert_value_bd($request->vDocFisc) : 0,
                    'valor_carga' => __convert_value_bd($request->valor_carga),
                    // 'valor_componente' => $valorComponente,
                    'valor_transporte' => __convert_value_bd($request->valor_transporte) ?? 0,
                    'valor_receber' => __convert_value_bd($request->valor_receber) ?? 0,
                    'detalhes_retira' => $request->detalhes_retira ?? '',
                    'observacao' => $request->observacao ?? '',
                    // 'quantidade_carga' => $qtdCarga,
                    'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                ]);

                $cte = Cte::create($request->all());
                for ($i = 0; $i < sizeof($request->nome_componente); $i++) {

                    ComponenteCte::create([
                        'nome' => $request->nome_componente[$i],
                        'valor' => __convert_value_bd($request->valor_componente[$i]),
                        'cte_id' => $cte->id
                    ]);
                }

                for ($i = 0; $i < sizeof($request->tipo_medida); $i++) {
                    MedidaCte::create([
                        'cte_id' => $cte->id,
                        'tipo_medida' => $request->tipo_medida[$i],
                        'quantidade_carga' => __convert_value_bd($request->quantidade_carga[$i]),
                        'cod_unidade' => $request->cod_unidade[$i]
                    ]);
                }
                for ($i = 0; $i < sizeof($request->chave_nfe); $i++) {
                    if (strlen($request->chave_nfe[$i]) > 0) {
                        ChaveNfeCte::create([
                            'cte_id' => $cte->id,
                            'chave' => str_replace(" ", "", $request->chave_nfe[$i])
                        ]);
                    }
                }
            });
            session()->flash("flash_sucesso", "CTe cadastrado!");
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('cte.index');
    }

    public function edit($id)
    {
        $item = Cte::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $cidades = Cidade::all();

        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();

        $veiculos = Veiculo::where('empresa_id', request()->empresa_id)->get();
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();

        $unidadesMedida = Cte::unidadesMedida();
        $tiposMedida = Cte::tiposMedida();
        $lastCte = $config->ultimo_numero_cte++;

        return view('cte.edit', compact(
            'naturezas',
            'clientes',
            'veiculos',
            'lastCte',
            'config',
            'cidades',
            'unidadesMedida',
            'tiposMedida',
            'item'
        ));
    }

    private function _validate(Request $request)
    {
        $rules = [
            'data_prevista_entrega' => 'required',
            'veiculo_id' => 'required',
            'perc_icms' => 'required',
            'remetente_id' => 'required',
            'destinatario_id' => 'required',
            'valor_transporte' => 'required',
            'valor_receber' => 'required',
            'produto_predominante' => 'required'
        ];
        $message = [
            'data_prevista_entrega.required' => 'Campo Obrigatório',
            'veiculo_id.required' => 'Campo Obrigatório',
            'perc_icms.required' => 'Campo Obrigatório',
            'remetente_id.required' => 'Campo Obrigatório',
            'destinatario_id.required' => 'Campo Obrgatório',
            'valor_transporte.required' => 'Campo Obrigatório',
            'valor_receber.required' => 'Campo Obrigatório',
            'produto_predominante.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $message);
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        try {
            $result = DB::transaction(function () use ($request, $id) {
                $item = Cte::findOrFail($id);
                $request->merge([
                    'descOutros' => $request->descOutros ?? '',
                    'nDoc' => $request->nDoc ?? '',
                    'vDocFisc' => $request->vDocFisc ? __convert_value_bd($request->vDocFisc) : 0,
                    'valor_carga' => __convert_value_bd($request->valor_carga),
                    'valor_transporte' => __convert_value_bd($request->valor_transporte) ?? 0,
                    'valor_receber' => __convert_value_bd($request->valor_receber) ?? 0,
                    'detalhes_retira' => $request->detalhes_retira ?? '',
                    'observacao' => $request->observacao ?? '',
                    'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                ]);
                $item->fill($request->all())->save();
                $item->componentes()->delete();
                $item->medidas()->delete();
                $item->chaves_nfe()->delete();
                for ($i = 0; $i < sizeof($request->nome_componente); $i++) {
                    ComponenteCte::create([
                        'nome' => $request->nome_componente[$i],
                        'valor' => __convert_value_bd($request->valor_componente[$i]),
                        'cte_id' => $item->id
                    ]);
                }
                for ($i = 0; $i < sizeof($request->tipo_medida); $i++) {
                    MedidaCte::create([
                        'cte_id' => $item->id,
                        'tipo_medida' => $request->tipo_medida[$i],
                        'quantidade_carga' => __convert_value_bd($request->quantidade_carga[$i]),
                        'cod_unidade' => $request->cod_unidade[$i]
                    ]);
                }
                for ($i = 0; $i < sizeof($request->chave_nfe); $i++) {
                    if (strlen($request->chave_nfe[$i]) > 0) {
                        ChaveNfeCte::create([
                            'cte_id' => $item->id,
                            'chave' => str_replace(" ", "", $request->chave_nfe[$i])
                        ]);
                    }
                }
            });
            session()->flash("flash_sucesso", "CTe atualizado!");
        } catch (\Exception $e) {
            // echo $e->getMessage();
            // die;
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('cte.index');
    }

    public function custos($id)
    {
        $item = Cte::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $categoria = CategoriaDespesaCte::where('empresa_id', request()->empresa_id)->get();
        return view('cte.custos', compact('item', 'categoria'));
    }

    public function destroy($id)
    {
        $item = Cte::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->componentes()->delete();
            $item->medidas()->delete();
            $item->chaves_nfe()->delete();
            $item->delete();
            session()->flash('flash_sucesso', 'Apagado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('cte.index');
    }

    public function storeDespesa(Request $request)
    {
        try {
            $result = DespesaCte::create([
                'descricao' => $request->descricao,
                'valor' => __convert_value_bd($request->valor),
                'categoria_id' => $request->categoria_id,
                'cte_id' => $request->cte_id
            ]);
            session()->flash('flash_sucesso', 'Despesa adicionada!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
        }
        return redirect()->route('cte.custos',  $request->cte_id);
    }

    public function deleteDespesa($id)
    {
        $item = DespesaCte::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function storeReceita(Request $request)
    {
        try {
            ReceitaCte::create([
                'descricao' => $request->descricao,
                'valor' => __convert_value_bd($request->valor),
                'cte_id' => $request->cte_id
            ]);
            session()->flash('flash_sucesso', 'Receita adicionada!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
        }
        return redirect()->route('cte.custos',  $request->cte_id);
    }

    public function deleteReceita($id)
    {
        $item = ReceitaCte::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function manifesto(Request $request)
    {
        $data = Cte::where('empresa_id', $request->empresa_id)->get();
        return view('cte.show', compact('data'));
    }

    public function detalhes($id)
    {
        $item = Cte::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        if (valida_objeto($item)) {
            $adm = session('user_logged');
        }
        return view("cte.detalhes", compact('adm', 'item'));
    }

    public function estadoFiscal($id)
    {
        $item = Cte::findOrFail($id);
        return view('cte.estado_fiscal', compact('item'));
    }

    public function estadoFiscalStore(Request $request)
    {
        $cte = Cte::findOrFail($request->cte_id);
        try {
            $estado_emissao = $request->estado_emissao;
            $cte->estado_emissao = $estado_emissao;
            if ($request->hasFile('xml')) {
                $xml = simplexml_load_file($request->xml);
                $chave = substr($xml->CTe->infCte->attributes()->Id, 3, 44);
                $file = $request->xml;
                $file->move(public_path('xml_cte/'), $chave . '.xml');
                $cte->chave = $chave;
                $cte->cte_numero = $xml->CTe->infCte->ide->nCT;
            }
            $cte->save();
            session()->flash("flash_sucesso", "Estado alterado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Aldo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function xmlTemp($id)
    {
        $item = Cte::findOrFail($id);
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

        $cte_service = new CTeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_CTe_400",
            "versao" => '4.00',
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ], $config);

        $cte = $cte_service->gerarCTe($item);
        if (!isset($cte['erros_xml'])) {
            $xml = $cte['xml'];
            return response($xml)
                ->header('Content-Type', 'application/xml');
        } else {
            foreach ($cte['erros_xml'] as $err) {
                echo $err;
            }
        }
    }

    public function dacteTemp($id)
    {
        $item = Cte::findOrFail($id);
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $cte_service = new CTeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_CTe_400",
            "versao" => '4.00',
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ], $config);
        $cte = $cte_service->gerarCTe($item);
        if (!isset($cte['erros_xml'])) {
            $xml = $cte['xml'];
            $dacte = new Dacte($xml);
            $dacte->debugMode(true);
            $dacte->setDefaultFont('times');
            $dacte->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
            // $dacte->monta();
            $dacte->printParameters('P', 'A4');
            $dacte->setDefaultDecimalPlaces(2);
            $pdf = $dacte->render();
            return response($pdf)
                ->header('Content-Type', 'application/pdf');
        } else {
            foreach ($cte['erros_xml'] as $err) {
                echo $err;
            }
        }
    }

    public function imprimir($id)
    {
        $item = Cte::findOrFail($id);
        if (valida_objeto($item)) {
            if (file_exists(public_path('xml_cte/') . $item->chave . '.xml')) {
                $xml = file_get_contents(public_path('xml_cte/') . $item->chave . '.xml');
                try {
                    $config = ConfigNota::where('empresa_id', request()->empresa_id)
                        ->first();
                    if ($config->logo) {
                        $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('logos/') . $config->logo));
                    } else {
                        $logo = null;
                    }
                    $dacte = new Dacte($xml);
                    $dacte->debugMode(true);
                    $dacte->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
                    // $dacte->monta();
                    $pdf = $dacte->render($logo);
                    header('Content-Type: application/pdf');
                    header("Content-Disposition: ; filename=DACTE $item->cte_numero");
                    return response($pdf)
                        ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }
            } else {
                echo "Arquivo não encontrado!";
            }
        } else {
            return redirect('/403');
        }
    }

    public function imprimirCCe($id)
    {
        $cte = Cte::findOrFail($id);
        if (!__valida_objeto($cte)) {
            abort(403);
        }
        if (file_exists(public_path('xml_cte_correcao/') . $cte->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_cte_correcao/') . $cte->chave . '.xml');
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
                ->first();
            if ($config->logo) {
                $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('logos/') . $config->logo));
            } else {
                $logo = null;
            }
            $dadosEmitente = $this->getEmitente();
            try {
                $daevento = new Daevento($xml, $dadosEmitente);
                $daevento->debugMode(true);
                $pdf = $daevento->render($logo);
                header('Content-Type: application/pdf');
                return response($pdf)
                    ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            echo "Arquivo não encontrado!";
        }
    }

    public function imprimirCancela($id)
    {
        $cte = Cte::findOrFail($id);
        if (!__valida_objeto($cte)) {
            abort(403);
        }
        if (file_exists(public_path('xml_cte_cancelada/') . $cte->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_cte_cancelada/') . $cte->chave . '.xml');
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
                ->first();
            if ($config->logo) {
                $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('logos/') . $config->logo));
            } else {
                $logo = null;
            }
            $dadosEmitente = $this->getEmitente();
            try {
                $daevento = new Daevento($xml, $dadosEmitente);
                $daevento->debugMode(true);
                $pdf = $daevento->render($logo);
                header('Content-Type: application/pdf');
                return response($pdf)
                    ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            echo "Arquivo não encontrado!";
        }
    }

    private function getEmitente()
    {
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        return [
            'razao' => $config->razao_social,
            'logradouro' => $config->logradouro,
            'numero' => $config->numero,
            'complemento' => '',
            'bairro' => $config->bairro,
            'CEP' => $config->cep,
            'municipio' => $config->municipio,
            'UF' => $config->UF,
            'telefone' => $config->telefone,
            'email' => ''
        ];
    }

    public function enviarXml(Request $request)
    {
        $email = $request->email;
        $id = $request->cte_id;
        $cte = Cte::where('id', $id)
            ->first();
        if (!file_exists(public_path('xml_cte/') . $cte->chave . '.xml')) {
            session()->flash('flash_erro', 'Arquivo não encontrado');
            return redirect()->back();
        }
        if (valida_objeto($cte)) {
            $this->criarPdfParaEnvio($cte);
            $value = session('user_logged');
            Mail::send('mail.xml_send_cte', ['emissao' => $cte->data_registro, 'cte' => $cte->cte_numero, 'usuario' => $value['nome']], function ($m) use ($cte, $email) {
                $nomeEmpresa = env('SMS_NOME_EMPRESA');
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $emailEnvio = env('MAIL_USERNAME');
                $m->from($emailEnvio, $nomeEmpresa);
                $m->subject('Envio de XML CTe ' . $cte->cte_numero);
                $m->attach(public_path('xml_cte/') . $cte->path_xml);
                $m->attach(public_path('pdf/') . 'CTe.pdf');
                $m->to($email);
            });
            return "ok";
        } else {
            return redirect('/403');
        }
    }

    private function criarPdfParaEnvio($cte)
    {
        $xml = file_get_contents(public_path('xml_cte/') . $cte->chave . '.xml');
        // $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents( 'imgs/logo.jpg'));
        // $docxml = FilesFolders::readFile($xml);
        try {
            $dacte = new Dacte($xml);
            // $dacte->debugMode(true);
            $dacte->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
            // $dacte->monta();
            $pdf = $dacte->render();
            header('Content-Type: application/pdf');
            file_put_contents(public_path('pdf/') . 'CTe.pdf', $pdf);
        } catch (InvalidArgumentException $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    }

    public function baixarXml($id)
    {
        $venda = Cte::findOrFail($id);
        if (!__valida_objeto($venda)) {
            abort(403);
        }
        if ($venda) {
            // $public = env('SERVIDOR_WEB') ? 'public/' : '';
            if (file_exists(public_path('xml_cte/') . $venda->chave . '.xml')) {
                return response()->download(public_path('xml_cte/') . $venda->chave . '.xml');
            } else {
                echo "Arquivo XML não encontrado!!";
            }
        } else {
            return redirect('/403');
        }
    }

    public function importarXml(Request $request)
    {

        if ($request->hasFile('xml')) {
            $arquivo = $request->hasFile('xml');
            $xml = simplexml_load_file($request->xml);
            $cidade = Cidade::getCidadeCod($xml->NFe->infNFe->emit->enderEmit->cMun);
            $dadosEmitente = [
                'cpf' => $xml->NFe->infNFe->emit->CPF,
                'cnpj' => $xml->NFe->infNFe->emit->CNPJ,
                'razaoSocial' => $xml->NFe->infNFe->emit->xNome,
                'nomeFantasia' => $xml->NFe->infNFe->emit->xFant,
                'logradouro' => $xml->NFe->infNFe->emit->enderEmit->xLgr,
                'numero' => $xml->NFe->infNFe->emit->enderEmit->nro,
                'bairro' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
                'cep' => $xml->NFe->infNFe->emit->enderEmit->CEP,
                'fone' => $xml->NFe->infNFe->emit->enderEmit->fone,
                'ie' => $xml->NFe->infNFe->emit->IE,
                'cidade_id' => $cidade->id,
                'empresa_id' => $request->empresa_id
            ];
            $emitente = $this->verificaClienteCadastrado($dadosEmitente);
            $cidade = Cidade::getCidadeCod($xml->NFe->infNFe->dest->enderDest->cMun);
            $dadosDestinatario = [
                'cpf' => $xml->NFe->infNFe->dest->CPF,
                'cnpj' => $xml->NFe->infNFe->dest->CNPJ,
                'razaoSocial' => $xml->NFe->infNFe->dest->xNome,
                'nomeFantasia' => $xml->NFe->infNFe->dest->xFant,
                'logradouro' => $xml->NFe->infNFe->dest->enderDest->xLgr,
                'numero' => $xml->NFe->infNFe->dest->enderDest->nro,
                'bairro' => $xml->NFe->infNFe->dest->enderDest->xBairro,
                'cep' => $xml->NFe->infNFe->dest->enderDest->CEP,
                'fone' => $xml->NFe->infNFe->dest->enderDest->fone,
                'ie' => $xml->NFe->infNFe->dest->IE,
                'cidade_id' => $cidade->id,
                'empresa_id' => $request->empresa_id
            ];
            $destinatario = $this->verificaClienteCadastrado($dadosDestinatario);
            $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
            $somaQuantidade = 0;
            foreach ($xml->NFe->infNFe->det as $item) {
                $somaQuantidade += $item->prod->qCom;
            }

            $unidade = $xml->NFe->infNFe->det[0]->prod->uCom;
            if ($unidade == 'M2') {
                $unidade = '04';
            } else if ($unidade == 'M3') {
                $unidade = '00';
            } else if ($unidade == 'KG') {
                $unidade = '01';
            } else if ($unidade == 'UNID') {
                $unidade = '03';
            } else if ($unidade == 'TON') {
                $unidade = '02';
            }
            $dadosDaNFe = [
                'remetente' => $emitente->id,
                'destinatario' => $destinatario->id,
                'chave' => $chave,
                'produto_predominante' => $xml->NFe->infNFe->det[0]->prod->xProd,
                'unidade' => $unidade,
                'valor_carga' => $xml->NFe->infNFe->total->ICMSTot->vProd,
                'munipio_envio' => $emitente->cidade->id . " - " . $emitente->cidade->nome . "(" . $emitente->cidade->uf . ")",
                'munipio_final' => $destinatario->cidade->id . " - " . $destinatario->cidade->nome . "(" . $destinatario->cidade->uf . ")",
                'componente' => 'Transporte',
                'valor_frete' => $xml->NFe->infNFe->total->ICMSTot->vFrete,
                'quantidade' => number_format($somaQuantidade, 4),
                'data_entrega' => date('d/m/Y')
            ];
            // dd($dadosDaNFe['remetente']);
            // echo "<pre>";
            // print_r($dadosDaNFe);
            // echo "</pre>";
            $config = ConfigNota::where('empresa_id', $request->empresa_id)
                ->first();
            $lastCte = $config->ultimo_numero_cte++;
            $unidadesMedida = Cte::unidadesMedida();
            $tiposMedida = Cte::tiposMedida();
            $tiposTomador = Cte::tiposTomador();
            $naturezas = NaturezaOperacao::where('empresa_id', $request->empresa_id)
                ->get();
            $modals = Cte::modals();
            $veiculos = Veiculo::where('empresa_id', $request->empresa_id)
                ->get();
            $config = ConfigNota::where('empresa_id', $request->empresa_id)
                ->first();
            $clienteCadastrado = Cliente::where('empresa_id', $request->empresa_id)
                ->first();
            $clientes = Cliente::where('empresa_id', $request->empresa_id)
                ->where('inativo', false)
                ->orderBy('razao_social')->get();
            foreach ($clientes as $c) {
                $c->cidade;
            }
            $cidades = Cidade::all();
            $tributacao = Tributacao::where('empresa_id', $request->empresa_id)
                ->first();
            return view("cte.xml_create", compact(
                'naturezas',
                'unidadesMedida',
                'tiposMedida',
                'tiposTomador',
                'modals',
                'tributacao',
                'veiculos',
                'cidades',
                'config',
                'lastCte',
                'clientes',
                'dadosDaNFe',
                'emitente',
                'destinatario',
            ));
        }
    }

    private function verificaClienteCadastrado($cliente)
    {
        if ($cliente['cnpj'] != '') {
            $cli = Cliente::where('empresa_id', request()->empresa_id)
                ->where('cpf_cnpj', $this->formataCnpj($cliente['cnpj']))->first();
        } else {
            $cli = Cliente::where('empresa_id', request()->empresa_id)
                ->where('cpf_cnpj', $cliente['cpf'])->first();
        }
        if ($cli == null) {
            $result = Cliente::create(
                [
                    'razao_social' => $cliente['razaoSocial'],
                    'nome_fantasia' => $cliente['nomeFantasia'] != '' ? $cliente['nomeFantasia'] : $cliente['razaoSocial'],
                    'bairro' => $cliente['bairro'],
                    'numero' => $cliente['numero'],
                    'rua' => $cliente['logradouro'],
                    'cpf_cnpj' => $cliente['cnpj'] ? $this->formataCnpj($cliente['cnpj']) : $cliente['cpf'],
                    'telefone' => $cliente['razaoSocial'],
                    'celular' => '',
                    'email' => 'teste@teste.com',
                    'cep' => $cliente['cep'],
                    'ie_rg' => $cliente['ie'],
                    'consumidor_final' => 0,
                    'limite_venda' => 0,
                    'cidade_id' => $cliente['cidade_id'],
                    'contribuinte' => 1,
                    'rua_cobranca' => '',
                    'numero_cobranca' => '',
                    'bairro_cobranca' => '',
                    'cep_cobranca' => '',
                    'cidade_cobranca_id' => NULL,
                    'empresa_id' => request()->empresa_id
                ]
            );
            $cliente = Cliente::find($result->id);
            return $cliente;
        }
        return $cli;
    }

    private function formataCnpj($cnpj)
    {
        $temp = substr($cnpj, 0, 2);
        $temp .= "." . substr($cnpj, 2, 3);
        $temp .= "." . substr($cnpj, 5, 3);
        $temp .= "/" . substr($cnpj, 8, 4);
        $temp .= "-" . substr($cnpj, 12, 2);
        return $temp;
    }
}
