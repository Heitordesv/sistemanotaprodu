<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\ConfigNota;
use App\Models\Cte;
use App\Models\Devolucao;
use App\Models\Empresa;
use App\Models\EscritorioContabil;
use App\Models\Filial;
use App\Models\ItemVenda;
use App\Models\ItemVendaCaixa;
use App\Models\ManifestaDfe;
use App\Models\Mdfe;
use App\Models\NfeRemessa;
use App\Models\RemessaNfe;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Models\XmlEnviado;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Mail;

class EnviarXmlController extends Controller
{
    protected $empresa_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if (!$value) {
                return redirect("/login");
            }
            return $next($request);
        });
    }

    public function index()
    {
        return view('enviar_xml.index');
    }

// 1. Esta função abre a página limpa (sem resultados)
public function filtroCfop(Request $request)
{
    return view('enviar_xml.filtroCfop', [
        'itens' => null,
        'start_date' => '',
        'end_date' => '',
        'cfop_filtro' => '',
        'somaTotalVendas' => 0
    ]);
}

// 2. Esta função processa a busca (Mantenha o nome que está na sua Rota)
public function filtroCfopGet(Request $request)
{
    $start_date = $request->start_date;
    $end_date = $request->end_date;
    $cfop_filtro = $request->cfop;

    if (!$start_date || !$end_date) {
        session()->flash('flash_erro', 'Informe data inicial e final');
        return redirect()->route('enviarXml.filtroCfop');
    }

    $end_date_carbon = \Carbon\Carbon::parse($end_date)->endOfDay();
    $start_date_carbon = \Carbon\Carbon::parse($start_date)->startOfDay();

    // Query de Itens Venda
    $itensVenda = ItemVenda::selectRaw('cfop, produto_id, sum(quantidade) AS qtd, sum(quantidade*valor) AS total')
        ->join('vendas', 'vendas.id', '=', 'item_vendas.venda_id')
        ->where('vendas.empresa_id', $this->empresa_id)
        ->where('vendas.estado_emissao', 'APROVADO')
        ->whereBetween('item_vendas.created_at', [$start_date_carbon, $end_date_carbon])
        ->when(strlen($cfop_filtro) == 4, function ($q) use ($cfop_filtro) {
            return $q->where('item_vendas.cfop', $cfop_filtro);
        })
        ->groupBy('cfop', 'produto_id')
        ->with('produto')
        ->get();

    // Query de Itens Venda Caixa
    $itensVendaCaixa = ItemVendaCaixa::selectRaw('cfop, item_venda_caixas.produto_id, sum(quantidade) AS qtd, sum(quantidade*valor) AS total')
        ->join('venda_caixas', 'venda_caixas.id', '=', 'item_venda_caixas.venda_caixa_id')
        ->where('venda_caixas.empresa_id', $this->empresa_id)
        ->where('venda_caixas.estado_emissao', 'APROVADO')
        ->whereBetween('item_venda_caixas.created_at', [$start_date_carbon, $end_date_carbon])
        ->when(strlen($cfop_filtro) == 4, function ($q) use ($cfop_filtro) {
            return $q->where('item_venda_caixas.cfop', $cfop_filtro);
        })
        ->groupBy('cfop', 'produto_id')
        ->with('produto')
        ->get();

    $todosItens = $itensVenda->concat($itensVendaCaixa);
    
    // Agrupamento para consolidar produtos iguais no mesmo CFOP
    $itens = $todosItens->groupBy('produto_id')->map(function ($produtos) {
        $primeiro = $produtos->first();
        return (object)[
            'produto_id' => $primeiro->produto_id,
            'nome' => $primeiro->produto->nome ?? 'Produto não encontrado',
            'qtd' => $produtos->sum('qtd'),
            'total' => $produtos->sum('total'),
            'cfop' => $primeiro->cfop,
            'unidade' => $primeiro->produto->unidade_venda ?? 'UN'
        ];
    });

    $somaTotalVendas = $this->somaTotalVendas($start_date_carbon, $end_date_carbon);

    return view('enviar_xml.filtroCfop', compact('itens', 'somaTotalVendas', 'cfop_filtro', 'start_date', 'end_date'));
}
    private function getCfops($start_date, $end_date)
    {
        $cfops = [];
        $itensVenda = ItemVenda::selectRaw('distinct(item_vendas.cfop) as cfop')
        ->join('vendas', 'vendas.id', '=', 'item_vendas.venda_id')
        ->where('vendas.empresa_id', $this->empresa_id)
        ->where('vendas.estado_emissao', '!=', 'cancelado')
        ->whereBetween('item_vendas.created_at', [
            $start_date,
            $end_date,
        ])
        ->get();
        $itensVendaCaixa = ItemVendaCaixa::selectRaw('distinct(item_venda_caixas.cfop) as cfop')
        ->join('venda_caixas', 'venda_caixas.id', '=', 'item_venda_caixas.venda_caixa_id')
        ->where('venda_caixas.empresa_id', $this->empresa_id)
        ->where('venda_caixas.estado_emissao', '!=', 'cancelado')
        ->whereBetween('item_venda_caixas.created_at', [
            $start_date,
            $end_date,
        ])
        ->groupBy('item_venda_caixas.produto_id')
        ->get();
        foreach ($itensVenda as $i) {
            if ($i->cfop != "0") {
                if (!in_array($i->cfop, $cfops)) {
                    array_push($cfops, $i->cfop);
                }
            }
        }
        foreach ($itensVendaCaixa as $i) {
            if ($i->cfop != "0") {
                if (!in_array($i->cfop, $cfops)) {
                    array_push($cfops, $i->cfop);
                }
            }
        }
        return $cfops;
    }

    private function somaTotalVendas($start_date, $end_date)
    {
        $vendas = Venda::selectRaw('sum(vendas.valor_total) as soma')
        ->whereBetween('created_at', [
            $start_date,
            $end_date
        ])
        ->where('empresa_id', $this->empresa_id)
        ->where('vendas.estado_emissao', '!=', 'cancelado')
        ->first();
        $vendasCaixa = VendaCaixa::selectRaw('sum(venda_caixas.valor_total) as soma')
        ->whereBetween('created_at', [
            $start_date,
            $end_date
        ])
        ->where('venda_caixas.empresa_id', $this->empresa_id)
        ->where('venda_caixas.estado_emissao', '!=', 'cancelado')
        ->first();
        return $vendas->soma + $vendasCaixa->soma;
    }

    private function uneObjetos($vendas, $vendasCaixa)
    {
        $temp = [];
        foreach ($vendas as $v) {
            array_push($temp, $v);
        }
        foreach ($vendasCaixa as $v) {
            for ($i = 0; $i < sizeof($temp); $i++) {
                if ($v->produto_id == $temp[$i]->produto_id) {
                    $temp[$i]->qtd += $v->qtd;
                    $temp[$i]->total += $v->total;
                }
            }
        }
        return $temp;
    }


    public function filtro(Request $request)
    {
        $filial_id = $request->filial_id;
        $files = glob(public_path('zips') . "/*");
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $xml = Venda::whereBetween('created_at', [
            $request->start_date,
            $request->end_date, true
        ])
        ->where('empresa_id', $request->empresa_id)
        ->when($filial_id, function ($query) use ($filial_id) {
            $filial_id = $filial_id == -1 ? null : $filial_id;
            return $query->where('filial_id', $filial_id);
        });
        $estado_emissao = $request->estado_emissao;
        if ($estado_emissao == 1) {
            $xml->where('estado_emissao', 'aprovado');
        } else {
            $xml->where('estado_emissao', 'cancelado');
        }
        $xml = $xml->get();


        $xmlRemessa = RemessaNfe::whereBetween('data_emissao', [
            $request->start_date,
            $request->end_date, true
        ])
        ->where('empresa_id', $this->empresa_id)
        ->when($filial_id, function ($query) use ($filial_id) {
            $filial_id = $filial_id == -1 ? null : $filial_id;
            return $query->where('filial_id', $filial_id);
        });

        $estado = $request->estado;
        if ($estado_emissao == 1) {
            $xmlRemessa->where('estado_emissao', 'aprovado');
        } else {
            $xmlRemessa->where('estado_emissao', 'cancelado');
        }
        $xmlRemessa = $xmlRemessa->get();

        $temp = [];
        foreach ($xml as $x) {
            array_push($temp, $x);
        }
        foreach ($xmlRemessa as $x) {
            array_push($temp, $x);
        }

        $xml = $temp;

        $public = env('SERVIDOR_WEB') ? 'public/' : '';
        $cnpj = $this->getCnpjEmpresa();
        try {
            if (count($xml) > 0) {
                // $zip_file = 'zips/xml_'.$cnpj.'.zip';
                $zip_file = public_path('zips') . '/xml-' . $cnpj . '.zip';
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                if ($estado_emissao == 1) {
                    foreach ($xml as $x) {
                        if (file_exists($public . 'xml_nfe/' . $x->chave . '.xml'))
                            $zip->addFile($public . 'xml_nfe/' . $x->chave . '.xml', $x->path_xml);
                    }
                } else {
                    foreach ($xml as $x) {
                        if (file_exists($public . 'xml_nfe_cancelada/' . $x->chave . '.xml'))
                            $zip->addFile($public . 'xml_nfe_cancelada/' . $x->chave . '.xml', $x->path_xml);
                    }
                }
                $zip->close();
            }
        } catch (\Exception $e) {
        }
        try {
            $xmlCte = Cte::whereBetween('created_at', [
                $request->start_date,
                $request->end_date, true
            ])
            ->where('empresa_id', $this->empresa_id)
            ->when($filial_id > 0, function ($query) use ($filial_id) {
                return $query->where('filial_id', $filial_id);
            });
            $estado_emissao = $request->estado_emissao;
            if ($estado_emissao == 1) {
                $xmlCte->where('estado_emissao', 'aprovado');
            } else {
                $xmlCte->where('estado_emissao', 'cancelado');
            }
            $xmlCte = $xmlCte->get();
            if (count($xmlCte) > 0) {
                $zip_file = public_path('zips') . '/xmlcte-' . $cnpj . '.zip';
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                if ($estado_emissao == 1) {
                    foreach ($xmlCte as $x) {
                        if (file_exists($public . 'xml_cte/' . $x->chave . '.xml'))
                            $zip->addFile($public . 'xml_cte/' . $x->chave . '.xml', $x->path_xml);
                    }
                } else {
                    foreach ($xmlCte as $x) {
                        if (file_exists($public . 'xml_cte_cancelada/' . $x->chave . '.xml'))
                            $zip->addFile($public . 'xml_cte_cancelada/' . $x->chave . '.xml', $x->path_xml);
                    }
                }
                $zip->close();
            }
        } catch (\Exception $e) {
        }
        try {
            $xmlNfce = VendaCaixa::whereBetween('created_at', [
                $request->start_date,
                $request->end_date, true
            ])
            ->where('empresa_id', $this->empresa_id)
            ->when($filial_id > 0, function ($query) use ($filial_id) {
                return $query->where('filial_id', $filial_id);
            });
            if ($estado_emissao == 1) {
                $xmlNfce->where('estado_emissao', 'aprovado');
            } else {
                $xmlNfce->where('estado_emissao', 'cancelado');
            }
            $xmlNfce = $xmlNfce->get();
            if (sizeof($xmlNfce) > 0) {
                $zip_file = public_path('zips') . '/xmlnfce-' . $cnpj . '.zip';
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                if ($estado_emissao == 1) {
                    foreach ($xmlNfce as $x) {

                        if (file_exists($public . 'xml_nfce/' . $x->chave . '.xml')) {
                            $zip->addFile($public . 'xml_nfce/' . $x->chave . '.xml', $x->chave . '.xml');
                        }
                    }
                } else {
                    foreach ($xmlNfce as $x) {
                        if (file_exists($public . 'xml_nfce_cancelada/' . $x->chave . '.xml'))
                            $zip->addFile($public . 'xml_nfce_cancelada/' . $x->chave . '.xml', $x->chave . '.xml');
                    }
                }
                $zip->close();
            }
        } catch (\Exception $e) {
        }
        $xmlMdfe = [];
        if (count($xmlMdfe) > 0) {
            try {
                $zip_file = public_path('zips') . '/xmlmdfe-' . $cnpj . '.zip';
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                if ($estado_emissao == 1) {
                    foreach ($xmlMdfe as $x) {
                        if (file_exists($public . 'xml_mdfe/' . $x->chave . '.xml')) {
                            $zip->addFile($public . 'xml_mdfe/' . $x->chave . '.xml', $x->chave . '.xml');
                        }
                    }
                } else {
                    foreach ($xmlMdfe as $x) {
                        if (file_exists($public . 'xml_mdfe_cancelada/' . $x->chave . '.xml')) {
                            $zip->addFile($public . 'xml_mdfe_cancelada/' . $x->chave . '.xml', $x->chave . '.xml');
                        }
                    }
                }
                $zip->close();
            } catch (\Exception $e) {
                // echo $e->getMessage();
            }
        }
        //nfe entrada
        $xmlEntrada = Compra::whereBetween('created_at', [
            $request->start_date,
            $request->end_date, true
        ])
        ->where('empresa_id', $this->empresa_id)
        ->where('numero_emissao', '>', 0)
        ->when($filial_id > 0, function ($query) use ($filial_id) {
            return $query->where('filial_id', $filial_id);
        });
        if ($estado_emissao == 1) {
            $xmlEntrada->where('estado', 'aprovado');
        } else {
            $xmlEntrada->where('estado', 'cancelado');
        }
        $xmlEntrada = $xmlEntrada->get();
        if (count($xmlEntrada) > 0) {
            try {
                $zip_file = public_path('zips') . '/xmlEntrada-' . $cnpj . '.zip';
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                if ($estado_emissao == 1) {
                    foreach ($xmlEntrada as $x) {
                        if (file_exists($public . 'xml_entrada_emitida/' . $x->chave . '.xml')) {
                            $zip->addFile($public . 'xml_entrada_emitida/' . $x->chave . '.xml', $x->chave . '.xml');
                        }
                    }
                } else {
                    foreach ($xmlEntrada as $x) {
                        if (file_exists($public . 'xml_nfe_entrada_cancelada/' . $x->chave . '.xml')) {
                            $zip->addFile($public . 'xml_nfe_entrada_cancelada/' . $x->chave . '.xml', $x->chave . '.xml');
                        }
                    }
                }
                $zip->close();
            } catch (\Exception $e) {
                // echo $e->getMessage();
            }
        }
        $xmlDevolucao = Devolucao::whereBetween('created_at', [
            $request->start_date,
            $request->end_date, true
        ])
        ->where('empresa_id', $this->empresa_id)
        ->when($filial_id > 0, function ($query) use ($filial_id) {
            return $query->where('filial_id', $filial_id);
        });
        // 1- Aprovado, 3 - Cancelado
        if ($estado_emissao == 1) {
            $xmlDevolucao->where('estado_emissao', 1);
        } else {
            $xmlDevolucao->where('estado_emissao', 3);
        }
        $xmlDevolucao = $xmlDevolucao->get();
        if (count($xmlDevolucao) > 0) {
            try {
                $zip_file = public_path('zips') . '/xmlDevolucao-' . $cnpj . '.zip';
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                if ($estado_emissao == 1) {
                    foreach ($xmlDevolucao as $x) {
                        if (file_exists($public . 'xml_devolucao/' . $x->chave_gerada . '.xml')) {
                            $zip->addFile($public . 'xml_devolucao/' . $x->chave_gerada . '.xml', $x->chave_gerada . '.xml');
                        }
                    }
                } else {
                    foreach ($xmlDevolucao as $x) {
                        if (file_exists($public . 'xml_devolucao_cancelada/' . $x->chave_gerada . '.xml')) {
                            $zip->addFile($public . 'xml_devolucao_cancelada/' . $x->chave_gerada . '.xml', $x->chave_gerada . '.xml');
                        }
                    }
                }
                $zip->close();
            } catch (\Exception $e) {
                // echo $e->getMessage();
            }
        }
        //Entrada Dfe e Compra fiscal
        $xmlCompraFiscal = Compra::whereBetween('data_emissao', [
            $request->start_date,
            $request->end_date, true
        ])
        ->where('empresa_id', $this->empresa_id)
        ->when($filial_id > 0, function ($query) use ($filial_id) {
            return $query->where('filial_id', $filial_id);
        })
        ->where('numero_nfe', '>', 0)->get();
        $xmlDfe = ManifestaDfe::whereBetween('data_emissao', [
            $request->start_date,
            $request->end_date, true
        ])
        ->where('empresa_id', $this->empresa_id)
            // ->when($filial_id > 0, function ($query) use ($filial_id) {
            //     return $query->where('filial_id', $filial_id);
            // })
        ->get();
        $xmlFiscalCompra = [];
        if (sizeof($xmlCompraFiscal) > 0 || sizeof($xmlDfe) > 0) {
            try {
                $zip_file = public_path('zips') . '/xmlCompraFiscal-' . $cnpj . '.zip';
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                foreach ($xmlCompraFiscal as $x) {
                    if (file_exists($public . 'xml_entrada/' . $x->xml_path)) {
                        $zip->addFile($public . 'xml_entrada/' . $x->xml_path, $x->xml_path);
                    }
                }
                foreach ($xmlDfe as $x) {
                    if (file_exists($public . 'xml_dfe/' . $x->chave . '.xml')) {
                        $zip->addFile($public . 'xml_dfe/' . $x->chave . '.xml', $x->chave . '.xml');
                    }
                }
                $zip->close();
            } catch (\Exception $e) {
                // echo $e->getMessage();
            }
            foreach ($xmlCompraFiscal as $x) {
                $data = [
                    'id' => $x->id,
                    'chave' => $x->chave,
                    'data_emissao' => $x->data_emissao,
                ];
                array_push($xmlFiscalCompra, $data);
            }
            foreach ($xmlDfe as $x) {
                $data = [
                    'id' => $x->id,
                    'chave' => $x->chave,
                    'data_emissao' => $x->data_emissao,
                ];
                array_push($xmlFiscalCompra, $data);
            }
        }
        //Fim entrada Dfe e Compra fiscal
        $start_date = str_replace("/", "-", $request->start_date);
        $end_date = str_replace("/", "-", $request->end_date);
        return view('enviar_xml.index', compact(
            'xml',
            'xmlNfce',
            'xmlCte',
            'xmlMdfe',
            'estado_emissao',
            'xmlEntrada',
            'xmlDevolucao',
            'start_date',
            'end_date',
            'xmlCompraFiscal',
            'filial_id'
        ));
    }

    private function getCnpjEmpresa()
    {
        $filial_id = request()->filial_id;
        if ($filial_id > 0) {
            $filial = Filial::findOrFail($filial_id);
            $cnpj = preg_replace('/[^0-9]/', '', $filial->cnpj);
        } else {
            $empresa = Empresa::find($this->empresa_id);
            $cnpj = preg_replace('/[^0-9]/', '', $empresa->configNota->cnpj);
        }
        return $cnpj;
    }

public function filtroCfopImprimir(Request $request)
{
    // Limpa qualquer saída anterior (previne o erro de código binário na tela)
    if (ob_get_contents()) ob_end_clean();

    $dataInicial = $request->dataInicial;
    $dataFinal = $request->dataFinal;
    $cfop = $request->cfop;
    $percentual = $request->percentual;
    $somaTotalVendas = $request->somaTotalVendas;

    $start_date_carbon = \Carbon\Carbon::parse($dataInicial)->startOfDay();
    $end_date_carbon = \Carbon\Carbon::parse($dataFinal)->endOfDay();

    $itensVenda = \App\Models\ItemVenda::selectRaw('sum(quantidade) AS qtd, sum(quantidade*valor) AS total, produto_id, cfop')
        ->join('vendas', 'vendas.id', '=', 'item_vendas.venda_id')
        ->where('vendas.empresa_id', $this->empresa_id)
        ->where('vendas.estado_emissao', 'APROVADO')
        ->where('item_vendas.cfop', $cfop)
        ->whereBetween('item_vendas.created_at', [$start_date_carbon, $end_date_carbon])
        ->groupBy('produto_id', 'cfop')
        ->with('produto')
        ->get();

    $itensVendaCaixa = \App\Models\ItemVendaCaixa::selectRaw('sum(quantidade) AS qtd, sum(quantidade*valor) AS total, produto_id, cfop')
        ->join('venda_caixas', 'venda_caixas.id', '=', 'item_venda_caixas.venda_caixa_id')
        ->where('venda_caixas.empresa_id', $this->empresa_id)
        ->where('venda_caixas.estado_emissao', 'APROVADO')
        ->where('item_venda_caixas.cfop', $cfop)
        ->whereBetween('item_venda_caixas.created_at', [$start_date_carbon, $end_date_carbon])
        ->groupBy('produto_id', 'cfop')
        ->with('produto')
        ->get();

    $itens = $itensVenda->concat($itensVendaCaixa)->groupBy('produto_id')->map(function ($agrupado) {
        $primeiro = $agrupado->first();
        return (object)[
            'produto_id' => $primeiro->produto_id,
            'nome'       => $primeiro->produto->nome ?? 'Produto não encontrado',
            'unidade'    => $primeiro->produto->unidade_venda ?? 'UN',
            'qtd'        => $agrupado->sum('qtd'),
            'total'      => $agrupado->sum('total'),
            'cfop'       => $primeiro->cfop
        ];
    });

    $config = \App\Models\ConfigNota::where('empresa_id', $this->empresa_id)->first();

    $p = view('enviar_xml.print', compact(
        'itens', 'dataInicial', 'dataFinal', 'cfop', 'percentual', 'somaTotalVendas', 'config'
    ))->render(); // Adicionei o ->render() aqui para garantir a string

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');
    
    $domPdf = new \Dompdf\Dompdf($options);
    $domPdf->loadHtml($p);
    $domPdf->setPaper("A4");
    $domPdf->render();

    // Retorna o PDF com o header correto para o navegador reconhecer
    return response($domPdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="relatorio_cfop_'.$cfop.'.pdf"');
}
    private function getFileXml($path)
    {
        $cnpj = $this->getCnpjEmpresa();
        $file = public_path('zips') . "/$path-$cnpj.zip";
        if (file_exists($file)) {
            return [
                'file' => $file,
                'cnpj' => $cnpj
            ];
        }
        $empresa = Empresa::find($this->empresa_id);
        // $filiais = Filia::where('empresa_id', $this->empresa_id)->get();
        // foreach ($filiais as $f) {
        $cnpj = preg_replace('/[^0-9]/', '', $empresa->cnpj);

        $file = public_path('zips') . "/$path-$cnpj.zip";
        if (file_exists($file)) {
            return [
                'file' => $file,
                'cnpj' => $cnpj
            ];
        }
        // }
        return [];
    }

    public function download()
    {
        // $public = env('SERVIDOR_WEB') ? 'public/' : '';
        $file = $this->getFileXml("xml");
        if (isset($file['file'])) {
            $this->xmlEnviado('nfe');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="xmls_nfe_' . $file['cnpj'] . '.zip"');
            readfile($file['file']);
        } else {
            echo "Arquivo não encontrado!";
        }
    }

    private function xmlEnviado($tipo)
    {
        XmlEnviado::create([
            'empresa_id' => $this->empresa_id,
            'tipo' => $tipo
        ]);
    }

    public function downloadNfce()
    {
        $file = $this->getFileXml("xmlnfce");
        if (isset($file['file'])) {
            $this->xmlEnviado('nfce');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="xmls_nfce_' . $file['cnpj'] . '.zip"');
            readfile($file['file']);
        } else {
            echo "Arquivo não encontrado!";
        }
    }

    public function emailNfce($dataInicial, $dataFinal)
    {
        $empresa = ConfigNota::where('empresa_id', $this->empresa_id)
        ->first();
        if ($empresa->usar_email_proprio) {
            die;
            $file = $this->getFileXml("xmlnfce");
            $fileDir = public_path('zips') . '/xmlnfce-' . $file['cnpj'] . '.zip';
            $this->xmlEnviado('nfce');
            $subject = 'XML de NFCe';
            $body = '<h1>Envio de XML</h1>';
            $body .= '<h3>Empresa: ' . $empresa->razao_social . '</h3>';
            $body .= '<h3>CNPJ: ' . $empresa->cnpj . '</h3>';
            $body .= '<h4>Período: ' . $dataInicial . ' - ' . $dataFinal . '</h4>';
            $send = $this->enviaEmailPHPMailer($fileDir, $subject, $body);
            if (!isset($send['erro'])) {
                session()->flash('flash_sucesso', 'Email enviado');
            } else {
                session()->flash('flash_erro', $send['erro']);
            }
            return redirect()->back();
        } else {
            Mail::send('mail.xml', [
                'data_inicial' => $dataInicial, 'data_final' => $dataFinal,
                'empresa' => $empresa->razao_social, 'cnpj' => $empresa->cnpj, 'tipo' => 'NFCe'
            ], function ($m) {
                $escritorio = EscritorioContabil::where('empresa_id', $this->empresa_id)
                ->first();
                if ($escritorio == null) {
                    echo "<h1>Configure o email do escritório <a target='_blank' href='/escritorio'>aqui</a></h1>";
                    die();
                }
                // $public = env('SERVIDOR_WEB') ? 'public/' : '';
                $file = $this->getFileXml("xmlnfce");
                $fileDir = public_path('zips') . '/xmlnfce-' . $file['cnpj'] . '.zip';
                $this->xmlEnviado('nfce');
                $nomeEmail = env('MAIL_NAME');
                $nomeEmail = str_replace("_", " ", $nomeEmail);
                $emailEnvio = env('MAIL_USERNAME');
                $m->from($emailEnvio, $nomeEmail);

                $m->from(env('MAIL_USERNAME'), $nomeEmail);
                $m->subject('Envio de XML');
                $m->attach($fileDir);
                $m->to($escritorio->email);
            });
            echo '<h1>Email enviado</h1>';
        }
    }

    public function emailCte($dataInicial, $dataFinal)
    {
        $empresa = ConfigNota::where('empresa_id', $this->empresa_id)
        ->first();
        if ($empresa->usar_email_proprio) {
            die;
            $file = $this->getFileXml("xmlcte");
            $fileDir = public_path('zips') . '/xmlcte-' . $file['cnpj'] . '.zip';
            $this->xmlEnviado('cte');
            $subject = 'XML de CTe';
            $body = '<h1>Envio de XML</h1>';
            $body .= '<h3>Empresa: ' . $empresa->razao_social . '</h3>';
            $body .= '<h3>CNPJ: ' . $empresa->cnpj . '</h3>';
            $body .= '<h4>Período: ' . $dataInicial . ' - ' . $dataFinal . '</h4>';
            $send = $this->enviaEmailPHPMailer($fileDir, $subject, $body);
            if (!isset($send['erro'])) {
                session()->flash('flash_sucesso', 'Email enviado');
            } else {
                session()->flash('flash_erro', $send['erro']);
            }
            return redirect()->back();
        } else {
            Mail::send('mail.xml', [
                'data_inicial' => $dataInicial, 'data_final' => $dataFinal,
                'empresa' => $empresa->razao_social, 'cnpj' => $empresa->cnpj, 'tipo' => 'Cte'
            ], function ($m) {
                $escritorio = EscritorioContabil::where('empresa_id', $this->empresa_id)
                ->first();
                if ($escritorio == null) {
                    echo "<h1>Configure o email do escritório <a target='_blank' href='/escritorio'>aqui</a></h1>";
                    die();
                }
                // $public = env('SERVIDOR_WEB') ? 'public/' : '';
                $file = $this->getFileXml("xmlcte");
                $fileDir = public_path('zips') . '/xmlcte-' . $file['cnpj'] . '.zip';
                $this->xmlEnviado('cte');
                $nomeEmail = env('MAIL_NAME');
                $nomeEmail = str_replace("_", " ", $nomeEmail);
                $emailEnvio = env('MAIL_USERNAME');
                $m->from($emailEnvio, $nomeEmail);

                $m->from(env('MAIL_USERNAME'), $nomeEmail);
                $m->subject('Envio de XML');
                $m->attach($fileDir);
                $m->to($escritorio->email);
            });
            echo '<h1>Email enviado</h1>';
        }
    }

    public function emailMdfe($dataInicial, $dataFinal)
    {
        $empresa = ConfigNota::where('empresa_id', $this->empresa_id)
        ->first();
        if ($empresa->usar_email_proprio) {
            die;
            $file = $this->getFileXml("xmlmdfe");
            $fileDir = public_path('zips') . '/xmlmdfe-' . $file['cnpj'] . '.zip';
            $this->xmlEnviado('mdfe');
            $subject = 'XML de MDFe';
            $body = '<h1>Envio de XML</h1>';
            $body .= '<h3>Empresa: ' . $empresa->razao_social . '</h3>';
            $body .= '<h3>CNPJ: ' . $empresa->cnpj . '</h3>';
            $body .= '<h4>Período: ' . $dataInicial . ' - ' . $dataFinal . '</h4>';
            $send = $this->enviaEmailPHPMailer($fileDir, $subject, $body);
            if (!isset($send['erro'])) {
                session()->flash('flash_sucesso', 'Email enviado');
            } else {
                session()->flash('flash_erro', $send['erro']);
            }
            return redirect()->back();
        } else {
            Mail::send('mail.xml', [
                'data_inicial' => $dataInicial, 'data_final' => $dataFinal,
                'empresa' => $empresa->razao_social, 'cnpj' => $empresa->cnpj, 'tipo' => 'MDFe'
            ], function ($m) {
                $escritorio = EscritorioContabil::where('empresa_id', $this->empresa_id)
                ->first();
                if ($escritorio == null) {
                    echo "<h1>Configure o email do escritório <a target='_blank' href='/escritorio'>aqui</a></h1>";
                    die();
                }
                // $public = env('SERVIDOR_WEB') ? 'public/' : '';
                $file = $this->getFileXml("xmlmdfe");
                $fileDir = public_path('zips') . '/xmlmdfe-' . $file['cnpj'] . '.zip';
                $this->xmlEnviado('mdfe');
                $nomeEmail = env('MAIL_NAME');
                $nomeEmail = str_replace("_", " ", $nomeEmail);
                $emailEnvio = env('MAIL_USERNAME');
                $m->from($emailEnvio, $nomeEmail);

                $m->from(env('MAIL_USERNAME'), $nomeEmail);
                $m->subject('Envio de XML');
                $m->attach($fileDir);
                $m->to($escritorio->email);
            });
            echo '<h1>Email enviado</h1>';
        }
    }

    public function email($dataInicial, $dataFinal)
    {
        $empresa = ConfigNota::where('empresa_id', $this->empresa_id)
        ->first();
        if ($empresa->usar_email_proprio) {
            $file = $this->getFileXml("xml");
            $fileDir = public_path('zips') . '/xml-' . $file['cnpj'] . '.zip';
            $this->xmlEnviado('nfe');
            $subject = 'XML de NFe';
            $body = '<h1>Envio de XML</h1>';
            $body .= '<h3>Empresa: ' . $empresa->razao_social . '</h3>';
            $body .= '<h3>CNPJ: ' . $empresa->cnpj . '</h3>';
            $body .= '<h4>Período: ' . $dataInicial . ' - ' . $dataFinal . '</h4>';
            $send = $this->enviaEmailPHPMailer($fileDir, $subject, $body);
            if (!isset($send['erro'])) {
                session()->flash('flash_sucesso', 'Email enviado');
            } else {
                session()->flash('flash_erro', $send['erro']);
            }
            return redirect()->back();
        } else {
            Mail::send('mail.xml', [
                'data_inicial' => $dataInicial, 'data_final' => $dataFinal,
                'empresa' => $empresa->razao_social, 'cnpj' => $empresa->cnpj, 'tipo' => 'NFe'
            ], function ($m) {
                // $public = env('SERVIDOR_WEB') ? 'public/' : '';
                $file = $this->getFileXml("xml");
                if (!isset($file['cnpj'])) {
                    session()->flash('flash_erro', 'Arquivo não encontrado!');
                    return redirect()->back();
                }
                $fileDir = public_path('zips') . '/xml-' . $file['cnpj'] . '.zip';
                $this->xmlEnviado('nfe');

                $escritorio = EscritorioContabil::where('empresa_id', $this->empresa_id)
                ->first();

                if ($escritorio == null) {
                    echo "<h1>Configure o email do escritório <a target='_blank' href='/escritorio'>aqui</a></h1>";
                    die();
                }
                $nomeEmail = env('MAIL_NAME');
                $nomeEmail = str_replace("_", " ", $nomeEmail);
                $m->from(env('MAIL_USERNAME'), $nomeEmail);
                $m->subject('Envio de XML');
                $m->attach($fileDir);
                $m->to($escritorio->email);
            });
            echo '<h1>Email enviado</h1>';
        }
    }

    public function downloadCte()
    {
        $file = $this->getFileXml("xmlcte");
        if (isset($file['file'])) {
            $this->xmlEnviado('cte');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="xmls_cte_' . $file['cnpj'] . '.zip"');
            readfile($file['file']);
        } else {
            echo "Arquivo não encontrado!";
        }
    }

    public function downloadMdfe()
    {
        $file = $this->getFileXml("xmlmdfe");
        if (isset($file['file'])) {
            $this->xmlEnviado('mdfe');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="xmls_mdfe_' . $file['cnpj'] . '.zip"');
            readfile($file['file']);
        } else {
            echo "Arquivo não encontrado!";
        }
    }
    
    public function downloadCompraFiscal(){
        $file = $this->getFileXml("xmlCompraFiscal");
        if(isset($file['file'])){
            $this->xmlEnviado('nfe');

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="xmls_comprafiscal_'.$file['cnpj'].'.zip"');
            readfile($file['file']);
        }else{
            echo "Arquivo não encontrado!";
        }

    }
}
