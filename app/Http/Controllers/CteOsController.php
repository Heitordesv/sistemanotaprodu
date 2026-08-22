<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ConfigNota;
use App\Models\CteOs;
use App\Models\NaturezaOperacao;
use App\Models\Veiculo;
use App\Services\CTeOsService;
use App\Services\CTeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use NFePHP\DA\CTe\Dacte;
use NFePHP\DA\CTe\Daevento;

class CteOsController extends Controller
{
    public function index()
    {
        $data = CteOs::where('empresa_id', request()->empresa_id)
        ->paginate(env("PAGINACAO"));;
        return view('cte_os.index', compact('data'));
    }

    public function create()
    {
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        $veiculos = Veiculo::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        return view('cte_os.create', compact('naturezas', 'clientes', 'veiculos', 'cidades'));
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'usuario_id' => get_id_user(),
                'emitente_id' => $request->remetente_id,
                'tomador_id' => $request->destinatario_id
            ]);
            CteOs::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->route('cteOs.index');
    }

    public function edit($id)
    {
        $item = CteOs::findOrFail($id);
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        $veiculos = Veiculo::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        return view('cte_os.edit', compact('item', 'naturezas', 'clientes', 'veiculos', 'cidades'));
    }

    public function update(Request $request, $id)
    {
        $item = CteOs::findOrFail($id);
        try {
            $request->merge([
                'usuario_id' => get_id_user(),
                'emitente_id' => $request->remetente_id,
                'tomador_id' => $request->destinatario_id
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->route('cteOs.index');
    }

    public function detalhes($id)
    {
        $item = CteOs::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        if (valida_objeto($item)) {
            $value = session('user_logged');
        }
        return view("cte_os.detalhes")
        ->with('adm', $value['adm'])
        ->with('item', $item);
    }

    public function destroy($id)
    {
        $item = CteOs::findOrFail($id);
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
        return redirect()->route('cteOs.index');
    }

    public function estadoFiscal($id)
    {
        $item = CteOs::findOrFail($id);
        return view('cte_os.estado_fiscal', compact('item'));
    }

    public function estadoFiscalStore(Request $request)
    {
        $cte = CteOs::findOrFail($request->cte_id);
        try {
            $estado_emissao = $request->estado_emissao;
            $cte->estado_emissao = $estado_emissao;
            if ($request->hasFile('xml')) {
                $xml = simplexml_load_file($request->xml);
                $chave = substr($xml->CTe->infCte->attributes()->Id, 3, 44);
                $file = $request->xml;
                $file->move(public_path('xml_cte_os/'), $chave . '.xml');
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
        $item = CteOs::findOrFail($id);

        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $cte_service = new CTeOsService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_CTe_300",
            "versao" => '3.00',
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
            // print_r($nfe['erros_xml']);
            foreach ($cte['erros_xml'] as $err) {
                echo $err;
            }
        }
    }

    public function imprimirCCe($id)
    {
        $cte = CteOs::findOrFail($id);
        if (!__valida_objeto($cte)) {
            abort(403);
        }
        if (file_exists(public_path('xml_cte_os_correcao/') . $cte->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_cte_os_correcao/') . $cte->chave . '.xml');
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
        $cte = CteOs::findOrFail($id);
        if (!__valida_objeto($cte)) {
            abort(403);
        }
        if (file_exists(public_path('xml_cte_os_cancelada/') . $cte->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_cte_os_cancelada/') . $cte->chave . '.xml');
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

    public function enviarXml(Request $request)
    {
        $email = $request->email;
        $id = $request->cte_id;
        $cte = CteOs::where('id', $id)
        ->first();
        if (!file_exists(public_path('xml_cte_os/') . $cte->chave . '.xml')) {
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
                $m->attach(public_path('xml_cte_os/') . $cte->path_xml);
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
        $xml = file_get_contents(public_path('xml_cte_os/') . $cte->chave . '.xml');
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
        $venda = CteOs::findOrFail($id);
        if (!__valida_objeto($venda)) {
            abort(403);
        }
        if ($venda) {
            // $public = env('SERVIDOR_WEB') ? 'public/' : '';
            if (file_exists(public_path('xml_cte_os/') . $venda->chave . '.xml')) {
                return response()->download(public_path('xml_cte_os/') . $venda->chave . '.xml');
            } else {
                echo "Arquivo XML não encontrado!!";
            }
        } else {
            return redirect('/403');
        }
    }
}
