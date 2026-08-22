<?php

namespace App\Http\Controllers;

use App\Helpers\StockMove;
use App\Models\Categoria;
use App\Models\CategoriaConta;
use App\Models\Cidade;
use App\Models\Compra;
use App\Models\ConfigNota;
use App\Models\ContaPagar;
use App\Models\DivisaoGrade;
use App\Models\Fornecedor;
use App\Models\ItemCompra;
use App\Models\ManifestaDfe;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TelaPedido;
use App\Models\Tributacao;

class CompraFiscalController extends Controller
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
        return view('compra_fiscal.index');
    }

    private function validaChave($chave)
    {
        $msg = "";
        $chave = substr($chave, 3, 44);
        $cp = Compra::where('chave', $chave)
        ->where('empresa_id', $this->empresa_id)
        ->first();
        $manifesto = ManifestaDfe::where('chave', $chave)
        ->where('empresa_id', $this->empresa_id)
        ->first();
        if ($cp != null) $msg = "XML já importado na compra fiscal";
        // if($manifesto != null) $msg .= "XML já importado através do manifesto fiscal";
        return $msg;
    }

    public function import(Request $request)
    {
        if ($request->hasFile('file')) {
            $arquivo = $request->hasFile('file');
            $xml = simplexml_load_file($request->file);
            $msgImport = $this->validaChave($xml->NFe->infNFe->attributes()->Id);
            if ($msgImport == "") {
                //var_dump($xml);
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

                $vFrete = number_format(
                    (float) $xml->NFe->infNFe->total->ICMSTot->vFrete,
                    2,
                    ",",
                    "."
                );
                $vDesc = $xml->NFe->infNFe->total->ICMSTot->vDesc;
                $idFornecedor = 0;
                $fornecedorEncontrado = $this->verificaFornecedor($dadosEmitente['cnpj']);
                $dadosAtualizados = [];
                if ($fornecedorEncontrado) {
                    $idFornecedor = $fornecedorEncontrado->id;
                    $dadosAtualizados = $this->verificaAtualizacao($fornecedorEncontrado, $dadosEmitente);
                } else {
                    array_push($dadosAtualizados, "Fornecedor cadastrado com sucesso!");
                    $idFornecedor = $this->cadastrarFornecedor($dadosEmitente);
                }
                //Produtos
                //itens
                $seq = 0;
                $itens = [];
                $contSemRegistro = 0;
                foreach ($xml->NFe->infNFe->det as $item) {
                    $produto = Produto::verificaCadastrado(
                        $item->prod->cEAN,
                        $item->prod->xProd,
                        $item->prod->cProd
                    );
                    $produtoNovo = !$produto ? true : false;
                    $codSiad = 0;
                    if ($produtoNovo) {
                        $contSemRegistro++;
                    } else {
                        $i = ItemCompra::where('produto_id', $produto->id)
                        ->first();
                        if ($i != null) {
                            $codSiad = $i->codigo_siad ?? 0;
                        }
                    }
                    $codigo = str_replace(".", "_", $item->prod->cProd);
                    $codigo = str_replace("/", "_", $codigo);
                    $codigo = str_replace("'", "_", $codigo);
                    $codigo = str_replace("-", "_", $codigo);
                    $codigo = str_replace("(", "", $codigo);
                    $codigo = str_replace(")", "", $codigo);
                    $codigo = str_replace(" ", "", $codigo);
                    $codigo = str_replace(":", "", $codigo);
                    $codigo = str_replace("[", "", $codigo);
                    $codigo = str_replace("]", "", $codigo);
                    $vIpi = 0;
                    $vICMSST = 0;
                    if (isset($item->imposto->IPI)) {
                        $valor = (float)$item->imposto->IPI->IPITrib->vIPI;
                        if ($valor > 0)
                            $vIpi = $valor / (float)$item->prod->qCom;
                    }
                    if (isset($item->imposto->ICMS)) {
                        $arr = (array_values((array)$item->imposto->ICMS));
                        $cst = $arr[0]->CST ? $arr[0]->CST : $arr[0]->CSOSN;
                        $valor = (float)$arr[0]->vICMSST ?? 0;
                        if ($valor > 0)
                            $vICMSST = $valor / $item->prod->qCom;
                    }

                    $item = [
                        'id' => !$produtoNovo ? $produto->id : 0,
                        'codigo' => $codigo,
                        'xProd' => str_replace("'", "", $item->prod->xProd),
                        'NCM' => (string)$item->prod->NCM,
                        'CEST' => $item->prod->CEST,
                        'CFOP' => $item->prod->CFOP,
                        'CFOP_entrada' => $this->getCfopEntrada($item->prod->CFOP),
                        'uCom' => $item->prod->uCom,
                        'vUnCom' => number_format((float)$item->prod->vUnCom + $vIpi + $vICMSST, 2, '.', ''),
                        'qCom' => $item->prod->qCom,
                        'codBarras' => $item->prod->cEAN,
                        'produtoNovo' => $produtoNovo,
                        'codSiad' => $codSiad,
                        'produtoId' => $produtoNovo ? '0' : $produto->id,
                        'conversao_unitaria' => $produtoNovo ? '' :
                        $produto->conversao_unitaria,
                        'valor_venda' => $produtoNovo ? 0 : $produto->valor_venda,
                        'valor_compra' => $produtoNovo ? 0 : $produto->valor_compra
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
                    'contSemRegistro' => $contSemRegistro,
                    'data_emissao' => substr($xml->NFe->infNFe->ide->dhEmi[0], 0, 16)
                ];
                //Pagamento
                $fatura = [];
                if (!empty($xml->NFe->infNFe->cobr->dup)) {
                    foreach ($xml->NFe->infNFe->cobr->dup as $dup) {
                        $titulo = $dup->nDup;
                        $vencimento = $dup->dVenc;
                        $vlr_parcela = number_format((float) $dup->vDup, 2, ".", "");
                        $parcela = [
                            'numero' => (int)$titulo,
                            'vencimento' => $vencimento,
                            'valor_parcela' => $vlr_parcela,
                            'rand' => rand(0, 10000)
                        ];
                        array_push($fatura, $parcela);
                    }
                } else {
                    $vencimento = substr($xml->NFe->infNFe->ide->dhEmi[0], 0, 10);
                    $parcela = [
                        'numero' => 1,
                        'vencimento' => $vencimento,
                        'valor_parcela' => (float)$xml->NFe->infNFe->total->ICMSTot->vProd,
                        'rand' => rand(0, 10000)
                    ];
                    array_push($fatura, $parcela);
                }
                // dd($fatura);
                //upload
                $file = $request->file;
                $nameArchive = $chave . ".xml";
                $pathXml = $file->move(public_path('xml_entrada'), $nameArchive);
                //fim upload
                $categorias = Categoria::where('empresa_id', $this->empresa_id)->get();
                $divisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
                ->where('sub_divisao', false)
                ->get();
                $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
                ->where('sub_divisao', true)
                ->get();
                $unidadesDeMedida = Produto::unidadesMedida();
                $listaCSTCSOSN = Produto::listaCSTCSOSN();
                $listaCST_PIS_COFINS = Produto::listaCST_PIS_COFINS();
                $listaCST_IPI = Produto::listaCST_IPI();
                $config = ConfigNota::where('empresa_id', $this->empresa_id)->first();
                $anps = Produto::lista_ANP();
                $naturezaPadrao = NaturezaOperacao::where('empresa_id', request()->empresa_id)->first();

                $telasPedido = TelaPedido::where('empresa_id', request()->empresa_id)->get();
                $tributacao = Tributacao::where('empresa_id', request()->empresa_id)
                ->first();
                return view('compra_fiscal.create', compact(
                    'itens',
                    'tributacao',
                    'divisoes',
                    'subDivisoes',
                    'fatura',
                    'anps',
                    'pathXml',
                    'idFornecedor',
                    'dadosNf',
                    'listaCSTCSOSN',
                    'listaCST_PIS_COFINS',
                    'listaCST_IPI', 
                    'config', 
                    'unidadesDeMedida', 
                    'categorias', 
                    'telasPedido', 
                    'dadosEmitente', 
                    'naturezaPadrao',
                    'dadosAtualizados',
                ));
            } else {
                session()->flash('flash_erro', $msgImport);
                return redirect()->route("compraFiscal.index");
            }
        } else {
            session()->flash('flash_erro', 'XML inválido!');
            return redirect()->route("compraFiscal.index");
        }
    }

    private function verificaFornecedor($cnpj)
    {
        $forn = Fornecedor::verificaCadastrado($this->formataCnpj($cnpj));
        return $forn;
    }

    private function cadastrarFornecedor($fornecedor)
    {
        $result = Fornecedor::create([
            'razao_social' => $fornecedor['razaoSocial'],
            'nome_fantasia' => $fornecedor['nomeFantasia'],
            'rua' => $fornecedor['logradouro'],
            'numero' => $fornecedor['numero'],
            'bairro' => $fornecedor['bairro'],
            'cep' => $this->formataCep($fornecedor['cep']),
            'cpf_cnpj' => $this->formataCnpj($fornecedor['cnpj']),
            'ie_rg' => $fornecedor['ie'],
            'celular' => '*',
            'telefone' => $this->formataTelefone($fornecedor['fone']),
            'email' => '*',
            'cidade_id' => $fornecedor['cidade_id'],
            'empresa_id' => $this->empresa_id
        ]);
        return $result->id;
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

    private function getCfopEntrada($cfop)
    {
        $natureza = NaturezaOperacao::where('empresa_id', $this->empresa_id)
        ->where('CFOP_saida_estadual', $cfop)
        ->first();
        if ($natureza != null) {
            return $natureza->CFOP_entrada_inter_estadual;
        }
        $natureza = NaturezaOperacao::where('empresa_id', $this->empresa_id)
        ->where('CFOP_saida_inter_estadual', $cfop)
        ->first();
        if ($natureza != null) {
            return $natureza->CFOP_entrada_inter_estadual;
        }
        $digito = substr($cfop, 0, 1);
        if ($digito == '5') {
            return '1' . substr($cfop, 1, 4);
        } else {
            return '2' . substr($cfop, 1, 4);
        }
    }

    public function store(Request $request)
    {
        //    dd($request->all());
        try {
            $result = DB::transaction(function () use ($request) {
                // $total = $this->somaItens($request);
                $request->merge([
                    'usuario_id' => get_id_user(),
                    'valor_frete' => __convert_value_bd($request->valor_frete) ?? 0,
                    'qtd_volumes' => $request->qtd_volumes ?? 0,
                    'peso_liquido' => $request->peso_liquido ?? 0,
                    'peso_bruto' => $request->peso_bruto ?? 0,
                    'desconto' => __convert_value_bd($request->desconto) ?? 0,
                    'total' => __convert_value_bd($request->total),
                    'fornecedor_id' => $request->fornecedor_id,
                    'numero_nfe' => $request->nNf ?? 0,
                    'estado' => 'novo'
                ]);
                $compra = Compra::create($request->all());
                $stockMove = new StockMove();
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    ItemCompra::create([
                        'compra_id' => $compra->id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor_unitario' => __convert_value_bd($request->valor_unitario[$i]),
                        'unidade_compra' => $request->unidade_compra[$i],
                    ]);
                    $product->valor_compra = __convert_value_bd($request->valor_unitario[$i]);
                    if ($product->reajuste_automatico) {
                        $product->valor_venda = $product->valor_compra +
                        (($product->valor_compra * $product->percentual_lucro) / 100);
                    }
                    $product->save();
                    $stockMove->pluStock(
                        $product->id,
                        __convert_value_bd($request->quantidade[$i]) * $product->conversao_unitaria,
                        __convert_value_bd($request->valor_unitario[$i])
                    );
                }
                if ($request->vencimento) {
                    for ($i = 0; $i < sizeof($request->vencimento); $i++) {
                        ContaPagar::create([
                            'compra_id' => $compra->id,
                            'fornecedor_id' => $request->fornecedor_id,
                            'data_vencimento' => $request->vencimento[$i],
                            'data_pagamento' => $request->vencimento[$i],
                            'valor_integral' => __convert_value_bd($request->valor_parcela[$i]),
                            'valor_pago' => 0,
                            'status' => 0,
                            'referencia' => "Parcela $i+1 da Compra código $compra->id",
                            'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->first()->id,
                            'empresa_id' => $request->empresa_id
                        ]);
                    }
                }
                return true;
            });
            session()->flash("flash_sucesso", "Compra adicionada com sucesso!");
        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>' . $e->getLine();
            die;
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('compras.index');
    }

    // private function somaItens($request)
    // {
    //     $total = 0;
    //     for ($i = 0; $i < sizeof($request->produto_id); $i++) {
    //         $total += __convert_value_bd($request->subtotal_item[$i]);
    //     }
    //     return $total;
    // }
}
