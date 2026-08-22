<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoriaConta;
use App\Models\ContaPagar;
use App\Models\ConfigNota;
use App\Models\Fornecedor;
use App\Models\Funcionario;
use App\Models\ComprovantePagamento;
use App\Models\ContaPagamentoDetalhe;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class ContaPagarController extends Controller
{
public function index(Request $request)
{
    $empresa_id = $request->empresa_id;

    // Relacionamentos b谩sicos
    $fornecedores = Fornecedor::where('empresa_id', $empresa_id)->get();
    $funcionarios = Funcionario::where('empresa_id', $empresa_id)->get();

    // Filtros
    $start_date     = $request->get('start_date');
    $end_date       = $request->get('end_date');
    $fornecedor_id  = $request->get('fornecedor_id');
    $funcionario_id = $request->get('funcionario_id');
    $filial_id      = $request->get('filial_id');

    // Ordena莽茫o segura
    $order_by  = $request->get('order_by', 'updated_at');
    $direction = $request->get('direction', 'desc');

    $allowedOrder = ['updated_at', 'created_at', 'data_vencimento'];

    if (!in_array($order_by, $allowedOrder)) {
        $order_by = 'updated_at';
    }

    if (!in_array($direction, ['asc', 'desc'])) {
        $direction = 'desc';
    }

    // Filial padr茫o
    $local_padrao = __get_local_padrao();
    if (!$filial_id && $local_padrao) {
        $filial_id = $local_padrao;
    }

    // =========================
    // QUERY BASE
    // =========================
    $query = ContaPagar::where('empresa_id', $empresa_id)
        ->select('*')
        ->selectRaw('
            COALESCE(valor_integral, 0) as valor_integral_calc,
            COALESCE(valor_pago, 0) as valor_pago_calc,
            (COALESCE(valor_integral, 0) - COALESCE(valor_pago, 0)) as valor_restante
        ')

        // 馃敟 FILTRO CORRETO (DATA DE VENCIMENTO)
        ->when($start_date, fn($q) => $q->whereDate('data_vencimento', '>=', $start_date))
        ->when($end_date, fn($q) => $q->whereDate('data_vencimento', '<=', $end_date))

        // Filtros
        ->when($fornecedor_id, fn($q) => $q->where('fornecedor_id', $fornecedor_id))
        ->when($funcionario_id, fn($q) => $q->where('funcionario_id', $funcionario_id))

        // FILIAL
        ->when($filial_id != 'todos', function ($q) use ($filial_id) {
            if ($filial_id == -1) {
                return $q->whereNull('filial_id');
            }
            return $q->where('filial_id', $filial_id);
        });

    // =========================
    // LISTAGEM
    // =========================
    $data = (clone $query)
        ->orderBy($order_by, $direction)
        ->paginate(env("PAGINACAO", 15))
        ->withQueryString();

    // =========================
    // DASHBOARD (SALDO REAL)
    // =========================
    $dashboardData = (clone $query)->get();

    // 馃挵 TOTAL REAL DE D脥VIDA (O QUE FALTA PAGAR)
    $totalFaltaPagar = $dashboardData->sum('valor_restante');

    // 馃挵 TOTAL J脕 PAGO
    $totalValorPago = $dashboardData->sum('valor_pago_calc');

    // 馃挵 TOTAL LAN脟ADO
    $totalGeral = $dashboardData->sum('valor_integral_calc');

    // 馃搳 CONTADORES
    $totalRegistros = $dashboardData->count();

    $totalQuitados = $dashboardData
        ->filter(fn($i) => $i->valor_restante <= 0)
        ->count();

    $totalEmAberto = $dashboardData
        ->filter(fn($i) => $i->valor_restante > 0)
        ->count();

    return view('conta_pagar.index', compact(
        'data',
        'dashboardData',
        'fornecedores',
        'funcionarios',
        'filial_id',
        'order_by',
        'direction',

        // DASHBOARD
        'totalFaltaPagar',
        'totalValorPago',
        'totalGeral',
        'totalRegistros',
        'totalQuitados',
        'totalEmAberto'
    ));
}
public function create(Request $request)
{
    $fornecedores = Fornecedor::where('empresa_id', $request->empresa_id)
        ->orderBy('razao_social')
        ->get();

    $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->get();

    $categorias = CategoriaConta::where('empresa_id', $request->empresa_id)
        ->where('tipo', 'pagar')
        ->orderBy('nome')
        ->get();

  //  $subcategorias = SubcategoriaConta::where('empresa_id', $request->empresa_id)
    //    ->orderBy('nome')
      //  ->get();

   // $centros = CentroCusto::where('empresa_id', $request->empresa_id)
      //  ->orderBy('nome')
      //  ->get();

   // $cartoes = Cartao::where('empresa_id', $request->empresa_id)
      //  ->orderBy('nome')
      //  ->get();

   // $filiais = Filial::where('empresa_id', $request->empresa_id)
     ///   ->orderBy('razao_social')
     //   ->get();

    $pagamentoDetalhes = null;

    return view('conta_pagar.create', compact(
        'categorias',
        'fornecedores',
        'funcionarios',
        'pagamentoDetalhes'
    ));
}
public function store(Request $request)
{
    $this->_validate($request);

    try {

        DB::transaction(function () use ($request) {

            $request->merge([
                'filial_id' => $request->filial_id == -1
                    ? null
                    : $request->filial_id
            ]);

            // QUANTIDADE DE PARCELAS
            $qtdParcelas = $request->tem_recorrencia
                ? ($request->qtd_parcelas ?? 1)
                : 1;

            $valorTotal = __convert_value_bd($request->valor_integral);

            $valorParcela = $valorTotal / $qtdParcelas;

            for ($i = 0; $i < $qtdParcelas; $i++) {

                // DATA VENCIMENTO
                $dataVencimento = \Carbon\Carbon::parse($request->data_vencimento);

                if ($request->tem_recorrencia) {

                    switch ($request->tipo_recorrencia) {

                        case 'quinzenal':
                            $dataVencimento->addDays(15 * $i);
                            break;

                        case 'semanal':
                            $dataVencimento->addDays(7 * $i);
                            break;

                        default:
                            $dataVencimento->addMonths($i);
                            break;
                    }
                }

                // REFERÊNCIA
                $referencia = $request->referencia;

                if ($qtdParcelas > 1) {
                    $referencia .= ' (' . ($i + 1) . '/' . $qtdParcelas . ')';
                }

                // DEFINE TIPO CONTA
                $tipoConta = $request->tem_recorrencia
                    ? 'Fixa'
                    : 'Variável';

                // CRIA CONTA
                $conta = ContaPagar::create([

                    'compra_id' => null,

                    'referencia' => $referencia,

                    'observacao' => $request->observacao,

                    'categoria_id' => $request->categoria_id,

                    'empresa_id' => $request->empresa_id,

                    'fornecedor_id' => $request->fornecedor_id ?: null,

                    'funcionario_id' => $request->funcionario_id ?: null,

                    'tipo_pagamento' => $request->tipo_pagamento,

                    'tipo_conta' => $tipoConta,

                    'tipo_recorrencia' => $request->tem_recorrencia
                        ? $request->tipo_recorrencia
                        : null,

                    'valor_integral' => $valorParcela,

                    'valor_pago' => $request->status
                        ? $valorParcela
                        : 0,

                    'status' => $request->status,

                    'data_vencimento' => $dataVencimento,

                    'data_pagamento' => $request->status
                        ? now()
                        : null,

                    'filial_id' => $request->filial_id
                ]);

                // DETALHES PAGAMENTO
                if (in_array($request->tipo_pagamento, [
                    'Boleto',
                    'Pix',
                    'Depósito Bancário'
                ])) {

                    $boletoPdf = null;

                    if ($request->hasFile('boleto_pdf')) {

                        $boletoPdf = $request->file('boleto_pdf')
                            ->store('pagamentos', 'public');
                    }

                    \App\Models\ContaPagamentoDetalhe::create([

                        'conta_pagar_id' => $conta->id,

                        'tipo_pagamento' => $request->tipo_pagamento,

                        'boleto_pdf' => $boletoPdf,

                        'boleto_codigo' => $request->boleto_codigo,

                        'pix_chave' => $request->pix_chave,

                        'dados_bancarios' => $request->dados_bancarios
                    ]);
                }
            }
        });

        session()->flash(
            'flash_sucesso',
            'Conta a pagar cadastrada com sucesso!'
        );

    } catch (\Exception $e) {

        session()->flash(
            'flash_erro',
            'Algo deu errado: ' . $e->getMessage()
        );

        __saveLogError($e, $request->empresa_id);
    }

    return redirect()->route('conta-pagar.index');
}
public function edit(Request $request, $id)
{
    $item = ContaPagar::findOrFail($id);

    if (!__valida_objeto($item)) {
        abort(403);
    }

    $fornecedores = Fornecedor::where('empresa_id', $request->empresa_id)
        ->orderBy('razao_social')
        ->get();

    $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->get();

   $categorias = CategoriaConta::where('empresa_id', $request->empresa_id)
        ->where('tipo', 'pagar')
        ->orderBy('nome')
        ->get();

 //   $subcategorias = SubcategoriaConta::where('empresa_id', $request->empresa_id)
    //    ->orderBy('nome')
    //    ->get();
//
 //   $centros = CentroCusto::where('empresa_id', $request->empresa_id)
    //    ->orderBy('nome')
    //    ->get();
//
  //  $cartoes = Cartao::where('empresa_id', $request->empresa_id)
   //     ->orderBy('nome')
      //  ->get();

  // / $filiais = Filial::where('empresa_id', $request->empresa_id)
     //   ->orderBy('razao_social')
       // ->get();

    $pagamentoDetalhes = $item->detalhesPagamento()->first();

    return view('conta_pagar.edit', compact(
        'item',
        'categorias',
        'fornecedores',
        'funcionarios',
        'pagamentoDetalhes'
    ));
}

public function update(Request $request, $id)
{
    $this->_validate($request);

    $item = ContaPagar::findOrFail($id);

    try {

        DB::transaction(function () use ($request, $item) {

            $request->merge([
                'valor_integral' => __convert_value_bd($request->valor_integral),

                'filial_id' => $request->filial_id == -1
                    ? null
                    : $request->filial_id
            ]);

            // DEFINE TIPO CONTA
            $tipoConta = $request->tem_recorrencia
                ? 'Fixa'
                : 'Variável';

            // DATA PAGAMENTO
            $dataPagamento = $request->status
                ? now()
                : null;

            // ATUALIZA CONTA
            $item->update([

                'referencia' => $request->referencia,

                'observacao' => $request->observacao,

                'categoria_id' => $request->categoria_id,

                'empresa_id' => $request->empresa_id,

                'fornecedor_id' => $request->fornecedor_id ?: 0,

                'funcionario_id' => $request->funcionario_id ?: 0,

                'tipo_pagamento' => $request->tipo_pagamento,

                'tipo_conta' => $tipoConta,

                'tipo_recorrencia' => $request->tem_recorrencia
                    ? $request->tipo_recorrencia
                    : null,

                'valor_integral' => $request->valor_integral,

                'valor_pago' => $request->status
                    ? $request->valor_integral
                    : 0,

                'status' => $request->status,

                'data_vencimento' => $request->data_vencimento,

                'data_pagamento' => $dataPagamento,

                'filial_id' => $request->filial_id
            ]);

            // DETALHES PAGAMENTO
            if (in_array($request->tipo_pagamento, [
                'Boleto',
                'Pix',
                'Depósito Bancário'
            ])) {

                $detalhe = $item->detalhesPagamento()->first();

                $boletoPdf = $detalhe->boleto_pdf ?? null;

                if ($request->hasFile('boleto_pdf')) {

                    $boletoPdf = $request->file('boleto_pdf')
                        ->store('pagamentos', 'public');
                }

                if ($detalhe) {

                    $detalhe->update([

                        'tipo_pagamento' => $request->tipo_pagamento,

                        'boleto_pdf' => $boletoPdf,

                        'boleto_codigo' => $request->boleto_codigo,

                        'pix_chave' => $request->pix_chave,

                        'dados_bancarios' => $request->dados_bancarios
                    ]);

                } else {

                    ContaPagamentoDetalhe::create([

                        'conta_pagar_id' => $item->id,

                        'tipo_pagamento' => $request->tipo_pagamento,

                        'boleto_pdf' => $boletoPdf,

                        'boleto_codigo' => $request->boleto_codigo,

                        'pix_chave' => $request->pix_chave,

                        'dados_bancarios' => $request->dados_bancarios
                    ]);
                }

            } else {

                // REMOVE DETALHES SE NÃO FOR MAIS NECESSÁRIO
                $item->detalhesPagamento()->delete();
            }
        });

        session()->flash(
            'flash_sucesso',
            'Conta a pagar atualizada com sucesso!'
        );

    } catch (\Exception $e) {

        session()->flash(
            'flash_erro',
            'Algo deu errado: ' . $e->getMessage()
        );

        __saveLogError($e, $request->empresa_id);
    }

    return redirect()->route('conta-pagar.index');
}



    private function _validate(Request $request)
    {
        $rules = [
            'fornecedor_id' => 'required',
            'referencia' => 'required',
            'valor_integral' => 'required',
            'data_vencimento' => 'required',
        ];
        $messages = [
            'referencia.required' => 'O campo referencia 茅 obrigat贸rio.',
            'fornecedor_id.required' => 'O campo fornecedor 茅 obrigat贸rio.',
            'valor_integral.required' => 'O campo valor 茅 obrigat贸rio.',
            'data_vencimento.required' => 'O campo vencimento 茅 obrigat贸rio.'
        ];
        $this->validate($request, $rules, $messages);
    }

  public function destroy($id)
{
    $item = ContaPagar::findOrFail($id);

    if (!__valida_objeto($item)) {
        abort(403);
    }

    try {
        $item->delete();

        return redirect()->back()->with('flash_sucesso', 'Conta removida!');

    } catch (\Exception $e) {

        __saveLogError($e, request()->empresa_id);

        return redirect()->back()->with('flash_erro', 'Algo deu errado: ' . $e->getMessage());
    }
}
   
    // Exibe a tela de pagamento
    public function pay($id)
    {
        $item = ContaPagar::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        //dd($item);
        return view('conta_pagar.pay', compact('item'));
    }

    // Processa pagamento e upload de comprovante
public function payPut(Request $request, $id)
{
    $item = ContaPagar::findOrFail($id);

    if (!__valida_objeto($item)) {
        abort(403);
    }

    $request->validate([
        'valor_pago' => 'required', // O valor que o usu谩rio est谩 pagando AGORA
        'data_pagamento' => 'required|date',
        'tipo_pagamento' => 'required',
        'comprovante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'observacao' => 'nullable|string|max:500'
    ]);

    try {
        DB::transaction(function () use ($item, $request) {

            // 1. Converte o valor que veio do formul谩rio (ex: "50,00" -> 50.00)
            $valorPagoAgora = (float) __convert_value_bd($request->valor_pago);
            
            // 2. Pega os valores atuais do banco (usando os nomes exatos do seu array)
            $valorIntegral = (float) $item->valor_integral; // 182.50
            $valorJaPagoAcumulado = (float) $item->valor_pago; // O que j谩 foi pago antes

            // 3. Soma o novo pagamento ao que j谩 existia
            $novoTotalPago = $valorJaPagoAcumulado + $valorPagoAgora;

            // 4. Trava de seguran莽a: Se o usu谩rio tentar pagar mais que o total, 
            // a gente limita ao valor integral para n茫o ficar saldo negativo "fantasma"
            if ($novoTotalPago > $valorIntegral) {
                $novoTotalPago = $valorIntegral;
            }

            // 5. Atualiza os campos do item
            $item->valor_pago = $novoTotalPago;
            $item->data_pagamento = $request->data_pagamento;
            $item->tipo_pagamento = $request->tipo_pagamento;

            // 馃幆 A REGRA: Status s贸 vira 1 (true) se o total pago for igual ao integral
            // Se for 182.49, o status continua 0. Se for 182.50, o status vira 1.
            if ($novoTotalPago >= $valorIntegral) {
                $item->status = 1; 
            } else {
                $item->status = 0; // Garante que continue aberto se for parcial
            }

            $item->save();

            // 6. Registro do Comprovante
            if ($request->hasFile('comprovante') && $request->file('comprovante')->isValid()) {
                $file = $request->file('comprovante');
                $nomeArquivo = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $caminho = $file->storeAs('comprovantes_pagamentos', $nomeArquivo, 'public');

                ComprovantePagamento::create([
                    'conta_pagar_id' => $item->id,
                    'empresa_id'     => $item->empresa_id,
                    'arquivo'        => $caminho,
                    'tipo_arquivo'   => $file->getClientOriginalExtension(),
                    'usuario_id'     => auth()->id(),
                    'observacao'     => $request->observacao,
                    'data_upload'    => now(),
                ]);
            }
        });

        session()->flash("flash_sucesso", "Pagamento parcial registrado!");

    } catch (\Exception $e) {
        __saveLogError($e, $item->empresa_id);
        session()->flash("flash_erro", "Erro ao processar pagamento: " . $e->getMessage());
    }

    return redirect()->route('conta-pagar.index');
}
     public function salvarComprovante(Request $request, $id)
    {
        $conta = ContaPagar::findOrFail($id);

        if (!__valida_objeto($conta)) abort(403);

        $request->validate([
            'comprovante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'observacao' => 'nullable|string|max:500'
        ]);

        if ($request->hasFile('comprovante') && $request->file('comprovante')->isValid()) {
            $file = $request->file('comprovante');
            $nomeArquivo = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) 
                          . '.' . $file->getClientOriginalExtension();

            $caminho = $file->storeAs('comprovantes_pagamentos', $nomeArquivo, 'public');

            ComprovantePagamento::create([
                'conta_pagar_id' => $conta->id,
                'empresa_id' => $conta->empresa_id,
                'arquivo' => $caminho,
                'tipo_arquivo' => $file->getClientOriginalExtension(),
                'usuario_id' => auth()->id(),
                'observacao' => $request->observacao ?? null,
                'data_upload' => now(),
            ]);

            return redirect()->back()->with('flash_sucesso', 'Comprovante salvo com sucesso!');
        }

        return redirect()->back()->with('flash_erro', 'Falha ao enviar o comprovante.');
    }


}
