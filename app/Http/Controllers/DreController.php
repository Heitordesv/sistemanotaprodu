<?php

namespace App\Http\Controllers;

use App\Models\ComissaoVenda;
use App\Models\ContaPagar;
use App\Models\Devolucao;
use App\Models\Dre;
use App\Models\DreCategoria;
use App\Models\Frete;
use App\Models\Funcionario;
use App\Models\LancamentoCategoria;
use App\Models\Produto;
use App\Models\Tributacao;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;
use Dompdf\Dompdf;


class DreController extends Controller
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
        return view('dre.index');
    }

    public function list(Request $request)
    {
        $data = Dre::where('empresa_id', $request->empresa_id)->get();
        if (!__valida_objeto($data)) {
            abort(403);
        }
        return view('dre.list', compact('data'));
    }

    public function store(Request $request)
    {
        $inicio = $request->inicio;
        $fim = $request->fim;

        if (!$inicio || !$fim) {
            session()->flash("flash_erro", "Informe a data inícial e final!");
            return redirect()->back();
        }
        try {
            $request->merge([
                'percentual_imposto' => $request->percentual_imposto ? __convert_value_bd($request->percentual_imposto) : 0,
                'observacao' => $request->observacao ?? ''
            ]);
            $dre = Dre::create($request->all());
            $dre->criaCategoriasPreDefinidas();
            $this->iniciaDre($dre, $inicio, $fim);
            session()->flash('flash_sucesso', 'Dre criada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('dre.index');
    }

    public function iniciaDre($dre, $inicio, $fim)
    {
        $somaCustos = 0;
        foreach ($dre->categorias as $key => $c) {
            if ($key == 0) {
                //Venda NFE
                $vendas = $this->getVendasPeriodo($this->parseDate($inicio), $this->parseDate($fim, true));
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'Faturamento bruto vendas',
                    'valor' => $vendas->soma ?? 0,
                    'percentual' => 0
                ];
                LancamentoCategoria::create($dataLancamento);
                // PDV
                $vendas = $this->getVendasPdvPeriodo($this->parseDate($inicio), $this->parseDate($fim, true));
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'Faturamento bruto vendas PDV',
                    'valor' => $vendas->soma ?? 0,
                    'percentual' => 0
                ];
                LancamentoCategoria::create($dataLancamento);
                // Outros
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'Faturamento outros',
                    'valor' => 0,
                    'percentual' => 0
                ];
                LancamentoCategoria::create($dataLancamento);
            }
            if ($key == 1) {
                //Devoluções
                $devolucoes = $this->getDevolucoes($this->parseDate($inicio), $this->parseDate($fim, true));
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'Devoluções',
                    'valor' => $devolucoes->soma ?? 0,
                    'percentual' => 0
                ];
                LancamentoCategoria::create($dataLancamento);
                //Abatimentos
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'Abatimentos/Descontos',
                    'valor' => 0,
                    'percentual' => 0
                ];
                LancamentoCategoria::create($dataLancamento);
                //Imposto
                $calculaImposto = $this->calculaImposto($this->parseDate($inicio), $this->parseDate($fim, true), $dre->percentual_imposto);
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'Impostos',
                    'valor' => $calculaImposto,
                    'percentual' => 0
                ];
                LancamentoCategoria::create($dataLancamento);
            }
            if ($key == 2) {
                //Faturamento liquido
                $faturamentoLiquido = $this->calculoFaturamentoLiquido($dre);
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'Faturamento Líquido',
                    'valor' => $faturamentoLiquido,
                    'percentual' => 100
                ];
                LancamentoCategoria::create($dataLancamento);
                $valorLiquido = $faturamentoLiquido;
            }
            if ($key == 3) {
                //Custos de Produção Variáveis
                // $compras = $this->getCompras($this->parseDate($inicio), $this->parseDate($fim, true));
                $cmv = $this->getCMVCMP(
                    $this->parseDate($inicio),
                    $this->parseDate($fim, true)
                );
                // echo "<pre>";
                // print_r($cmv);
                // echo "</pre>";
                // die;
                $somaCustos += $cmv;
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'CMV/CMP',
                    'valor' => $cmv,
                    'percentual' => 0
                ];
                LancamentoCategoria::create($dataLancamento);
                $fretes = $this->getFretes($this->parseDate($inicio), $this->parseDate($fim, true));
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'Despesas com Transportes',
                    'valor' => $fretes->soma ?? 0,
                    'percentual' => 0
                ];
                LancamentoCategoria::create($dataLancamento);
                $somaCustos += $fretes->soma ?? 0;
                $comissoes = $this->getComissaoVendas($this->parseDate($inicio), $this->parseDate($fim, true));
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'Comissões Vendas',
                    'valor' => $comissoes->soma ?? 0,
                    'percentual' => 0
                ];
                LancamentoCategoria::create($dataLancamento);
                $somaCustos += $comissoes->soma ?? 0;
            }
            if ($key == 4) {
                //Custos Fixos e Despesas
                $salarios = $this->getSalarios($this->parseDate($inicio), $this->parseDate($fim, true));
                $dataLancamento = [
                    'categoria_id' => $c->id,
                    'nome' => 'Sálario Funcionários',
                    'valor' => $salarios->soma ?? 0,
                    'percentual' => 0
                ];
                LancamentoCategoria::create($dataLancamento);
                $somaCustos += $salarios->soma ?? 0;
                $contas = $this->getContasPagar($this->parseDate($inicio), $this->parseDate($fim, true));
                foreach ($contas as $conta) {
                    $dataLancamento = [
                        'categoria_id' => $c->id,
                        'nome' => $conta->nome,
                        'valor' => $conta->soma ?? 0,
                        'percentual' => 0
                    ];
                    LancamentoCategoria::create($dataLancamento);
                    $somaCustos += $conta->soma ?? 0;
                }
            }
        }
        $dre->lucro_prejuizo = $valorLiquido - $somaCustos;
        // echo $dre->lucro_prejuizo;
        // die;
        $dre->save();
        $this->recalcularPercentual($dre->id);
    }

    private function getVendasPeriodo($inicio, $fim)
    {
        $vendas = Venda::selectRaw('sum(valor_total) as soma')
            ->whereBetween('created_at', [
                $inicio,
                $fim
            ])
            ->where('empresa_id', $this->empresa_id)
            ->first();
        return $vendas;
    }

    private function getVendasPdvPeriodo($inicio, $fim)
    {
        $vendas = VendaCaixa::selectRaw('sum(valor_total) as soma')
            ->whereBetween('created_at', [
                $inicio,
                $fim
            ])
            ->where('empresa_id', $this->empresa_id)
            ->first();
        return $vendas;
    }

    private function getDevolucoes($inicio, $fim)
    {
        $devolucoes = Devolucao::selectRaw('sum(valor_devolvido) as soma')
            ->whereBetween('created_at', [
                $inicio,
                $fim
            ])
            ->where('empresa_id', $this->empresa_id)
            ->where('tipo', 0)
            ->first();
        return $devolucoes;
    }

    private function calculaImposto($inicio, $fim, $percImposto)
    {
        $tributacao = Tributacao::where('empresa_id', $this->empresa_id)
            ->first();
        if ($tributacao->regime != 1) {
            $vendas = Venda::selectRaw('sum(vendas.valor_total) as soma')
                ->whereBetween('created_at', [
                    $inicio,
                    $fim
                ])
                ->where('empresa_id', $this->empresa_id)
                ->where('vendas.estado_emissao', '!=', 'cancelado')
                ->first();

            $vendasCaixa = VendaCaixa::selectRaw('sum(venda_caixas.valor_total) as soma')
                ->whereBetween('created_at', [
                    $inicio,
                    $fim
                ])
                ->where('venda_caixas.empresa_id', $this->empresa_id)
                ->where('venda_caixas.estado_emissao', '!=', 'cancelado')
                ->first();

            $soma = $vendasCaixa->soma + $vendas->soma;

            $p = $soma * __convert_value_bd($percImposto / 100);

            return $p;
        } else {
            $vendas = Venda::select(\DB::raw('sum(vendas.valor_total) as soma'))
                ->when(!empty($inicio), function ($query) use ($inicio) {
                    return $query->whereDate('created_at', '>=', $inicio);
                })
                ->when(!empty($fim), function ($query) use ($fim) {
                    return $query->whereDate('created_at', '<=', $fim);
                })
                ->where('vendas.empresa_id', $this->empresa_id)
                ->where('vendas.estado_emissao', '!=', 'cancelado')
                ->limit($total_resultados ?? 1000000)
                ->get();
            $impostoNFe = $this->extrairImposto($vendas, 'xml_nfe');
            $vendasCaixa = VendaCaixa::select(\DB::raw('sum(venda_caixas.valor_total) as soma'))
                ->when(!empty($inicio), function ($query) use ($inicio) {
                    return $query->whereDate('created_at', '>=', $inicio);
                })
                ->when(!empty($end_date), function ($query) use ($fim) {
                    return $query->whereDate('created_at', '<=', $fim);
                })
                ->where('venda_caixas.empresa_id', $this->empresa_id)
                ->where('venda_caixas.estado', '!=', 'cancelado')
                ->limit($total_resultados ?? 1000000)
                ->get();
            $impostoNFCe = $this->extrairImposto($vendasCaixa, 'xml_nfce');
            return $impostoNFe + $impostoNFCe;
        }
    }

    private function uneArrayVendas($vendas, $vendasCaixa)
    {
        $adicionados = [];
        $arr = [];
        foreach ($vendas as $v) {
            $temp = [
                // 'data' => $v->data,
                'total' => $v->total,
                // 'itens' => $v->itens
            ];
            array_push($adicionados, $v->data);
            array_push($arr, $temp);
        }
        foreach ($vendasCaixa as $v) {
            if (!in_array($v->data, $adicionados)) {
                $temp = [
                    //'data' => $v->data,
                    'total' => $v->total,
                    // 'itens' => $v->itens
                ];
                array_push($adicionados, $v->data);
                array_push($arr, $temp);
            } else {
                for ($aux = 0; $aux < count($arr); $aux++) {
                    if ($arr[$aux]['total'] += $v->total) {

                        // $arr[$aux]['itens'] += $i->itens;
                    }
                }
            }
        }
        return $arr;
    }

    private function extrairImposto($vendas, $path)
    {
        $somaIcms = 0;
        $somaPis = 0;
        $somaCofins = 0;
        foreach ($vendas as $v) {
            $file = public_path($path) . "/" . $v->chave . ".xml";
            $xml = simplexml_load_file($file);
            $vIcms = $xml->NFe->infNFe->total->ICMSTot->vICMS;
            $vPis = $xml->NFe->infNFe->total->ICMSTot->vPIS;
            $vCofins = $xml->NFe->infNFe->total->ICMSTot->vCOFINS;
            $somaIcms += $vIcms;
            $somaPis += $vPis;
            $somaCofins += $vCofins;
        }
        return $somaIcms + $somaPis + $somaCofins;
    }

    private function calculoFaturamentoLiquido($dre)
    {
        $somaBruto = 0;
        $somaDeducoes = 0;
        foreach ($dre->categorias as $key => $c) {
            if ($key == 0) {
                foreach ($c->lancamentos as $l) {
                    $somaBruto += $l->valor;
                }
            }
            if ($key == 1) {
                foreach ($c->lancamentos as $l) {
                    $somaDeducoes += $l->valor;
                }
            }
        }
        return $somaBruto - $somaDeducoes;
    }

    private function getComissaoVendas($inicio, $fim)
    {
        $comissoes = ComissaoVenda::selectRaw('sum(valor) as soma')
            ->whereBetween('created_at', [
                $inicio,
                $fim
            ])
            ->where('empresa_id', $this->empresa_id)
            ->first();
        return $comissoes;
    }

    private function getFretes($inicio, $fim)
    {
        $fretes = Frete::selectRaw('sum(fretes.valor) as soma')
            ->join('vendas', 'vendas.frete_id', '=', 'fretes.id')
            ->whereBetween('fretes.created_at', [
                $inicio,
                $fim
            ])
            ->where('vendas.empresa_id', $this->empresa_id)
            ->first();
        return $fretes;
    }

    private function getSalarios($inicio, $fim)
    {
        $funcionarios = Funcionario::selectRaw('sum(salario) as soma')
            ->whereBetween('created_at', [
                $inicio,
                $fim
            ])
            ->where('empresa_id', $this->empresa_id)
            ->first();
        return $funcionarios;
    }

    private function getContasPagar($inicio, $fim)
    {
        $contas = ContaPagar::selectRaw('categoria_contas.nome as nome, sum(conta_pagars.valor_integral) as soma')
            ->join('categoria_contas', 'categoria_contas.id', '=', 'conta_pagars.categoria_id')
            ->whereBetween('conta_pagars.data_vencimento', [
                $inicio,
                $fim
            ])
            ->where('conta_pagars.empresa_id', $this->empresa_id)
            ->where('categoria_contas.nome', '!=', 'Compras')
            ->where('categoria_contas.nome', '!=', 'Vendas')
            ->groupBy('categoria_contas.id')
            ->get();
        return $contas;
    }

    private function recalcularPercentual($dreId)
    {
        $dre = Dre::find($dreId);
        $liquido = 0;
        $somaDeducoes = 0;
        foreach ($dre->categorias as $key => $c) {
            $faturamento = $c->soma();
            if ($key > 0) {
                foreach ($c->lancamentos as $l) {
                    if ($faturamento == 0) {
                        $l->percentual = 0;
                    } else {
                        $percentual = number_format((($l->valor / $faturamento) * 100), 2);
                        $l->percentual = $percentual;
                    }
                    $l->save();
                }
            }
            if ($key == 2) {
                $liquido = $l->valor;
                echo $liquido;
            }
            if ($key > 2) {
                foreach ($c->lancamentos as $l) {
                    $somaDeducoes += $l->valor;
                }
            }
        }
        $lucro_prejuizo = $liquido - $somaDeducoes;
        $dre->lucro_prejuizo = $lucro_prejuizo;
        $dre->save();
    }

    private function parseDate($date, $plusDay = false)
    {
        if ($plusDay == false)
            return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
        else
            return date('Y-m-d', strtotime("+1 day", strtotime(str_replace("/", "-", $date))));
    }

    private function getCMVCMP($inicio, $fim)
    {
        $vendas = Venda::whereBetween('created_at', [
            $inicio,
            $fim
        ])
            ->where('empresa_id', $this->empresa_id)
            ->get();
        $vendasCaixa = VendaCaixa::whereBetween('created_at', [
            $inicio,
            $fim
        ])
            ->where('empresa_id', $this->empresa_id)
            ->get();
        $custo = 0;
        foreach ($vendas as $v) {
            foreach ($v->itens as $i) {
                $produto = Produto::find($i->produto_id);
                $custo += $produto->valor_compra * $i->quantidade;
            }
        }
        foreach ($vendasCaixa as $v) {
            foreach ($v->itens as $i) {
                $produto = Produto::find($i->produto_id);
                $custo += $produto->valor_compra * $i->quantidade;
            }
        }
        return $custo;
    }

    public function show(Request $request, $id)
    {
        $item = Dre::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $tributacao = Tributacao::where('empresa_id', $request->empresa_id)
            ->first();
        return view('dre.show', compact('item', 'tributacao'));
    }

    public function destroy($id)
    {
        $item = Dre::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function imprimir($id)
    {
        $dre = Dre::findOrFail($id);
        if (!__valida_objeto($dre)) {
            abort(403);
        }
        if (valida_objeto($dre)) {
            $tributacao = Tributacao::where('empresa_id', $this->empresa_id)
                ->first();
            $p = view('dre.imprimir', compact('dre', 'tributacao'));
            // return $p;
            $domPdf = new Dompdf(["enable_remote" => true]);
            $domPdf->loadHtml($p);
            // $pdf = ob_get_clean();
            $domPdf->setPaper("A4");
            $domPdf->render();
            $domPdf->stream("DRE.pdf");
        } else {
            return redirect('/403');
        }
    }

    public function novolancamento(Request $request)
    {
        $categoriaId = $request->categoria_id;
        $valor = __convert_value_bd($request->valor);
        $nome = $request->nome;
        $dataLancamento = [
            'categoria_id' => $categoriaId,
            'nome' => $nome,
            'valor' => $valor,
            'percentual' => 0
        ];
        LancamentoCategoria::create($dataLancamento);
        $categoria = DreCategoria::find($categoriaId);
        
        $this->recalcularPercentual($categoria->dre_id);
        session()->flash("flash_sucesso", "Lançamento cadastrado com sucesso!");
        return redirect()->back();
    }

    public function updatelancamento(Request $request)
    {
        $lancamentoId = $request->lancamento_id;
        $lancamento = LancamentoCategoria::find($lancamentoId);
        $lancamento->valor = __convert_value_bd($request->valor);
        $lancamento->nome = $request->nome;
        $lancamento->save();
        
        $this->recalcularPercentual($lancamento->categoria->dre_id);
        session()->flash("flash_sucesso", "Lançamento alterado com sucesso!");
        return redirect()->back();
    }

    public function deleteLancamento($id)
    {
        try {
            $lancamento = LancamentoCategoria::find($id);
            $id = $lancamento->categoria->dre_id;
            $lancamento->delete();

            $this->recalcularPercentual($id);
            session()->flash("flash_sucesso", "Lançamento removido com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Erro ao remover lançamento!");
        }
        return redirect()->back();
    }
}
