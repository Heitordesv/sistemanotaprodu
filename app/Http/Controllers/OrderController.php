<?php

namespace App\Http\Controllers;

use App\Models\Acessor;
use App\Models\Cliente;
use App\Models\ConfigNota;
use App\Models\ApiBrasil;
use App\Models\Funcionario;
use App\Models\FuncionarioOs;
use App\Models\GrupoCliente;
use App\Models\OrdemServico;
use App\Models\Pais;
use App\Models\ProdutoOs;
use App\Models\RelatorioOs;
use App\Models\Servico;
use App\Models\ServicoOs;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use App\Jobs\EnviarMensagemWhatsAppOS;
use App\Jobs\EnviarWhatsAppJobOrdens;
use App\Models\AberturaCaixa;
use App\Models\Categoria;
use App\Models\Certificado;
use App\Models\Cidade;
use App\Models\ConfigCaixa;
use App\Models\CreditoVenda;
use App\Models\ListaPreco;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Tributacao;
use App\Models\VendaCaixa;
use App\Models\VendaCaixaPreVenda;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\returnSelf;

class OrderController extends Controller
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

    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $estado = $request->get('estado');
        $data = OrdemServico::where('empresa_id', $request->empresa_id)
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
            ->orderBy('created_at', 'Desc')
            ->with(['cliente', 'usuario', 'servicos.servico', 'produtos.produto'])
            ->paginate(env("PAGINACAO", 20));
        $clientes = Cliente::where('empresa_id', $request->empresa_id)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social']);
        return view('ordem_servico.index', compact('data', 'clientes'));
    }

    public function relatorioOs(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $estado = $request->get('estado');
        $data = OrdemServico::where('empresa_id', $request->empresa_id)
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
            ->orderBy('created_at', 'Desc')
            ->paginate(env("PAGINACAO"));
        return view('ordem_servico.relatorioOs', compact('data'));
    }


    public function create(Request $request)
    {
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        $paises = Pais::all();
        $grupos = GrupoCliente::where('empresa_id', request()->empresa_id)->get();
        $acessores = Acessor::where('empresa_id', request()->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        return view(
            'ordem_servico.create',
            compact(
                'clientes',
                'paises',
                'grupos',
                'acessores',
                'funcionarios'
            )
        );
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $ordem = OrdemServico::create([
                'descricao' => $request->input('descricao'),
                'usuario_id' => get_id_user(),
                'cliente_id' => $request->cliente_id,
                'empresa_id' => $request->empresa_id,
                'estado' => 'pendente'
            ]);
            session()->flash("flash_sucesso", "Ordem de serviço criada com sucesso!");
            return redirect()->route('ordemServico.completa', $ordem->id);
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Não foi possível criar a ordem de serviço.");
            __saveLogError($e, request()->empresa_id);
            return redirect()->back()->withInput();
        }
    }

    private function _validate(Request $request)
    {
        $rules = [
            'cliente_id' => 'required|integer|exists:clientes,id',
            'descricao' => 'required|string|min:3|max:5000'
        ];
        $messages = [
            'cliente_id.required' => 'Selecione um cliente.',
            'cliente_id.exists' => 'O cliente selecionado não existe.',
            'descricao.required' => 'Informe a descrição do serviço.',
            'descricao.min' => 'A descrição deve ter pelo menos 3 caracteres.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function completa($id)
    {
        $ordem = OrdemServico::findOrFail($id);
        if (!__valida_objeto($ordem)) {
            abort(403);
        }
        $funcionarios = Funcionario::where('empresa_id', $this->empresa_id)->get();
        $servicos = Servico::where('empresa_id', $this->empresa_id)->get();
        $relatorio = RelatorioOs::all();
        return view(
            'ordem_servico.ordem_completa',
            compact('funcionarios', 'ordem', 'servicos', 'relatorio')
        );
    }

    public function storeFuncionario(Request $request)
    {
        $id = $request->ordem_servico_id;
        $ordem = OrdemServico::findOrFail($id);
        $this->_validateFuncionario($request);
        try {
            FuncionarioOs::create([
                'usuario_id' => get_id_user(),
                'funcionario_id' => $request->funcionario_id,
                'ordem_servico_id' => $request->ordem_servico_id,
                'funcao' => $request->funcao
            ]);
            session()->flash("flash_sucesso", "Funcionario Adicionado a OS");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('ordemServico.completa', $ordem->id);
    }

    private function _validateFuncionario(Request $request)
    {
        $rules = [
            'funcao' => 'required',
        ];
        $messages = [
            'funcao' => 'Campo Obrigatório',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function deleteFuncionario(Request $request, $id)
    {
        $item = FuncionarioOs::findOrfail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Funcionário removido");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->back();
    }

    public function storeServico(Request $request)
    {

        try {

            ServicoOs::create([
                'servico_id' => $request->servico_id,
                'ordem_servico_id' => $request->ordem_servico_id,
                'quantidade' => __convert_value_bd($request->quantidade),
                'valor_unitario' => __convert_value_bd($request->valor),
                'sub_total' => (float)__convert_value_bd($request->valor) * (float)__convert_value_bd($request->quantidade),
            ]);

            $this->calcTotal($request->ordem_servico_id);

            session()->flash("flash_sucesso", "Serviço adicionado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function storeProduto(Request $request)
    {
        try {
            ProdutoOs::create([
                'produto_id' => $request->produto_id,
                'ordem_servico_id' => $request->ordem_servico_id,
                'quantidade' => __convert_value_bd($request->quantidade),
                'valor_unitario' => __convert_value_bd($request->valor_unitario),
                'sub_total' => __convert_value_bd($request->valor_unitario) * __convert_value_bd($request->quantidade),
            ]);
            $this->calcTotal($request->ordem_servico_id);

            session()->flash("flash_sucesso", "Produto adicionado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function deleteServico(Request $request, $id)
    {
        $item = ServicoOs::findOrfail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }

        try {
            $item->delete();
            $this->calcTotal($item->ordem_servico_id);

            session()->flash("flash_sucesso", "Serviço removido");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->back();
    }

    private function calcTotal($id)
    {
        $item = OrdemServico::findOrFail($id);
        $total = 0;
        foreach ($item->servicos as $s) {
            $total += $s->sub_total;
        }

        foreach ($item->produtos as $p) {
            $total += $p->sub_total;
        }
        $item->valor = $total;
        $item->save();
    }

    public function deleteProduto($id)
    {
        $item = ProdutoOs::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }

        try {
            $item->delete();
            $this->calcTotal($item->ordem_servico_id);

            session()->flash('flash_sucesso', 'Registro removido!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function addRelatorio($id)
    {
        $ordem = OrdemServico::where('id', $id)->first();
        return view('ordem_servico.add_relatorio', compact('ordem'));
    }

    public function storeRelatorio(Request $request)
    {
        $this->_validateRelatorio($request);
        $id = $request->ordem_servico_id;
        $ordem = OrdemServico::findOrFail($id);
        try {
            RelatorioOs::create([
                'usuario_id' => get_id_user(),
                'texto' => $request->texto,
                'ordem_servico_id' => $ordem->id
            ]);
            session()->flash("flash_sucesso", "Relatório Adicionado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->route('ordemServico.completa', $ordem->id);
    }

    private function _validateRelatorio(Request $request)
    {
        $rules = [
            'texto' => 'required|min:15',
        ];
        $messages = [
            'texto.required' => 'O campo texto é obrigatório.',
            'texto.min' => 'Minimo de 15 caracteres.',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function alterarStatusServico(Request $request, $id)
    {
        $servicoOs = ServicoOs::where('id', $id)->first();
        try {
            $servicoOs->status = !$servicoOs->status;
            $servicoOs->save();
            session()->flash("flash_sucesso", "Status Alterado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->back();
    }

    public function deleteRelatorio(Request $request, $id)
    {
        $relatorioOs = RelatorioOs::where('id', $id)->first();
        try {
            $relatorioOs->delete();
            session()->flash("flash_sucesso", "Relatório Deletado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->back();
    }

    public function editRelatorio($id)
    {
        $ordem = RelatorioOs::findOrFail($id);
        if (!__valida_objeto($ordem)) {
            abort(403);
        }
        return view('ordem_servico.edit_relatorio', compact('ordem'));
    }

    public function upRelatorio(Request $request)
    {
        $id = $request->ordem_servico_id;
        $ordem = RelatorioOs::findOrFail($id);
        try {
            $ordem->texto = $request->texto;
            $ordem->save();
            session()->flash("flash_sucesso", "Reletório Alterado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->route('ordemServico.completa', $ordem->id);
    }

    public function alterarEstado($id)
    {
        $ordem = OrdemServico::findOrFail($id);
        if (!__valida_objeto($ordem)) {
            abort(403);
        }
        return view('ordem_servico.alterar_estado', compact('ordem'));
    }

    public function alterarEstadoPost(Request $request)
    {
        $dados = $request->validate([
            'id' => 'required|integer|exists:ordem_servicos,id',
            'novo_estado' => 'required|in:pendente,Em Andamento,pronto,finalizado,reprovado',
            'novo_status_pagamento' => 'required|boolean',
            'descricao' => 'required|string|min:3|max:5000',
            'valor_entrada' => 'nullable|string',
            'valor_pago' => 'nullable|string',
            'desconto' => 'nullable|string',
            'forma_pagamento' => 'nullable|in:dinheiro,cartao,pix,boleto',
        ]);

        try {
            $ordem = OrdemServico::findOrFail($dados['id']);
            if (!__valida_objeto($ordem)) {
                abort(403);
            }

            $ordem->estado = $dados['novo_estado'];
            $ordem->status_pagamento = $dados['novo_status_pagamento'];
            $ordem->descricao = $dados['descricao'];

            if ($dados['novo_status_pagamento']) {
                $ordem->desconto = __convert_value_bd($dados['desconto'] ?? 0);
                $ordem->valor_pago = __convert_value_bd($dados['valor_pago'] ?? 0);
                $ordem->valor_entrada = __convert_value_bd($dados['valor_entrada'] ?? 0);
                $ordem->forma_pagamento = $dados['forma_pagamento'] ?? null;
            } else {
                $ordem->valor_pago = null;
                $ordem->forma_pagamento = null;
                $ordem->desconto = 0;
                $ordem->valor_entrada = 0;
            }

            $ordem->save();

            // Enviar WhatsApp via Job
            $cliente = $ordem->cliente;
            if ($cliente && $cliente->celular) {
                $numero = '55' . preg_replace('/\D/', '', $cliente->celular);
                $nomeCliente = $cliente->razao_social ?? $cliente->nome ?? 'Cliente';
                $estado = strtolower($ordem->estado);

                $mensagens = [
                    'finalizado' => "Olá {$nomeCliente}, sua ordem foi *FINALIZADA*! 🎉 Agradecemos sua confiança. Avalie-nos: https://avaliar.exemplo.com",
                    'em andamento' => "Olá {$nomeCliente}, sua OS está *em andamento*! Em breve traremos novidades.",
                    'reprovado' => "Olá {$nomeCliente}, sua ordem foi *REPROVADA*. Em caso de dúvidas, fale conosco.",
                    'pendente' => "Olá {$nomeCliente}, sua OS está *pendente*. Avisaremos sobre atualizações.",
                    'pronto' => "Olá {$nomeCliente}, sua ordem está *PRONTA*! ✅ Pode vir buscar."
                ];

                $mensagem = $mensagens[$estado] ?? "Olá {$nomeCliente}, sua ordem foi atualizada para: *{$ordem->estado}*.";

                EnviarMensagemWhatsAppOS::dispatch($request->empresa_id, $numero, $mensagem);
            }

            session()->flash("flash_sucesso", "Ordem de serviço alterada com sucesso!");
        } catch (\Exception $e) {
            Log::error("Erro ao alterar estado da OS [{$request->id}]: " . $e->getMessage());
            session()->flash("flash_erro", "Erro ao alterar a ordem: " . $e->getMessage());
        }

        return redirect()->route('ordemServico.completa', $request->id);
    }


    public function enviarWhatsApp(Request $request)
    {
        $dados = $request->validate([
            'number' => 'required|string',
            'text' => 'required|string',
        ]);

        $numero = '55' . preg_replace('/\D/', '', $dados['number']);

        EnviarMensagemWhatsAppOS::dispatch(
            $this->empresa_id,
            $numero,
            $dados['text']
        );

        return redirect()->route('ordemServico.index', $request->id)->with('success', 'Mensagem enviada com sucesso!');
    }

    public function imprimir($id)
    {
        $ordem = OrdemServico::findOrFail($id);
        if (valida_objeto($ordem)) {
            $config = ConfigNota::where('empresa_id', $this->empresa_id)
                ->first();

            if ($config == null) {
                return redirect('/configNF');
            }

            $p = view('ordem_servico/print')
                ->with('ordem', $ordem)
                ->with('config', $config);

            $domPdf = new Dompdf(["enable_remote" => true]);
            $domPdf->loadHtml($p);

            $pdf = ob_get_clean();

            $domPdf->setPaper("A4");
            $domPdf->render();
            $domPdf->stream("OS $ordem->numero_sequencial.pdf", array("Attachment" => false));
        } else {
            return redirect('/403');
        }
    }


    public function destroy($id)
    {
        $item = OrdemServico::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            DB::transaction(function () use ($item) {
                $this->removeItens($item);
                $item->delete();
            });
            session()->flash("flash_sucesso", "Ordem de serviço excluída com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Não foi possível excluir a ordem de serviço.");
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('ordemServico.index');
    }

    private function removeItens($item)
    {
        foreach ($item->servicos as $s) {
            $s->delete();
        }
        foreach ($item->produtos as $produto) {
            $produto->delete();
        }
        foreach ($item->relatorios as $s) {
            $s->delete();
        }
        foreach ($item->funcionarios as $s) {
            $s->delete();
        }
    }

    public function finalizarOs($id)
    {
        $pedido = OrdemServico::findOrFail($id);
        if (!__valida_objeto($pedido)) {
            abort(403);
        }

        if (!empty($pedido->produtos)) {

            $itensDopedido = [];
            foreach ($pedido->produtos as $i) {
                $product = $i->produto;
                $qtd = $i->quantidade;
                $value_unit = $i->valor_unitario;
                $sub_total = $i->sub_total;
                $key = null;
                $idOs = $id;
                $itensDopedido[] = view('frontBox.partials.row_frontBox', compact('product', 'qtd', 'value_unit', 'sub_total', 'key', 'idOs'));
            }

            // dd($itensDopedido);

            $config = ConfigNota::where('empresa_id', request()->empresa_id)
                ->first();
            $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
                ->get();
            $categorias = Categoria::where('empresa_id', request()->empresa_id)
                ->get();
            $produtos = Produto::where('empresa_id', request()->empresa_id)
                ->get();
            $tributacao = Tributacao::where('empresa_id', request()->empresa_id)
                ->get();
            $tiposPagamento = VendaCaixa::tiposPagamento();
            $config = ConfigNota::where('empresa_id', request()->empresa_id)
                ->first();
            if ($config->nat_op_padrao == null) {
                session()->flash("flash_warning", "Informe a natureza de operação primeiramente!");
                return redirect()->route('configNF.index');
            }
            $certificado = Certificado::where('empresa_id', request()->empresa_id)
                ->first();
            $usuario = Usuario::findOrFail(get_id_user());
            if (count($naturezas) == 0 || $config == null || count($categorias) == 0  || count($produtos) == 0 || $tributacao == null) {
                $p = view("frontBox.alerta", compact('produtos', 'categorias', 'naturezas', 'config', 'tributacao'));
                return $p;
            } else {
                $tiposPagamentoMulti = VendaCaixa::tiposPagamentoMulti();
                $categorias = Categoria::where('empresa_id', request()->empresa_id)
                    ->orderBy('nome')->get();
                $clientes = Cliente::orderBy('razao_social')
                    ->where('empresa_id', request()->empresa_id)
                    ->get();
                foreach ($clientes as $c) {
                    $c->totalEmAberto = 0;
                    $soma = $this->getTotalContaCredito($c);
                    if ($soma->total != null) {
                        $c->totalEmAberto = $soma->total;
                    }
                }
                $atalhos = ConfigCaixa::where('usuario_id', get_id_user())
                    ->first();
                $lista = ListaPreco::where('empresa_id', request()->empresa_id)->get();
                $rascunhos = $this->getRascunhos();
                $preVendas = VendaCaixaPreVenda::where('empresa_id', request()->empresa_id)
                    ->where('status', 0)
                    ->limit(20)
                    ->orderBy('id', 'desc')
                    ->get();
                $funcionarios = Funcionario::where('funcionarios.empresa_id', request()->empresa_id)
                    ->select('funcionarios.*')
                    ->join('usuarios', 'usuarios.id', '=', 'funcionarios.usuario_id')
                    ->get();
                $funcionarios = $this->validaCaixaAberto($funcionarios);
                if (sizeof($funcionarios) == 0 && $usuario->caixa_livre) {
                    session()->flash("flash_erro", "Usuário definido para caixa livre, cadastre ao menos um funcionário!");
                    return redirect('/funcionarios');
                }

                $usuarios = Usuario::where('empresa_id', request()->empresa_id)
                    ->where('ativo', 1)
                    ->orderBy('nome', 'asc')
                    ->get();
                $vendedor = Funcionario::where('empresa_id', request()->empresa_id)->get();
                $estados = Cliente::estados();
                $cidades = Cidade::all();
                $pais = Pais::all();
                $grupos = GrupoCliente::get();
                $acessores = Acessor::where('empresa_id', request()->empresa_id)->get();
                $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
                $abertura = AberturaCaixa::where('empresa_id', request()->empresa_id)
                    ->where('usuario_id', get_id_user())
                    ->where('status', 0)
                    ->orderBy('id', 'desc')
                    ->first();
                $sangrias = [];
                $suprimentos = [];
                $vendas = [];
                if ($abertura != null) {
                    $sangrias = SangriaCaixa::where('empresa_id', request()->empresa_id)
                        ->where('usuario_id', get_id_user())
                        ->whereBetween('created_at', [
                            $abertura->created_at,
                            date('Y-m-d H:i:s')
                        ])
                        ->get();
                    $suprimentos = SuprimentoCaixa::where('empresa_id', request()->empresa_id)
                        ->where('usuario_id', get_id_user())
                        ->whereBetween('created_at', [
                            $abertura->created_at,
                            date('Y-m-d H:i:s')
                        ])
                        ->get();
                    $vendas = VendaCaixa::where('empresa_id', request()->empresa_id)
                        ->where('usuario_id', get_id_user())
                        ->whereBetween('created_at', [
                            $abertura->created_at,
                            date('Y-m-d H:i:s')
                        ])->get();
                }
                return view('frontBox.index', compact(
                    'tiposPagamento',
                    'config',
                    'pedido',
                    'itensDopedido',
                    'abertura',
                    'certificado',
                    'rascunhos',
                    'preVendas',
                    'estados',
                    'sangrias',
                    'vendas',
                    'suprimentos',
                    'cidades',
                    'pais',
                    'grupos',
                    'acessores',
                    'vendedor',
                    'usuarios',
                    'funcionarios',
                    'lista',
                    'atalhos',
                    'usuario',
                    'clientes',
                    'categorias',
                    'tiposPagamentoMulti',
                ));
            }

        } else {
            session()->flash("flash_erro", "Algo deu errado" . "Não pode finalizar OS sem informar ao menos um produto!");
            return redirect()->route('ordemServico.completa', $id);
        }
    }

    private function getTotalContaCredito($cliente)
    {
        return CreditoVenda::selectRaw('sum(vendas.valor_total) as total')
            ->join('vendas', 'vendas.id', '=', 'credito_vendas.venda_id')
            ->where('credito_vendas.cliente_id', $cliente->id)
            ->where('status', 0)
            ->first();
    }

    private function getRascunhos()
    {
        return VendaCaixa::where('rascunho', 1)
            ->where('empresa_id', request()->empresa_id)
            ->limit(20)
            ->orderBy('id', 'desc')
            ->get();
    }
public function dashboard(Request $request)
{
    $empresa_id = $request->empresa_id;

    // Filtro de Data dinâmico
    $inicio = $request->get('start_date') 
        ? \Carbon\Carbon::parse($request->get('start_date'))->startOfDay() 
        : now()->startOfMonth();

    $fim = $request->get('end_date') 
        ? \Carbon\Carbon::parse($request->get('end_date'))->endOfDay() 
        : now()->endOfMonth();

    // Query base (reutilizável)
    $queryBase = OrdemServico::where('empresa_id', $empresa_id)
        ->whereBetween('created_at', [$inicio, $fim]);

    // 1. Total de OS
    $totalOs = (clone $queryBase)->count();

    // 2. OS por estado
    $osPorEstado = (clone $queryBase)
        ->select('estado', \DB::raw('count(*) as total'))
        ->groupBy('estado')
        ->get();

    // 3. Faturamento (Serviços e Produtos)
    $totalServicos = \App\Models\ServicoOs::whereHas('ordemServico', function($q) use ($empresa_id, $inicio, $fim) {
            $q->where('empresa_id', $empresa_id)
              ->whereBetween('created_at', [$inicio, $fim]);
        })->sum('sub_total');

    $totalProdutos = \App\Models\ProdutoOs::whereHas('ordemServico', function($q) use ($empresa_id, $inicio, $fim) {
            $q->where('empresa_id', $empresa_id)
              ->whereBetween('created_at', [$inicio, $fim]);
        })->sum('sub_total');

    $totalFaturado = $totalServicos + $totalProdutos;

    // 4. Lista de OS com PAGINAÇÃO (Troquei ->get() por ->paginate())
    // O número 10 indica quantos registros por página dentro da tabela
    $osRecentes = (clone $queryBase)
        ->with('cliente')
        ->orderBy('created_at', 'desc')
        ->paginate(5); 

    // 5. Agrupamento por dia
    $osPorDia = (clone $queryBase)
        ->select(\DB::raw('DATE(created_at) as dia'), \DB::raw('count(*) as total'))
        ->groupBy('dia')
        ->orderBy('dia', 'asc')
        ->get();

    $mesReferencia = $inicio->format('d/m/Y') . ' até ' . $fim->format('d/m/Y');

    return view('ordem_servico.dashboard', [
        'totalOs' => $totalOs,
        'osPorEstado' => $osPorEstado,
        'osRecentes' => $osRecentes,
        'totalFaturado' => $totalFaturado,
        'totalServicos' => $totalServicos,
        'totalProdutos' => $totalProdutos,
        'osPorDia' => $osPorDia,
        'mesReferencia' => $mesReferencia,
        'title' => 'Dashboard de Ordens de Serviço'
    ]);
}
    private function validaCaixaAberto($funcionarios)
    {
        $temp = [];
        $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
        foreach ($funcionarios as $f) {
            $aberturaNfe = AberturaCaixa::where('empresa_id', request()->empresa_id)
                ->when($config->caixa_por_usuario == 1, function ($q) use ($f) {
                    return $q->where('usuario_id', $f->usuario_id);
                })
                ->orderBy('id', 'desc')->first();
            if ($aberturaNfe != null) {
                if ($aberturaNfe->status == 0)
                    array_push($temp, $f);
            }
        }
        return $temp;
    }
}