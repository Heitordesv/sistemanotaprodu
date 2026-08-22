<?php

namespace App\Http\Controllers;

use App\Helpers\StockMove;
use App\Models\Devolucao;
use App\Models\Cidade;
use App\Models\Fornecedor;
use App\Models\ConfigNota;
use App\Models\ItemDevolucao;
use App\Models\Tributacao;
use App\Models\Transportadora;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DevolucaoService;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\Daevento;

class DevolucaoController extends Controller
{
    public function __construct()
    {
        if (!is_dir(public_path('xml_devolucao_entrada'))) {
            mkdir(public_path('xml_devolucao_entrada'), 0777, true);
        }
        if (!is_dir(public_path('xml_devolucao'))) {
            mkdir(public_path('xml_devolucao'), 0777, true);
        }
        if (!is_dir(public_path('xml_devolucao_cancelada'))) {
            mkdir(public_path('xml_devolucao_cancelada'), 0777, true);
        }
        if (!is_dir(public_path('xml_devolucao_correcao'))) {
            mkdir(public_path('xml_devolucao_correcao'), 0777, true);
        }
    }

    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $fornecedor_id = $request->get('fornecedor_id');
        $data = Devolucao::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($fornecedor_id), function ($query) use ($fornecedor_id) {
            return $query->where('fornecedor_id', $fornecedor_id);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('devolucao.index', compact('data'));
    }

    public function create()
    {
        return view('devolucao.create');
    }

    public function edit($id)
    {
        $item = Devolucao::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->first();
        $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();
        return view('devolucao.edit', compact('item', 'naturezas', 'transportadoras'));
    }

    public function viewXml(Request $request)
    {
        if ($request->hasFile('xml')) {
            $xml = simplexml_load_file($request->xml);
            if (!isset($xml->NFe->infNFe)) {
                session()->flash('flash_erro', 'Este xml não é uma NFe');
                return redirect()->route('devolucao.create');
            }
            if (!$this->validaChave($xml->NFe->infNFe->attributes()->Id)) {
                session()->flash('flash_erro', 'Este XML de devolução já esta incluido no sistema com estado aprovado!');
                // return redirect("/devolucao/nova");
            }
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
                'cidade_id' => $cidade->id
            ];
            $transportadora = null;
            $transportadoraDoc = null;
            if ($xml->NFe->infNFe->transp) {
                $transp = $xml->NFe->infNFe->transp->transporta;
                $veic = $xml->NFe->infNFe->transp->veicTransp;
                $transportadoraDoc = (int)$transp->CNPJ;
                $vol = $xml->NFe->infNFe->transp->vol;
                $modFrete = $xml->NFe->infNFe->transp;
                $transportadora = [
                    'transportadora_nome' => (string)$transp->xNome,
                    'transportadora_cidade' => (string)$transp->xMun,
                    'transportadora_uf' => (string)$transp->UF,
                    'transportadora_cpf_cnpj' => (string)$transp->CNPJ,
                    'transportadora_ie' => (int)$transp->IE,
                    'transportadora_endereco' => (string)$transp->xEnder,
                    'frete_quantidade' => (float)$vol->qVol,
                    'frete_especie' => (string)$vol->esp,
                    'frete_marca' => '',
                    'frete_numero' => 0,
                    'frete_tipo' => (int)$modFrete,
                    'veiculo_placa' => (string)$veic->placa,
                    'veiculo_uf' => (string)$veic->UF,
                    'frete_peso_bruto' => (float)$vol->pesoB,
                    'frete_peso_liquido' => (float)$vol->pesoL,
                    'despesa_acessorias' => (float)$xml->NFe->infNFe->total->ICMSTot->vOutro
                ];
            }
            $vFrete = number_format(
                (float) $xml->NFe->infNFe->total->ICMSTot->vFrete,
                2,
                ",",
                "."
            );
            $vDesc = number_format((float) $xml->NFe->infNFe->total->ICMSTot->vDesc, 2, ",", ".");
            $idFornecedor = 0;
            $fornecedorEncontrado = $this->verificaFornecedor($dadosEmitente['cnpj'] == '' ? $dadosEmitente['cpf'] : $dadosEmitente['cnpj']);
            $dadosAtualizados = [];
            if ($fornecedorEncontrado) {
                $idFornecedor = $fornecedorEncontrado->id;
                $dadosAtualizados = $this->verificaAtualizacao($fornecedorEncontrado, $dadosEmitente);
            } else {
                array_push($dadosAtualizados, "Fornecedor cadastrado com sucesso");
                $idFornecedor = $this->cadastrarFornecedor($dadosEmitente);
            }

            $idTransportadora = 0;
            if ($transportadoraDoc != null) {
                $transportadoraEncontrada = $this->verificaTransportadora($transportadoraDoc);
                if ($transportadoraEncontrada) {
                    $idTransportadora = $transportadoraEncontrada->id;
                } else {
                    array_push(
                        $dadosAtualizados,
                        "Transportadora cadastrada com sucesso"
                    );
                    $idTransportadora = $this->cadastrarTransportadora($transportadora);
                }
            }
            $seq = 0;
            $itens = [];
            $contSemRegistro = 0;
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
            $tributacao = Tributacao::where('empresa_id', request()->empresa_id)
            ->first();
            foreach ($xml->NFe->infNFe->det as $item) {
                $trib = Devolucao::getTrib($item->imposto);

                $item = [
                    'codigo' => $item->prod->cProd,
                    'xProd' => $item->prod->xProd,
                    'ncm' => $item->prod->NCM,
                    'vFrete' => $item->prod->vFrete ?? 0,
                    'cfop' => $item->prod->CFOP,
                    'unidade_medida' => $item->prod->uCom,
                    'vUnCom' => $item->prod->vUnCom,
                    'qCom' => $item->prod->qCom,
                    'vDesc' => $item->prod->vDesc,
                    'codBarras' => $item->prod->cEAN ?? '',
                    'CEST' => $item->prod->CEST ?? 0,
                    'cst_csosn' => $trib['cst_csosn'],
                    'cst_pis' => $trib['cst_pis'],
                    'cst_cofins' => $trib['cst_cofins'],
                    'cst_ipi' => $trib['cst_ipi'],
                    'perc_icms' => $trib['pICMS'],
                    'perc_pis' => $trib['pPIS'],
                    'perc_cofins' => $trib['pCOFINS'],
                    'perc_ipi' => $trib['pIPI'],
                    'pRedBC' => $trib['pRedBC'],
                    'modBCST' => $trib['modBCST'],
                    'vBCST' => $trib['vBCST'],
                    'pICMSST' => $trib['pICMSST'],
                    'vICMSST' => $trib['vICMSST'],
                    'pMVAST' => $trib['pMVAST'],
                    'codigo_anp' => $trib['codigo_anp'] ?? 0,
                    'valor_partida' => $trib['valor_partida'] ?? 0,
                    'perc_glp' => $trib['perc_glp'] ?? 0,
                    'perc_gnn' => $trib['perc_gnn'] ?? 0,
                    'perc_gni' => $trib['perc_gni'] ?? 0,
                ];

                array_push($itens, $item);
            }
            $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
            $dadosNf = [
                'chave' => $chave,
                'vProd' => $xml->NFe->infNFe->total->ICMSTot->vProd,
                'indPag' => $xml->NFe->infNFe->ide->indPag,
                'nNf' => $xml->NFe->infNFe->ide->nNF,
                'vFrete' => $vFrete,
                'vDesc' => $vDesc,
            ];

            $fatura = [];
            if (!empty($xml->NFe->infNFe->cobr->dup)) {
                foreach ($xml->NFe->infNFe->cobr->dup as $dup) {
                    $titulo = $dup->nDup;
                    $vencimento = $dup->dVenc;
                    $vencimento = explode('-', $vencimento);
                    $vencimento = $vencimento[2] . "/" . $vencimento[1] . "/" . $vencimento[0];
                    $vlr_parcela = number_format((float) $dup->vDup, 2, ",", ".");
                    $parcela = [
                        'numero' => $titulo,
                        'vencimento' => $vencimento,
                        'valor_parcela' => $vlr_parcela
                    ];
                    array_push($fatura, $parcela);
                }
            }
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
            $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
            ->get();
            $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)
            ->get();


            $file = $request->xml;
            $nameArchive = $chave . ".xml";
            $pathXml = $file->move(public_path('xml_devolucao_entrada'), $nameArchive);

            $tipoFrete = 0;
            if ($transportadora != null) {
                $tipoFrete = $transportadora['frete_tipo'];
            }

            return view('devolucao.view_xml', compact(
                'fatura',
                'tipoFrete',
                'dadosNf',
                'naturezas',
                'config',
                'cidade',
                'transportadora',
                'dadosEmitente',
                'transportadoras',
                'dadosAtualizados',
                'itens',
                'idTransportadora',
                'idFornecedor',
                'pathXml',
                'nameArchive'
            ));
        } else {
            session()->flash('flash_erro', 'XML inválido!');
            return redirect()->route('devolucao.create');
        }
    }

    private function validaChave($chave)
    {
        $chave = substr($chave, 3, 44);
        $item = Devolucao::where('empresa_id', request()->empresa_id)
        ->where('chave_nf_entrada', $chave)
        ->where('estado_emissao', 1)
        ->first();
        return $item == null ? true : false;
    }

    private function verificaFornecedor($doc)
    {
        if (strlen($doc) == 14) {
            $doc = $this->formataCnpj($doc);
        } else {
            $doc = $this->formataCpf($doc);
        }
        $item = Fornecedor::verificaCadastrado($doc);
        return $item;
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

    private function formataCpf($cnpj)
    {
        $temp = substr($cnpj, 0, 3);
        $temp .= "." . substr($cnpj, 3, 3);
        $temp .= "." . substr($cnpj, 6, 3);
        $temp .= "-" . substr($cnpj, 9, 2);
        return $temp;
    }

    private function formataCep($cep)
    {
        $temp = substr($cep, 0, 5);
        $temp .= "-" . substr($cep, 5, 3);
        return $temp;
    }

    private function formataTelefone($fone)
    {
        $temp = substr($fone, 0, 2);
        $temp .= " " . substr($fone, 2, 4);
        $temp .= "-" . substr($fone, 4, 4);
        return $temp;
    }

    private function cadastrarFornecedor($fornecedor)
    {
        $doc = $fornecedor['cnpj'] == '' ? $fornecedor['cpf'] : $fornecedor['cnpj'];
        if (strlen($doc) == 14) {
            $doc = $this->formataCnpj($doc);
        } else {
            $doc = $this->formataCpf($doc);
        }
        $result = Fornecedor::create([
            'razao_social' => $fornecedor['razaoSocial'],
            'nome_fantasia' => $fornecedor['nomeFantasia'],
            'rua' => $fornecedor['logradouro'],
            'numero' => $fornecedor['numero'],
            'bairro' => $fornecedor['bairro'],
            'cep' => $this->formataCep($fornecedor['cep']),
            'cpf_cnpj' => $doc,
            'ie_rg' => $fornecedor['ie'],
            'celular' => '*',
            'telefone' => $this->formataTelefone($fornecedor['fone']),
            'email' => '*',
            'cidade_id' => $fornecedor['cidade_id'],
            'empresa_id' => request()->empresa_id,
            'contribuinte' => 1
        ]);
        return $result->id;
    }

    private function verificaTransportadora($cnpj)
    {
        $transp = Transportadora::verificaCadastrado($cnpj);
        return $transp;
    }

    private function verificaAtualizacao($fornecedorEncontrado, $dadosEmitente)
    {
        $dadosAtualizados = [];
        $verifica = $this->dadosAtualizados(
            'Razao Social',
            $fornecedorEncontrado->razao_social,
            $dadosEmitente['razaoSocial']
        );
        if ($verifica) array_push($dadosAtualizados, $verifica);
        $verifica = $this->dadosAtualizados(
            'Nome Fantasia',
            $fornecedorEncontrado->nome_fantasia,
            $dadosEmitente['nomeFantasia']
        );
        if ($verifica) array_push($dadosAtualizados, $verifica);
        $verifica = $this->dadosAtualizados(
            'Rua',
            $fornecedorEncontrado->rua,
            $dadosEmitente['logradouro']
        );
        if ($verifica) array_push($dadosAtualizados, $verifica);
        $verifica = $this->dadosAtualizados(
            'Numero',
            $fornecedorEncontrado->numero,
            $dadosEmitente['numero']
        );
        if ($verifica) array_push($dadosAtualizados, $verifica);
        $verifica = $this->dadosAtualizados(
            'Bairro',
            $fornecedorEncontrado->bairro,
            $dadosEmitente['bairro']
        );
        if ($verifica) array_push($dadosAtualizados, $verifica);
        $verifica = $this->dadosAtualizados(
            'IE',
            $fornecedorEncontrado->ie_rg,
            $dadosEmitente['ie']
        );
        if ($verifica) array_push($dadosAtualizados, $verifica);
        $this->atualizar($fornecedorEncontrado, $dadosEmitente);
        return $dadosAtualizados;
    }

    private function dadosAtualizados($campo, $anterior, $atual)
    {
        if ($anterior != $atual) {
            return $campo . " atualizado";
        }
        return false;
    }

    private function atualizar($fornecedor, $dadosEmitente)
    {
        $fornecedor->razao_social = $dadosEmitente['razaoSocial'];
        $fornecedor->nome_fantasia = $dadosEmitente['nomeFantasia'];
        $fornecedor->rua = $dadosEmitente['logradouro'];
        $fornecedor->ie_rg = $dadosEmitente['ie'];
        $fornecedor->bairro = $dadosEmitente['bairro'];
        $fornecedor->numero = $dadosEmitente['numero'];
        $fornecedor->save();
    }

    private function cadastrarTransportadora($transp)
    {
        $cidade = Cidade::where('nome', $transp['transportadora_cidade'])
        ->first();
        if ($cidade == null) {
            $cidade = Cidade::where('uf', $transp['transportadora_uf'])
            ->first();
        }
        $result = Transportadora::create([
            'razao_social' => $transp['transportadora_nome'],
            'cnpj_cpf' => $transp['transportadora_cpf_cnpj'],
            'logradouro' => $transp['transportadora_endereco'],
            'cidade_id' => $cidade == null ? 1 : $cidade->id,
            'empresa_id' => request()->empresa_id
        ]);
        return $result->id;
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $devolucao = Devolucao::create([
                    'fornecedor_id' => $request->fornecedor_id,
                    'usuario_id' => get_id_user(),
                    'natureza_id' => $request->natureza_id,
                    'valor_integral' => (float)$request->valor_integral,
                    'valor_devolvido' => (float)$request->valor_devolucao,
                    'motivo' => $request->motivo ?? '',
                    'observacao' => $request->observacao ?? '',
                    'estado_emissao' => 'novo',
                    'devolucao_parcial' => $request->valor_integral == $request->valor_devolucao ? 1 : 0,
                    'chave_nf_entrada' => $request->chave_nf_entrada,
                    'nNf' => $request->nNf,
                    'vFrete' => $request->vFrete,
                    'vDesc' => $request->vDesc ? __convert_value_bd($request->vDesc) : 0,
                    'chave_gerada' => '',
                    'numero_gerado' => 0,
                    'tipo' => $request->tipo,
                    'empresa_id' => $request->empresa_id,
                    'transportadora_nome' => $transportadora['transportadora_cidade'] ?? '',
                    'transportadora_cidade' => $transportadora['transportadora_cidade'] ?? '',
                    'transportadora_uf' => $request->transportadora_uf ?? '',
                    'transportadora_cpf_cnpj' => $transportadora['transportadora_cpf_cnpj'] ?? '',
                    'transportadora_ie' => $transportadora['transportadora_ie'] ?? '',
                    'transportadora_endereco' => $transportadora['transportadora_endereco'] ?? '',
                    'frete_quantidade' => $request->frete_quantidade ?? 0,
                    'frete_especie' => $request->frete_especie ?? '',
                    'frete_marca' => $transportadora['frete_marca'] ?? '',
                    'frete_numero' => $transportadora['frete_numero'] ?? 0,
                    'frete_tipo' => $request->frete_tipo ?? 0,
                    'veiculo_placa' => $request->veiculo_placa ?? '',
                    'veiculo_uf' => $request->veiculo_uf ?? '',
                    'frete_peso_bruto' => __convert_value_bd($request->frete_peso_bruto) ?? 0,
                    'frete_peso_liquido' => __convert_value_bd($request->frete_peso_liquido) ?? 0,
                    'despesa_acessorias' => __convert_value_bd($request->despesa_acessorias) ?? 0,
                    'transportadora_id' => $request->transportadora_id
                ]);

                $stockMove = new StockMove();
                // dd($request->all());
                for ($i = 0; $i < sizeof($request->nome); $i++) {
                    ItemDevolucao::create([
                        'cod' => $request->codigo[$i],
                        'nome' => $request->nome[$i],
                        'ncm' => $request->ncm[$i],
                        'cfop' => $request->cfop[$i],
                        'valor_unit' => __convert_value_bd($request->valor_unit[$i]),
                        'vFrete' => $request->vfrete[$i] ?? 0,
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'item_parcial' => $request->item_parcial[$i] ?? 0,
                        'unidade_medida' => $request->unidade_medida[$i],
                        'codBarras' => $request->codBarras[$i] ?? '',
                        'devolucao_id' => $devolucao->id,
                        'cst_csosn' => $request->cst_csosn[$i],
                        'cst_pis' => $request->cst_pis[$i],
                        'cst_cofins' => $request->cst_cofins[$i],
                        'cst_ipi' => $request->cst_ipi[$i],
                        //'vDesc' => $request->vDesc[$i] ? __convert_value_bd($request->vDesc[$i]) : 0,
                        'vDesc' => isset($request->vDesc[$i]) 
    ? __convert_value_bd($request->vDesc[$i]) 
    : 0,
                        'perc_icms' => $request->perc_icms[$i],
                        'perc_pis' => $request->perc_pis[$i],
                        'perc_cofins' => $request->perc_cofins[$i],
                        'perc_ipi' => $request->perc_ipi[$i],
                        'pRedBC' => $request->pRedBC[$i] ?? 0,
                        'modBCST' => $request->modBCST[$i] ?? 0,
                        'vBCST' => $request->vBCST[$i] ?? 0,
                        'pICMSST' => $request->pOCMSST[$i] ?? 0,
                        'vICMSST' => $request->vICMSST[$i] ?? 0,
                        'pMVAST' => $request->pMVAST[$i] ?? 0,
                        'vBCSTRet' => $request->vBCSTRet[$i] ?? 0,
                        'pST' => $request->pST[$i] ?? 0,
                        'vICMSSubstituto' => $request->vICMSSubstituto[$i] ?? 0,
                        'vICMSSTRet' => $request->vICMSSTRet[$i] ?? 0,
                        'orig' => $request->orig[$i] ?? 0,
                        'codigo_anp' => $request->codigo_anp[$i] ?? '',
                        'descricao_anp' => $request->descricao_anp[$i] ?? '',
                        'uf_cons' => $request->uf_cons[$i] ?? '',
                        'valor_partida' => $request->valor_partida[$i] ?? 0,
                        'perc_glp' => $request->perc_glp[$i] ?? 0,
                        'perc_gnn' => $request->perc_gnn[$i] ?? 0,
                        'perc_gni' => $request->perc_gni[$i] ?? 0,
                        'unidade_tributavel' => $request->unidade_tributavel[$i] ?? '',
                        'quantidade_tributavel' => $request->quantidade_tributavel[$i] ?? 0,
                        'CEST' => $request->CEST[$i] ?? '',
                    ]);

                    if (env("DEVOLUCAO_ALTERA_ESTOQUE") == 1) {
                        $produto = Produto::where('nome', $request->xProd[$i])->first();
                        if ($produto != null) {
                            $stockMove->downStock(
                                (int) $produto->id,
                                (float) __convert_value_bd($request->quantidade[$i])
                            );
                        }
                    }
                }
                // return $i;
            });
            // return response()->json($result);
session()->flash('flash_sucesso', 'Devolução criada com sucesso!');
} catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
    __saveLogError($e, request()->empresa_id);
    return response()->json($e->getMessage(), 400);
}
return redirect()->route('devolucao.index');
}

public function update(Request $request, $id)
{
    $item = Devolucao::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    try {
        $request->merge([
            'fornecedor_id' => $request->fornecedor_id,
            'usuario_id' => get_id_user(),
            'natureza_id' => $request->natureza_id,
            'valor_integral' => $request->valor_integral,
            'valor_devolvido' => $request->valor_devolucao,
            'motivo' => $request->motivo ?? '',
            'observacao' => $request->observacao ?? '',
                // 'estado' => 'novo',
            'devolucao_parcial' => $request->valor_integral == $request->valor_devolucao ? 1 : 0,
            'chave_nf_entrada' => $request->xmlEntrada,
            'nNf' => $request->nNf,
            'vFrete' => $request->vFrete,
            'vDesc' => $request->vDesc ? __convert_value_bd($request->vDesc) : 0,
            'chave_gerada' => '',
            'numero_gerado' => 0,
            'tipo' => $request->tipo,
            'empresa_id' => $request->empresa_id,
            'transportadora_nome' => $transportadora['transportadora_cidade'] ?? '',
            'transportadora_cidade' => $transportadora['transportadora_cidade'] ?? '',
            'transportadora_uf' => $request->transportadora_uf ?? '',
            'transportadora_cpf_cnpj' => $transportadora['transportadora_cpf_cnpj'] ?? '',
            'transportadora_ie' => $transportadora['transportadora_ie'] ?? '',
            'transportadora_endereco' => $transportadora['transportadora_endereco'] ?? '',
            'frete_quantidade' => $request->frete_quantidade ?? 0,
            'frete_especie' => $request->frete_especie ?? '',
            'frete_marca' => $transportadora['frete_marca'] ?? '',
            'frete_numero' => $transportadora['frete_numero'] ?? 0,
            'frete_tipo' => $request->frete_tipo ?? 0,
            'veiculo_placa' => $request->veiculo_placa ?? '',
            'veiculo_uf' => $request->veiculo_uf ?? '',
            'frete_peso_bruto' => __convert_value_bd($request->frete_peso_bruto) ?? 0,
            'frete_peso_liquido' => __convert_value_bd($request->frete_peso_liquido) ?? 0,
            'despesa_acessorias' => __convert_value_bd($request->despesa_acessorias) ?? 0,
            'transportadora_id' => $request->transportadora_id
        ]);
        $item->fill($request->all())->save();

        $item->itens()->delete();

        $stockMove = new StockMove();

        for ($i = 0; $i < sizeof($request->nome); $i++) {
            ItemDevolucao::create([
                'cod' => $request->codigo[$i],
                'nome' => $request->nome[$i],
                'ncm' => $request->ncm[$i],
                'cfop' => $request->cfop[$i],
                'valor_unit' => __convert_value_bd($request->valor_unit[$i]),
                'vFrete' => $request->vfrete[$i] ?? 0,
                'quantidade' => __convert_value_bd($request->quantidade[$i]),
                'item_parcial' => $request->item_parcial[$i] ?? 0,
                'unidade_medida' => $request->unidade_medida[$i],
                'codBarras' => $request->codBarras[$i] ?? '',
                'devolucao_id' => $item->id,
                'cst_csosn' => $request->cst_csosn[$i],
                'cst_pis' => $request->cst_pis[$i],
                'cst_cofins' => $request->cst_cofins[$i],
                'cst_ipi' => $request->cst_ipi[$i],
                'perc_icms' => $request->perc_icms[$i],
                'perc_pis' => $request->perc_pis[$i],
                'perc_cofins' => $request->perc_cofins[$i],
                'perc_ipi' => $request->perc_ipi[$i],
                'pRedBC' => $request->pRedBC[$i] ?? 0,
                'vDesc' => $request->vDesc[$i] ? __convert_value_bd($request->vDesc[$i]) : 0,
                'modBCST' => $request->modBCST[$i] ?? 0,
                'vBCST' => $request->vBCST[$i] ?? 0,
                'pICMSST' => $request->pOCMSST[$i] ?? 0,
                'vICMSST' => $request->vICMSST[$i] ?? 0,
                'pMVAST' => $request->pMVAST[$i] ?? 0,
                'vBCSTRet' => $request->vBCSTRet[$i] ?? 0,
                'pST' => $request->pST[$i] ?? 0,
                'vICMSSubstituto' => $request->vICMSSubstituto[$i] ?? 0,
                'vICMSSTRet' => $request->vICMSSTRet[$i] ?? 0,
                'orig' => $request->orig[$i] ?? 0,
                'codigo_anp' => $request->codigo_anp[$i] ?? '',
                'descricao_anp' => $request->descricao_anp[$i] ?? '',
                'uf_cons' => $request->uf_cons[$i] ?? '',
                'valor_partida' => $request->valor_partida[$i] ?? 0,
                'perc_glp' => $request->perc_glp[$i] ?? 0,
                'perc_gnn' => $request->perc_gnn[$i] ?? 0,
                'perc_gni' => $request->perc_gni[$i] ?? 0,
                'unidade_tributavel' => $request->unidade_tributavel[$i] ?? '',
                'quantidade_tributavel' => $request->quantidade_tributavel[$i] ?? 0,
                'CEST' => $request->CEST[$i] ?? '',
            ]);

            if (env("DEVOLUCAO_ALTERA_ESTOQUE") == 1) {
                $produto = Produto::where('nome', $request->xProd[$i])->first();
                if ($produto != null) {
                    $stockMove->downStock(
                        (int) $produto->id,
                        (float) __convert_value_bd($request->quantidade[$i])
                    );
                }
            }
        }
        session()->flash('flash_sucesso', 'Atualizado com sucesso!');
    } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
        __saveLogError($e, request()->empresa_id);
        return response()->json($e->getMessage(), 400);
    }
    return redirect()->route('devolucao.index');
}

public function destroy($id)
{
    $item = Devolucao::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    try {
        $item->delete();
        session()->flash('flash_sucesso', 'Deletado com sucesso!');
    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }
    return redirect()->route('devolucao.index');
}

public function estadoFiscal($id)
{
    $item = Devolucao::findOrfail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    return view('devolucao.estado_fiscal', compact('item'));
}

public function estadoFiscalStore(Request $request)
{
    try {
        $item = Devolucao::findOrfail($request->devolucao_id);
        $estado_emissao = $request->estado_emissao;
        $item->estado_emissao = $estado_emissao;
        if ($request->hasFile('xml')) {

            $xml = simplexml_load_file($request->xml);
            $chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);
            $file = $request->file;
            $file->move(public_path('xml_devolucao'), $chave . '.xml');
            $item->chave_gerada = $chave;
            $item->numero_gerado = (int)$xml->NFe->infNFe->ide->nNF;
        }
        $item->save();
        session()->flash("flash_sucesso", "Estado alterado");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Erro: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }
    return redirect()->back();
}

public function xmlTemp($id)
{

    $item = Devolucao::where('empresa_id', request()->empresa_id)
    ->where('id', $id)
    ->first();

    $config = ConfigNota::where('empresa_id', request()->empresa_id)
    ->first();

    $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

    $devolucao_service = new DevolucaoService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$config->ambiente,
        "razaosocial" => $config->razao_social,
        "siglaUF" => $config->cidade->uf,
        "cnpj" => $cnpj,
        "schemes" => "PL_009_V4",
        "versao" => "4.00",
        "tokenIBPT" => " v8zRciG2x1Y32X8Q_ebzXXHj5yKd6cwJgkdXgeJTak5rwqe4v4yzt0537HmXrY8G",
        "CSC" => $config->csc,
        "CSCid" => $config->csc_id
    ], $config);
    $nfe = $devolucao_service->gerarDevolucao($item);

    if (!isset($nfe['erros_xml'])) {
        $xml = $nfe['xml'];
        return response($xml)
        ->header('Content-Type', 'application/xml');
    } else {
        print_r($nfe['erros_xml']);
    }
}

public function danfeTemp($id)
{

    $item = Devolucao::where('empresa_id', request()->empresa_id)
    ->where('id', $id)
    ->first();

    $config = ConfigNota::where('empresa_id', request()->empresa_id)
    ->first();

    $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

    $devolucao_service = new DevolucaoService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$config->ambiente,
        "razaosocial" => $config->razao_social,
        "siglaUF" => $config->cidade->uf,
        "cnpj" => $cnpj,
        "schemes" => "PL_009_V4",
        "versao" => "4.00",
        "tokenIBPT" => " v8zRciG2x1Y32X8Q_ebzXXHj5yKd6cwJgkdXgeJTak5rwqe4v4yzt0537HmXrY8G",
        "CSC" => $config->csc,
        "CSCid" => $config->csc_id
    ], $config);
    $nfe = $devolucao_service->gerarDevolucao($item);

    if (!isset($nfe['erros_xml'])) {
        $xml = $nfe['xml'];
        try {
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

public function imprimir($id)
{
    $item = Devolucao::findOrFail($id);
    
    // Validação do objeto
    if (!__valida_objeto($item)) {
        abort(403);
    }

    $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();

    // Verifica se o arquivo XML existe
    $xmlPath = public_path('xml_devolucao/') . $item->chave_gerada . '.xml';
    if (!file_exists($xmlPath)) {
        return response()->json(['error' => 'Arquivo XML não encontrado!'], 404);
    }

    $xml = file_get_contents($xmlPath);

    // Processa o logo, caso exista
    $logo = null;
    if ($config && $config->logo) {
        $logoPath = public_path('logos/') . $config->logo;

        if (file_exists($logoPath)) {
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($logoPath));
        }
    }

    try {
        $danfe = new Danfe($xml);
        $danfe->setVUnComCasasDec($config->casas_decimais ?? 2); // Define casas decimais padrão caso não configurado
        $pdf = $danfe->render($logo); // Renderiza o DANFE com ou sem logo

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=DANFE_{$item->numero_gerado}.pdf");
    } catch (InvalidArgumentException $e) {
        return response()->json(['error' => "Erro no processamento: " . $e->getMessage()], 500);
    }
}


public function imprimirCorrecao($id)
{
    $item = Devolucao::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    if ($item->sequencia_cce > 0) {
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        if (file_exists(public_path('xml_devolucao_correcao/') . $item->chave_gerada . '.xml')) {
            $xml = file_get_contents(public_path('xml_devolucao_correcao/') . $item->chave_gerada . '.xml');
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

                header("Content-Disposition: ; filename=CCe $item->numero_gerado.pdf");
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

public function imprimirCancelamento($id)
{
    $item = Devolucao::findOrFail($id);
    if (!__valida_objeto($item)) {
        abort(403);
    }
    if ($item->estado_emissao == 'cancelado') {
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
        ->first();
        if (file_exists(public_path('xml_devolucao_cancelada/') . $item->chave_gerada . '.xml')) {
            $xml = file_get_contents(public_path('xml_devolucao_cancelada/') . $item->chave_gerada . '.xml');
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
                header("Content-Disposition: ; filename=Cancelamento $item->numero_gerado.pdf");
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
}
