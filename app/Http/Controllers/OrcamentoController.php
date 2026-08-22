<?php

namespace App\Http\Controllers;

use App\Helpers\StockMove;
use App\Models\CategoriaConta;
use App\Models\ConfigNota;
use App\Models\ContaReceber;
use App\Models\FaturaOrcamento;
use App\Models\Frete;
use App\Models\ItemOrcamento;
use App\Models\ItemVenda;
use App\Models\NaturezaOperacao;
use App\Models\Orcamento;
use App\Models\Produto;
use App\Models\Venda;
use App\Services\NFService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Foreach_;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use NFePHP\DA\NFe\Danfe;

class OrcamentoController extends Controller
{
    public function __construct()
    {
        if (!is_dir(public_path('orcamento'))) {
            mkdir(public_path('orcamento'), 0777, true);
        }
    }

    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $type_search = $request->get('type_search');
        $estado = $request->get('estado');
        $date = date('d/m/Y');
        $data = Orcamento::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('cliente_id', $cliente_id);
        })
        ->when($estado != "", function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(env("PAGINACAO"));
        return view('orcamento.index', compact('data', 'date'));
    }

    public function show($id)
    {
        $data = Orcamento::findOrFail($id);
        if (!__valida_objeto($data)) {
            abort(403);
        }
        $naturezaOperacao = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        $d1 = strtotime(date('Y-m-d'));
        $d2 = strtotime($data->validade);
        $distancia = $d2 - $d1;
        $diasParaVencimento = $distancia / 86400;
        $simulacao = $this->simulacaoPagamento($data->valor_total);
        $somaItens = 0;
        foreach ($data->itens as $i) {
            $somaItens += $i->quantidade * $i->valor;
        }
        // $data->valor_total = $somaItens;
        // $data->save();
        // $this->deleteParcelas($data);
        return view('orcamento.show', compact(
            'data',
            'naturezaOperacao',
            'diasParaVencimento',
            'simulacao',
            'somaItens'
        ));
    }

    private function simulacaoPagamento($total)
    {
        $soma = 0;
        $tempArr = [];
        $valorP = number_format($total / 12, 2);
        for ($i = 1; $i <= 12; $i++) {
            $t = [
                'parcelas' => $i,
                'valor' => number_format($total / $i, 2)
            ];
            array_push($tempArr, $t);
        }
        return $tempArr;
    }

    public function addPagamentos(Request $request)
    {
        $id = $request->orcamento_id;
        $valor = __convert_value_bd($request->valor);
        $orcamento = Orcamento::findorFail($id);
        try {
            $vencimento = \Carbon\Carbon::parse(str_replace("/", "-", $request->data))->format('Y-m-d');
            $strtotimeData = strtotime($vencimento);
            $strtotimeHoje = strtotime(date('Y-m-d'));
            $dif = $strtotimeData - $strtotimeHoje;
            $vencimento = \Carbon\Carbon::parse(str_replace("/", "-", $request->data))->format('Y-m-d');
            FaturaOrcamento::create([
                'valor' => $valor,
                'vencimento' => $vencimento,
                'orcamento_id' => $id,
                'empresa_id' => $request->empresa_id
            ]);
            $this->atualizarTotal($orcamento);
            session()->flash("flash_sucesso", "Parcela adicionada");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function gerarPagamentos(Request $request)
    {
        try {
            $qtdParcelas = $request->qtd_parcelas;
            $intervalo = $request->intervalo;
            $id = $request->orcamento_id;
            $orcamento = Orcamento::findorFail($id);
            $total = $orcamento->valor_total;
            $soma = 0;
            foreach ($orcamento->duplicatas as $dp) {
                $dp->delete();
            }
            $vp = __convert_value_bd($total / $qtdParcelas, 2);
            $data = date('Y-m-d');
            for ($i = 0; $i < $qtdParcelas; $i++) {
                $valor = 0;
                if ($i < $qtdParcelas - 1) {
                    $valor = $vp;
                    $soma += $vp;
                } else {
                    $valor = __convert_value_bd($total - $soma, 2);
                }
                $data = $this->calculaData($data, $intervalo);
                FaturaOrcamento::create([
                    'valor' => $valor,
                    'vencimento' => $data,
                    'orcamento_id' => $id,
                    'empresa_id' => $request->empresa_id
                ]);
            }
            session()->flash("flash_sucesso", "Parcela gerado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }


    private function calculaData($data, $intervalo)
    {
        return date('Y-m-d', strtotime("+$intervalo day", strtotime(str_replace("/", "-", $data))));
    }

    public function destroyParcela($id)
    {
        $item = FaturaOrcamento::findOrfail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Parcela removida");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    private function atualizarTotal($orcamento)
    {
        $orcamento = Orcamento::findOrFail($orcamento->id);
        $soma = 0;
        foreach ($orcamento->itens as $i) {
            $soma += $i->quantidade * $i->valor;
        }
        $orcamento->valor_total = $soma;
        $orcamento->save();
    }

    public function addItem(Request $request)
    {
        try {
            $orcamento = Orcamento::findOrFail($request->orcamento_id);
            $product = Produto::findOrFail($request->produto_id);
            ItemOrcamento::create([
                'orcamento_id' => $orcamento->id,
                'produto_id' => (int)$request->produto_id,
                'quantidade' => __convert_value_bd($request->quantidade),
                'valor' => __convert_value_bd($request->valor_unitario),
                'altura' => $request->altura ?? 0,
                'largura' => $request->largura ?? 0,
                'profundidade' => $request->profundidade ?? 0,
                'acrescimo_perca' => $request->acrescimo_perca ?? 0,
                'esquerda' => $request->esquerda ?? 0,
                'direita' => $request->direita ?? 0,
                'inferior' => $request->inferior ?? 0,
                'superior' => $request->superior ?? 0
            ]);
            session()->flash("flash_sucesso", "Item adicionado!");
            $this->atualizarTotal($orcamento);
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('orcamentoVenda.show', $orcamento);
    }

    // Destroy Item
    public function destroyItem(Request $request, $id)
    {
        $item = ItemOrcamento::findOrfail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Produto removido");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->back();
    }

    public function imprimir($id)
    {
        $item = Orcamento::find($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $config = ConfigNota::where('empresa_id', $item->empresa_id)
        ->first();
        $p = view('orcamento.print', compact('config', 'item'));
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Pedido de Venda $id.pdf", array("Attachment" => false));
    }

    public function reprovar($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        if (valida_objeto($orcamento)) {
            $orcamento->estado = 'REPROVADO';
            $orcamento->save();
            session()->flash("flash_erro", "Orçamento reprovado!");
            return redirect()->back();
        } else {
            return redirect('/403');
        }
    }

    public function enviarEmail(Request $request)
    {
        $email = $request->email;
        $id = $request->id;
        $item = Orcamento::findOrFail($id);
        if (valida_objeto($item)) {
            $config = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();
            if ($email == '') {
                session()->flash("flash_sucesso", "Informe um email!");
                return redirect()->back();
            }
            $p = view('orcamento.print', compact('config', 'item'));
            // return $p;
            $domPdf = new Dompdf(["enable_remote" => true]);
            $domPdf->loadHtml($p);
            $pdf = ob_get_clean();
            $domPdf->setPaper("A4");
            $domPdf->render();
            file_put_contents(public_path('orcamento/') . 'ORCAMENTO_' . $item->id . '.pdf', $domPdf->output());
            $value = session('user_logged');
            if ($config->usar_email_proprio) {
                $send = $this->enviaEmailPHPMailer($item, $email, $config);
                if (!isset($send['erro'])) {
                    session()->flash("flash_sucesso", "Email enviado!");
                } else {
                    session()->flash("flash_erro", "Erro ao enviar email: " . $send['erro']);
                }
                return redirect()->back();
            } else {
                try {
                    Mail::send('mail.orcamento_send', [
                        'emissao' => $item->created_at,
                        'valor' => $item->valor_total, 'usuario' => $value['nome'], 'config' => $config
                    ], function ($m) use ($item, $email, $pdf) {
                        $public = env('SERVIDOR_WEB') ? 'public/' : '';
                        $nomeEmpresa = env('MAIL_NAME');
                        $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                        $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                        $emailEnvio = env('MAIL_USERNAME');
                        $m->from($emailEnvio, $nomeEmpresa);
                        $m->subject('Envio de Oçamento ' . $item->id);
                        $m->attach($public . 'orcamento/ORCAMENTO_' . $item->id . '.pdf');
                        $m->to($email);
                        return response()->json("ok", 200);
                    });
                    if (isset($request->redirect)) {
                        session()->flash("flash_sucesso", "Email enviado!");
                        return redirect()->back();
                    }
                } catch (\Exception $e) {
                    return response()->json($e->getMessage(), 401);
                }
            }
        } else {
            return redirect('/403');
        }
    }

    public function store(Request $request)
    {

        $this->_validate($request);
        $orcamento = Orcamento::findOrFail($request->orcamento_id);
        try {

            $request->merge([
                'valor' => $request->valor ?? 0,
                'numeracaoVolumes' => __convert_value_bd($request->numeracaoVolumes),
                'placa' => $request->placa ?? '',
                'peso_liquido' => $request->peso_liquido ?? 0,
                'peso_bruto' => $request->peso_bruto ?? 0,
                'especie' => $request->especie ?? '',
                'qtdVolumes' => $request->qtdVolumes ?? 0
            ]);
            $result = DB::transaction(function () use ($request, $orcamento) {

                $venda = Venda::create([
                    'cliente_id' => $orcamento->cliente_id,
                    'empresa_id' => $orcamento->empresa_id,
                    'usuario_id' => get_id_user(),
                    'natureza_id' => $request->natureza_id,
                    'valor_total' => $orcamento->valor_total,
                    'desconto' => $orcamento->desconto,
                    'acrescimo' => $orcamento->acrescimo,
                    'forma_pagamento' => $orcamento->forma_pagamento,
                    'tipo_pagamento' => $orcamento->tipo_pagamento,
                    'observacao' => $orcamento->observacao,
                    'estado_emissao' => 'novo',
                    'sequencia_cce' => 0,
                    'chave' => '',
                    'path_xml' => '',
                ]);
                $stockMove = new StockMove();
                foreach ($orcamento->itens as $product) {
                    ItemVenda::create([
                        'venda_id' => $venda->id,
                        'produto_id' => $product->produto_id,
                        'quantidade' => $product->quantidade,
                        'valor' => $product->valor,
                        'valor_custo' => $product->produto->valor_compra
                    ]);
                    $stockMove->downStock(
                        $product->id,
                        __convert_value_bd($request->quantidade),
                    );
                }
                foreach ($orcamento->duplicatas as $key => $fatura) {
                    ContaReceber::create([
                        'venda_id' => $venda->id,
                        'cliente_id' => $fatura->cliente_id,
                        'data_vencimento' => $fatura->vencimento,
                        'data_recebimento' => $fatura->vencimento,
                        'valor_integral' => $fatura->valor,
                        'valor_recebido' => 0,
                        'status' => 0,
                        'referencia' => "Parcela $key+1 da Compra código $venda->id",
                        'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->first()->id,
                        'empresa_id' => $fatura->empresa_id,
                        'juros' => 0,
                        'multa' => 0,
                        'venda_caixa_id' => null,
                        'observacao' => '',
                        'tipo_pagamento' => $orcamento->tipo_pagamento
                    ]);
                }
                if($request->tipo != 9){
                    Frete::create([
                        'frete_id' => $request->id,
                        'valor' => __convert_value_bd($request->valor),
                        'placa' => $request->placa,
                        'tipo' => $request->tipo,
                        'uf' => $request->uf,
                        'numeracaoVolumes' => $request->numeracaoVolumes,
                        'peso_liquido' => $request->peso_liquido,
                        'peso_bruto' => $request->peso_bruto,
                        'especie' => $request->especie,
                        'qtdVolumes' => $request->qtdVolumes
                    ]);
                }
                $orcamento->estado = 'APROVADO';
                $orcamento->save();
                return $orcamento;
            });
            session()->flash("flash_sucesso", "Venda gerada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->route('vendas.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'natureza_id' => 'required',
            // 'uf' => 'required',
        ];
        $messages = [
            'natureza_id.required' => 'Campo Obrigatório',
            'uf.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    private function somaItens($request)
    {
        $valor_total = 0;
        for ($i = 0; $i < sizeof($request->produto_id); $i++) {
            $valor_total += __convert_value_bd($request->subtotal_item[$i]);
        }
        return $valor_total;
    }

    public function rederizarDanfe($id)
    {
        $orcamento = Orcamento::find($id);
        if (valida_objeto($orcamento)) {
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
            $nfe_service = new NFService([
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
            $nfe = $nfe_service->simularOrcamento($orcamento);
            if (!isset($nfe['erros_xml'])) {
                $xml = $nfe['xml'];

                $public = env('SERVIDOR_WEB') ? 'public/' : '';

                if ($config->logo) {
                    $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents($public . 'logos/' . $config->logo));

                    $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(public_path('logos/') . $config->logo));
                } else {
                    $logo = null;
                }

                try {
                    $danfe = new Danfe($xml);
                    // $id = $danfe->monta();
                    $pdf = $danfe->render($logo);
                    header('Content-Type: application/pdf');
                    // echo $pdf;
                    return response($pdf)
                    ->header('Content-Type', 'application/pdf');
                } catch (InvalidArgumentException $e) {
                    echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
                }
            } else {
                foreach ($nfe['erros_xml'] as $e) {
                    echo $e;
                }
            }
        } else {
            return redirect('/403');
        }
    }

    public function relatorioItens($start_date, $end_date)
    {
        $dI = $start_date;
        $dF = $end_date;
        $orcamentos = Orcamento::whereDate('created_at', [
            $dI = (date('Y-m-d')),
            $dF = (date('Y-m-d'))
        ])
        ->where('estado', 'NOVO')
        ->get();
        $itens = [];
        foreach ($orcamentos as $o) {
            foreach ($o->itens as $i) {
                // echo $i;
                $temp = [
                    'codigo' => $i->produto->id,
                    'produto' => $i->produto->nome,
                    'quantidade' => $i->quantidade
                ];
                $dp = $this->itemNaoInserido($temp, $itens);

                if (!$dp) {
                    array_push($itens, $temp);
                } else {
                    for ($aux = 0; $aux < sizeof($itens); $aux++) {
                        if ($itens[$aux]['codigo'] == $temp['codigo']) {
                            $itens[$aux]['quantidade'] += $i->quantidade;
                        }
                    }
                }
            }
        }
        $p = view('relatorios/relatorio_compra_orcamento')
        ->with('data_inicial', $dI)
        ->with('data_final', $dF)
        ->with('itens', $itens);
        // return $p;
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Relatório de compra orçamento.pdf");
    }

    private function itemNaoInserido($item, $itens)
    {
        foreach ($itens as $i) {
            if ($i['codigo'] == $item['codigo']) return true;
        }
        return false;
    }
}
