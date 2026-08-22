<?php

namespace App\Http\Controllers;

use App\Models\Cotacao;
use App\Models\Fornecedor;
use App\Models\ItemCotacao;
use App\Models\Produto;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CotacaoController extends Controller
{
    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $fornecedor_id = $request->get('fornecedor_id');
        $data = Cotacao::where('empresa_id', $request->empresa_id)
            ->when(!empty($start_date), function ($query) use ($start_date) {
                return $query->whereDate('created_at', '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date) {
                return $query->whereDate('created_at', '<=', $end_date);
            })
            ->when(!empty($fornecedor_id), function ($query) use ($fornecedor_id) {
                return $query->where('fornecedor_id', $fornecedor_id);
            })
            ->orderBy('data_registro', 'desc')
            ->paginate(env("PAGINACAO"));
        return view('cotacao.index', compact('data'));
    }

    public function create()
    {
        return view('cotacao.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $result = DB::transaction(function () use ($request) {
                $request->merge([
                    'forma_pagamento' => $request->forma_pagamento ?? 0,
                    'responsavel' => $request->responsavel ?? 0,
                    'link' => $this->generateRandomString(20),
                    'observacao' => $request->observacao ?? '',
                    'resposta' => $request->resposta ?? 0,
                    'ativa' => 0,
                    'valor' => $request->valor ?? 0,
                    'desconto' => $request->desconto ?? 0,
                    'escolhida' => $request->escolhida ?? 0,
                    'referencia' => $request->referencia ?? 0
                ]);
                $cotacao = Cotacao::create($request->all());
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    ItemCotacao::create([
                        'cotacao_id' => $cotacao->id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor' => __convert_value_bd($product->valor_compra[$i])
                    ]);
                }
                return true;
            });
            session()->flash("flash_sucesso", "Cadastro com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('cotacao.index');
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function show(Request $request, $id)
    {
        $item = Cotacao::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('cotacao.show', compact('item'));
    }

    public function destroy($id)
    {
        $item = Cotacao::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Apagado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    private function _validate(Request $request)
    {
        $rules = [
            'fornecedor_id' => 'required',
            'produto_id' => 'required',
            'quantidade' => 'required'
        ];
        $messages = [
            'fornecedor_id.required' => 'Campo Obrigatório',
            'produto_id.required' => 'Campo Obrigatório',
            'quantidade.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function referencia()
    {
        $cotacoes = Cotacao::select(\DB::raw('referencia'))
            ->where('referencia', '!=', '*')
            ->where('referencia', '!=', '')
            ->where('empresa_id', request()->empresa_id)
            ->groupBy('referencia')
            ->get();
        return view('cotacao.listaPorReferencia', compact('cotacoes'));
    }

    public function referenciaView($referencia)
    {
        $cotacoes = Cotacao::where('referencia', $referencia)
            ->where('valor', '>', 0)
            ->where('empresa_id', request()->empresa_id)
            ->get();
        if (count($cotacoes) > 0) {
            $itens = $this->preparaItens($cotacoes);
            $fornecedores = [];
            foreach ($itens as $i) {
                if (!$this->estaNoArray($fornecedores, $i)) {
                    array_push(
                        $fornecedores,
                        [
                            'fornecedor' => $i['fornecedor'],
                            'qtd' => 1
                        ]
                    );
                } else {
                    for ($aux = 0; $aux < count($fornecedores); $aux++) {
                        if ($fornecedores[$aux]['fornecedor'] == $i['fornecedor']) $fornecedores[$aux]['qtd'] += 1;
                    }
                }
            }
            $melhorResultado = $cotacoes[0];
            foreach ($cotacoes as $c) {
                if ($c->valor < $melhorResultado->valor) $melhorResultado = $c;
            }
            return view('cotacao.ver_resultados', compact('itens', 'melhorResultado', 'fornecedores', 'cotacoes'));
        } else {
            session()->flash('flash_erro', 'Referência sem nehuma resposta!');
            return redirect('/cotacao/listaPorReferencia');
        }
    }


    private function estaNoArray($arr, $elem)
    {
        foreach ($arr as $a) {
            if ($a['fornecedor'] == $elem['fornecedor']) return true;
        }
        return false;
    }

    private function preparaItens($cotacoes)
    {
        if (count($cotacoes) > 0) {
            // echo $cotacoes;
            $melhoresItens = $this->itemInicial($cotacoes[0]);
            // print_r($itemInicial);
            foreach ($cotacoes as $c) {
                foreach ($c->itens as $i) {
                    for ($aux = 0; $aux < count($melhoresItens); $aux++) {
                        if ($melhoresItens[$aux]['item'] == $i->produto->nome) {
                            $valorTemp = $i->valor * $i->quantidade;
                            if ($valorTemp < $melhoresItens[$aux]['valor_total']) {
                                $melhoresItens[$aux]['valor_total'] = $valorTemp;
                                $melhoresItens[$aux]['valor_unitario'] = $i->valor;
                                $melhoresItens[$aux]['fornecedor'] = $c->fornecedor->razao_social;
                            }
                        }
                    }
                }
            }
            return $melhoresItens;
        }
    }

    private function itemInicial($cotacao)
    {
        $itens = [];
        foreach ($cotacao->itens as $i) {
            $temp = [
                'item' => $i->produto->nome,
                'valor_unitario' => $i->valor,
                'quantidade' => $i->quantidade,
                'valor_total' => $i->valor * $i->quantidade,
                'fornecedor' => $cotacao->fornecedor->razao_social
            ];
            array_push($itens, $temp);
        }
        return $itens;
    }

    public function sendMail($id)
    {
        $cotacao = Cotacao::where('id', $id)
            ->first();
        if (valida_objeto($cotacao)) {
            $pathUrl = env('PATH_URL');
            try {
                Mail::send(
                    'mail.cotacao',
                    ['link' => "$pathUrl/response/$cotacao->link"],
                    function ($message) use ($cotacao) {
                        $nomeEmpresa = env('MAIL_NAME');
                        $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                        $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                        $emailEnvio = env('MAIL_USERNAME');
                        $message->to($cotacao->fornecedor->email)->subject('Cotação de Serviço/Compra');
                        $message->from($emailEnvio, $nomeEmpresa);
                        $message->subject('Envio de Cotação ' . $cotacao->id);
                    }
                );
                session()->flash('flash_sucesso', 'EMAIL ENVIADO');
                return redirect()->route('cotacao.index');
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        } else {
            return redirect('/403');
        }
    }

    public function alterarStatus($id, $status)
    {
        $cotacao = Cotacao::where('id', $id)
            ->first();
        if (valida_objeto($cotacao)) {
            $cotacao->ativa = $status;
            $result = $cotacao->save();
            if ($result) {
                session()->flash('flash_sucesso', 'Cotação ' .
                    ($status == 1 ? 'Ativada!' : 'Desativada!'));
            } else {
                session()->flash('flash_erro', 'Erro');
            }
            return redirect("/cotacao");
        } else {
            return redirect('/403');
        }
    }

    public function view($id)
    {
        $cotacao = Cotacao::where('id', $id)
            ->first();
        if (valida_objeto($cotacao)) {
            return view('cotacao.view', compact('cotacao'));
        } else {
            return redirect('/403');
        }
    }


    public function deleteItem($id)
    {
        $item = ItemCotacao::where('id', $id)
            ->first();
        if (valida_objeto($item->cotacao)) {
            $cotacaoId = $item->cotacao->id;
            if ($item->delete()) {
                session()->flash('flash_sucesso', 'Item removido!');
            } else {
                session()->flash('flash_erro', 'Erro ao remover item');
            }
            return redirect()->back();
        } else {
            return redirect('/403');
        }
    }

    public function clonar($id)
    {
        $cotacao = Cotacao::where('id', $id)
            ->first();
        if (valida_objeto($cotacao)) {
            $fornecedores = Fornecedor::where('empresa_id', request()->empresa_id)
                ->orderBy('razao_social')
                ->get();
            return view('cotacao.clone')
                ->with('cotacaoJs', true)
                ->with('cloneJs', true)
                ->with('cotacao', $cotacao)
                ->with('fornecedores', $fornecedores);
        } else {
            return redirect('/403');
        }
    }

    public function imprimirMelhorResultado(Request $request)
    {
        $fornecedor = $request->fornecedor;
        $referencia = $request->referencia;
        $cotacoes = Cotacao::where('referencia', $referencia)
            ->where('valor', '>', 0)
            ->where('empresa_id', request()->empresa_id)
            ->get();
        $temp = [];
        if (count($cotacoes) > 0) {
            $itens = $this->preparaItens($cotacoes);
            foreach ($itens as $i) {
                if ($i['fornecedor'] == $fornecedor) {
                    array_push($temp, $i);
                }
            }
        }
        $fornecedorByNome = Fornecedor::where('razao_social', $fornecedor)
            ->where('empresa_id', request()->empresa_id)
            ->first();
        $p = view('cotacao.relatorio')
            ->with('cotacao', $cotacoes[0])
            ->with('fornecedor', $fornecedorByNome)
            ->with('itens', $temp);
        // return $p;
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Refencia_{$cotacoes[0]->referencia}_Fornecedor_{$fornecedorByNome->razao_social}.pdf");
    }

    public function clonarSave(Request $request)
    {
        $data = $request->data;
        $cotacao = Cotacao
            ::where('id', $data['cotacao'])
            ->first();
        $tst = 0;
        foreach ($data['fornecedores'] as $f) {
            $forn = Fornecedor::where('id', $f)
                ->first();
            $result = Cotacao::create([
                'forma_pagamento' => '*',
                'responsavel' => '',
                'valor' => 0,
                'desconto' => 0,
                'fornecedor_id' => $forn->id,
                'link' => $this->generateRandomString(20),
                'referencia' => $cotacao->referencia,
                'observacao' => $cotacao->observacao,
                'resposta' => false,
                'ativa' => true,
                'escolhida' => false,
                'empresa_id' => request()->empresa_id
            ]);

            foreach ($cotacao->itens as $i) {
                $itemResult = ItemCotacao::create([
                    'cotacao_id' => $result->id,
                    'produto_id' => $i->produto->id,
                    'valor' => 0,
                    'quantidade' => $i->quantidade
                ]);
            }
        }
        echo json_encode($cotacao);
    }


    public function escolher($id)
    {
        $cotacao = Cotacao::find($id);
        if (valida_objeto($cotacao)) {
            $cotacao->escolhida = true;
            $cotacao->save();
            session()->flash('flash_sucesso', 'Cotação escolhida para referencia ' . $cotacao->referencia . '!');
            return redirect('/cotacao/listaPorReferencia');
        } else {
            return redirect('/403');
        }
    }
}
