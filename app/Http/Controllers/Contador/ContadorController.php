<?php

namespace App\Http\Controllers\Contador;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ConfigNota;
use App\Models\Contador;
use App\Models\Empresa;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;

class ContadorController extends Controller
{
    public function __construct()
    {
        if (!is_dir(public_path('zips'))) {
            mkdir(public_path('zips'), 0777, true);
        }
    }
    public function index()
    {
        return view('painel_contador.index');
    }

    public function setEmpresa(Request $request)
    {
        session()->forget('empresa_selecionada');
        $item = Empresa::findOrFail($request->empresa);
        $emp = [
            'empresa_id' => $item->id,
            'nome' => $item->razao_social,
            'documento' => $item->cpf_cnpj,
        ];
        session(['empresa_selecionada' => $emp]);
        session()->flash("flash_sucesso", "Empresa selecionada");
        return redirect()->back();
    }

    public function empresas()
    {
        $user_contador = session('user_contador');
        $data = [];
        foreach ($user_contador as $u) {
            $empresa = Empresa::findOrFail($u['empresa_id']);
            $data[] = $empresa;
        }
        $empresaSelecionada = session('empresa_selecionada') ? session('empresa_selecionada')['empresa_id'] : null;
        return view('painel_contador.empresas', compact('data', 'empresaSelecionada'));
    }

    public function empresaDetalhe($id)
    {
        $user = session('user_logged');
        $empresa = Empresa::findOrFail($id);
        if ($empresa->contador_id != $user['contador_id']) {
            abort(403);
        }
        $hoje = date('Y-m-d');
        $planoExpirado = false;
        if ($empresa->planoEmpresa) {
            $exp = $empresa->planoEmpresa->expiracao;
            if (strtotime($hoje) > strtotime($exp)) {
                $planoExpirado = true;
            }
        }
        return view('painel_contador.detalhes_empresa', compact('empresa', 'planoExpirado'));
    }

    public function clientes(Request $request)
    {
        $emp = session('empresa_selecionada');
        if ($emp == null) {
            session()->flash("flash_erro", "Selecione a empresa no menu superior!");
            return redirect()->route('contador.index');
        }
        $data = Cliente::where('empresa_id', $emp['empresa_id'])
            ->when(!empty($request->razao_social), function ($q) use ($request) {
                return $q->where('razao_social', 'LIKE', "%$request->razao_social%");
            })
            ->orderBy('razao_social', 'asc')
            ->paginate(40);
        return view('painel_contador.clientes', compact('data'))
            ->with('razao_social', $request->razao_social);
    }

    public function fornecedores(Request $request)
    {
        $emp = session('empresa_selecionada');
        if ($emp == null) {
            session()->flash("flash_erro", "Selecione a empresa no menu superior!");
            return redirect()->route('contador.index');
        }
        $data = Fornecedor::where('empresa_id', $emp['empresa_id'])
            ->when(!empty($request->razao_social), function ($q) use ($request) {
                return $q->where('razao_social', 'LIKE', "%$request->razao_social%");
            })
            ->orderBy('razao_social', 'asc')
            ->paginate(40);
        return view('painel_contador.fornecedores', compact('data'))
            ->with('razao_social', $request->razao_social);
    }

    public function produtos(Request $request)
    {
        $emp = session('empresa_selecionada');
        if ($emp == null) {
            session()->flash("flash_erro", "Selecione a empresa no menu superior!");
            return redirect()->route('contador.index');
        }
        $data = Produto::where('empresa_id', $emp['empresa_id'])
            ->when(!empty($request->nome), function ($q) use ($request) {
                return $q->where('nome', 'LIKE', "%$request->nome%");
            })
            ->when(!empty($request->cfop), function ($q) use ($request) {
                return $q->where('CFOP_saida_estadual', 'LIKE', "%$request->cfop%")
                    ->orWhere('CFOP_saida_inter_estadual', 'LIKE', "%$request->cfop%");
            })
            ->orderBy('nome', 'asc')
            ->paginate(40);
        return view('painel_contador.produtos')
            ->with('data', $data)
            ->with('nome', $request->nome)
            ->with('cfop', $request->cfop);
    }

    public function vendas(Request $request)
    {
        $data_inicio = $request->start_date;
        $data_fim = $request->end_date;
        $estado_emissao = $request->estado_emissao;
        $emp = session('empresa_selecionada');
        if ($emp == null) {
            session()->flash("flash_erro", "Selecione a empresa no menu superior!");
            return redirect()->route('contador.index');
        }
        $data = Venda::where('vendas.empresa_id', $emp['empresa_id'])
            ->select('vendas.*')
            ->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
            ->when(!empty($request->cliente), function ($q) use ($request) {
                return $q->where('clientes.razao_social', 'LIKE', "%$request->razao_social%");
            })
            ->when(!empty($data_inicio), function ($query) use ($data_inicio) {
                return $query->whereDate('vendas.created_at', '>=', $data_inicio);
            })
            ->when(!empty($data_fim), function ($query) use ($data_fim) {
                return $query->whereDate('vendas.created_at', '<=', $data_fim);
            })
            ->when($estado_emissao != '' && $estado_emissao != 'TODOS', function ($query) use ($estado_emissao) {
                return $query->where('vendas.estado_emissao', $estado_emissao);
            })
            ->orderBy('vendas.created_at', 'desc')
            ->paginate(40);

        return view('painel_contador.vendas', compact('data'))
            ->with('cliente', $request->cliente)
            ->with('data_inicio', $data_inicio)
            ->with('data_fim', $data_fim)
            ->with('estado_emissao', $estado_emissao);
    }

    public function downloadXmlNfe($id)
    {
        $venda = Venda::findOrFail($id);
        if (file_exists(public_path('xml_nfe/') . $venda->chave . '.xml')) {
            return response()->download(public_path('xml_nfe/') . $venda->chave . '.xml');
        } else {
            echo "Arquivo XML não encontrado!!";
        }
    }

    public function pdv(Request $request)
    {
        $data_inicio = $request->start_date;
        $data_fim = $request->end_date;
        $estado_emissao = $request->estado_emissao;
        $emp = session('empresa_selecionada');
        if ($emp == null) {
            session()->flash("flash_erro", "Selecione a empresa no menu superior!");
            return redirect()->route('contador.index');
        }
        $data = VendaCaixa::where('venda_caixas.empresa_id', $emp['empresa_id'])
            ->select('venda_caixas.*')
            ->when(!empty($request->cliente), function ($q) use ($request) {
                return $q->where('clientes.razao_social', 'LIKE', "%$request->razao_social%")
                    ->join('clientes', 'clientes.id', '=', 'venda_caixas.cliente_id');
            })
            ->when(!empty($data_inicio), function ($query) use ($data_inicio) {
                return $query->whereDate('venda_caixas.created_at', '>=', $data_inicio);
            })
            ->when(!empty($data_fim), function ($query) use ($data_fim) {
                return $query->whereDate('venda_caixas.created_at', '<=', $data_fim);
            })
            ->when($estado_emissao != '' && $estado_emissao != 'TODOS', function ($query) use ($estado_emissao) {
                return $query->where('venda_caixas.estado_emissao', $estado_emissao);
            })
            ->orderBy('venda_caixas.created_at', 'desc')
            ->paginate(40);
        return view('painel_contador.pdv', compact('data'))
            ->with('cliente', $request->cliente)
            ->with('data_inicio', $data_inicio)
            ->with('data_fim', $data_fim)
            ->with('estado_emissao', $estado_emissao);
    }

    public function downloadXmlPdv($id)
    {
        $venda = VendaCaixa::findOrFail($id);
        if (file_exists(public_path('xml_nfce/') . $venda->chave . '.xml')) {
            return response()->download(public_path('xml_nfce/') . $venda->chave . '.xml');
        } else {
            echo "Arquivo XML não encontrado!!";
        }
    }

    public function downloadCertificado($id)
    {
        $config = ConfigNota::where('empresa_id', $id)
            ->first();
        if ($config == null) {
            session()->flash("flash_erro", "Nenhum certificado!");
            return redirect()->back();
        }
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $files = array_diff(scandir(public_path('certificados')), array('.', '..'));
        $certificados = [];
        foreach ($files as $file) {
            $name_file = explode(".", $file);
            if ($name_file[0] == $cnpj) {
                array_push($certificados, $file);
            }
        }
        if (sizeof($certificados) > 1) {
            return view('empresas.certificados', compact('certificados'));
        }
        try {
            if (file_exists(public_path('certificados/') . $cnpj . '.p12')) {
                return response()->download(public_path('certificados/') . $cnpj . '.p12');
            } elseif (file_exists(public_path('certificados/') . $cnpj . '.pfx')) {
                return response()->download(public_path('certificados/') . $cnpj . '.pfx');
            } elseif (file_exists(public_path('certificados/') . $cnpj . '.bin')) {
                return response()->download(public_path('certificados/') . $cnpj . '.bin');
            } else {
                echo "Nenhum arquivo encontrado!";
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function downloadFiltroXmlNfe(Request $request)
    {
        $data_inicio = $request->data_inicio;
        $data_fim = $request->data_fim;
        $estado = $request->estado_emissao;
        $emp = session('empresa_selecionada');
        $empresa = Empresa::findOrFail($emp['empresa_id']);
        $config = $empresa->configNota;
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $data = Venda::where('vendas.empresa_id', $emp['empresa_id'])
            ->select('vendas.*')
            ->join('clientes', 'clientes.id', '=', 'vendas.cliente_id')
            ->when(!empty($request->cliente), function ($q) use ($request) {
                return $q->where('clientes.razao_social', 'LIKE', "%$request->razao_social%");
            })
            ->when(!empty($data_inicio), function ($query) use ($data_inicio) {
                return $query->whereDate('vendas.created_at', '>=', $data_inicio);
            })
            ->when(!empty($data_fim), function ($query) use ($data_fim) {
                return $query->whereDate('vendas.created_at', '<=', $data_fim);
            })
            ->when($estado != '' && $estado != 'todos', function ($query) use ($estado) {
                return $query->where('vendas.estado_emissao', $estado);
            })
            ->orderBy('vendas.created_at', 'desc')
            ->get();
        $zip_file = public_path('zips') . '/xml-' . $cnpj . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($data as $item) {
            if (file_exists(public_path('xml_nfe/') . $item->chave . '.xml')) {
                $zip->addFile(public_path('xml_nfe/') . $item->chave . '.xml', $item->path_xml);
            }
        }
        $zip->close();
        return response()->download(public_path('zips') . '/xml-' . $cnpj . '.zip');
    }

    public function downloadFiltroXmlNfce(Request $request)
    {
        $data_inicio = $request->start_date;
        $data_fim = $request->end_date;
        $estado = $request->estado_emissao;
        $emp = session('empresa_selecionada');
        $empresa = Empresa::findOrFail($emp['empresa_id']);
        $config = $empresa->configNota;
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $data = VendaCaixa::where('venda_caixas.empresa_id', $emp['empresa_id'])
            ->select('venda_caixas.*')
            ->when(!empty($request->cliente), function ($q) use ($request) {
                return $q->where('clientes.razao_social', 'LIKE', "%$request->razao_social%")
                    ->join('clientes', 'clientes.id', '=', 'venda_caixas.cliente_id');
            })
            ->when(!empty($data_inicio), function ($query) use ($data_inicio) {
                return $query->whereDate('venda_caixas.created_at', '>=', $data_inicio);
            })
            ->when(!empty($data_fim), function ($query) use ($data_fim) {
                return $query->whereDate('venda_caixas.created_at', '<=', $data_fim);
            })
            ->when($estado != '' && $estado != 'todos', function ($query) use ($estado) {
                return $query->where('venda_caixas.estado_emissao', $estado);
            })
            ->orderBy('venda_caixas.created_at', 'desc')
            ->get();
        $zip_file = public_path('zips') . '/xml-nfce-' . $cnpj . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($data as $item) {
            if (file_exists(public_path('xml_nfce/') . $item->chave . '.xml')) {
                $zip->addFile(public_path('xml_nfce/') . $item->chave . '.xml', $item->path_xml);
            }
        }
        $zip->close();
        return response()->download(public_path('zips') . '/xml-nfce-' . $cnpj . '.zip');
    }
}
