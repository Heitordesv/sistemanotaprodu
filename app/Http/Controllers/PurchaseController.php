<?php

namespace App\Http\Controllers;

use App\Helpers\StockMove;
use App\Models\Categoria;
use App\Models\CategoriaConta;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\Compra;
use App\Models\NaturezaOperacao;
use App\Models\ConfigNota;
use App\Models\ContaPagar;
use App\Models\Fornecedor;
use App\Models\ItemCompra;
use App\Models\Marca;
use App\Models\Produto;
use App\Models\Transportadora;
use App\Models\Tributacao;
use App\Models\TelaPedido;
use App\Models\DivisaoGrade;
use Illuminate\Http\Request;
use App\Services\NFeEntradaService;
use App\Utils\Util;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\Daevento;
use Illuminate\Support\Facades\DB;


class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        if (!is_dir(public_path('xml_entrada_emitida'))) {
            mkdir(public_path('xml_entrada_emitida'), 0777, true);
        }
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $fornecedor_id = $request->get('fornecedor_id');
        $filial_id = $request->filial_id;
        $local_padrao = __get_local_padrao();
        if (!$filial_id && $local_padrao) {
            $filial_id = $local_padrao;
        }
        $data = Compra::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($fornecedor_id), function ($query) use ($fornecedor_id) {
            return $query->where('fornecedor_id', $fornecedor_id);
        })
        ->when($filial_id != 'todos', function ($query) use ($filial_id) {
            $filial_id = $filial_id == -1 ? null : $filial_id;
            return $query->where('filial_id', $filial_id);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('compras.index', compact('data', 'filial_id'));
    }

    public function destroy($id)
    {
        $item = Compra::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->itens()->delete();
            $item->delete();
            session()->flash("flash_sucesso", "Compra removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('compras.index');
    }

    public function nfeEntrada($id)
    {
        $item = Compra::findOrFail($id);
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
        ->get();
        return view('compras.nfe', compact('item', 'naturezas'));
    }

    public function setNatureza(Request $request, $id)
    {
        $item = Compra::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Natureza de operação definida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function xmlTemp($id)
    {
        $item = Compra::findOrFail($id);
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        if ($config == null) {
            return response()->json("Configure o emitente", 401);
        }
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFeEntradaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        $nfe = $nfe_service->gerarNFe($item);
        if (!isset($nfe['erros_xml'])) {
            return response($nfe['xml'])
            ->header('Content-Type', 'application/xml');
        } else {
            print_r($nfe['erros_xml']);
        }
    }

    public function danfeTemp($id)
    {
        $item = Compra::findOrFail($id);
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        if ($config == null) {
            return response()->json("Configure o emitente", 401);
        }
        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $nfe_service = new NFeEntradaService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        $nfe = $nfe_service->gerarNFe($item);
        if (!isset($nfe['erros_xml'])) {
            try {
                $xml = $nfe['xml'];
                $logo = null;
                $danfe = new Danfe($xml);
                $danfe->setVUnComCasasDec($config->casas_decimais);
                $pdf = $danfe->render($logo);
                header("Content-Disposition: ; filename=DANFE TEMPORÁRIA");
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            print_r($nfe['erros_xml']);
        }
    }

    public function danfe($id)
    {
        $item = Compra::findOrFail($id);
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        if ($config == null) {
            return response()->json("Configure o emitente", 401);
        }
        if (file_exists(public_path('xml_entrada_emitida/') . $item->chave . '.xml')) {
            $xml = file_get_contents(public_path('xml_entrada_emitida/') . $item->chave . '.xml');
            if ($config->logo) {
                $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'logos/' . $config->logo));
            } else {
                $logo = null;
            }
            try {
                $danfe = new Danfe($xml);
                $danfe->setVUnComCasasDec($config->casas_decimais);
                $pdf = $danfe->render($logo);
                header("Content-Disposition: ; filename=DANFE $item->numero_emissao");
                // header('Content-type: text/html; charset=UTF-8');
                return response($pdf)
                ->header('Content-Type', 'application/pdf');
            } catch (InvalidArgumentException $e) {
                echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
            }
        } else {
            echo "Arquivo XML não encontrado!!";
        }
    }

    public function imprimirCorrecao($id)
    {
        $item = Compra::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        if ($item->sequencia_cce > 0) {
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
            if (file_exists(public_path('xml_nfe_entrada_correcao/') . $item->chave . '.xml')) {
                $xml = file_get_contents(public_path('xml_nfe_entrada_correcao/') . $item->chave . '.xml');
                if ($config->logo) {
                    $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'logos/' . $config->logo));
                } else {
                    $logo = null;
                }
                $dadosEmitente = $this->getEmitente();
                try {
                    $daevento = new Daevento($xml, $dadosEmitente);
                    $daevento->debugMode(true);
                    $pdf = $daevento->render($logo);
                    header("Content-Disposition: ; filename=CCe $item->numero_emissao");
                    return response($pdf)
                    ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }
            } else {
                echo "Arquivo XML não encontrado!!";
            }
        } else {
            echo "<center><h1>Este documento não possui evento de correção!<h1></center>";
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

    public function imprimirCancelamento($id)
    {
        $item = Compra::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        if ($item->estado == 'cancelado') {
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
            if (file_exists(public_path('xml_nfe_entrada_cancelada/') . $item->chave . '.xml')) {
                $xml = file_get_contents(public_path('xml_nfe_entrada_cancelada/') . $item->chave . '.xml');
                if ($config->logo) {
                    $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'logos/' . $config->logo));
                } else {
                    $logo = null;
                }
                $dadosEmitente = $this->getEmitente();
                try {
                    $daevento = new Daevento($xml, $dadosEmitente);
                    $daevento->debugMode(true);
                    $pdf = $daevento->render($logo);
                    header("Content-Disposition: ; filename=Cancelamento $item->numero_nfe");
                    return response($pdf)
                    ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }
            } else {
                echo "Arquivo XML não encontrado!!";
            }
        } else {
            echo "<center><h1>Este documento não possui evento de cancelamento!<h1></center>";
        }
    }

    public function edit(Request $request, $id)
    {

        $item = Compra::findOrFail($id);
        $dataValidate = [
            'categorias', 'produtos', 'fornecedors'
        ];
        $util = new Util();
        $validateEntry = $util->validateEntry($dataValidate, $request->empresa_id);
        if ($validateEntry != null) {
            session()->flash("flash_erro", $validateEntry['message']);
            return redirect($validateEntry['route']);
        }
        $fornecedores = Fornecedor::where('empresa_id', $request->empresa_id)->get();
        $transportadoras = Transportadora::where('empresa_id', $request->empresa_id)->get();
        $categorias = Categoria::where('empresa_id', $request->empresa_id)->get();
        $marcas = Marca::where('empresa_id', $request->empresa_id)->get();
        $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', request()->empresa_id)->get();
        $naturezaPadrao = NaturezaOperacao::where('empresa_id', $request->empresa_id)
        ->first();
        $tributacao = Tributacao::where('empresa_id', $request->empresa_id)
        ->first();
        $telasPedido = TelaPedido::where('empresa_id', request()->empresa_id)->get();
        $divisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
        ->where('sub_divisao', false)
        ->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
        ->where('sub_divisao', true)
        ->get();
        return view(
            'compra_manual.edit',
            compact(
                'fornecedores',
                'transportadoras',
                'categorias',
                'marcas',
                'divisoes',
                'subDivisoes',
                'categoriasEcommerce',
                'naturezaPadrao',
                'tributacao',
                'telasPedido',
                'item'
            )
        );
    }
}
