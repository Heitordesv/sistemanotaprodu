<?php

namespace App\Http\Controllers;

use App\Models\ApuracaoMensal;
use App\Models\ApuracaoSalarioEvento;
use App\Models\EventoSalario;
use App\Models\FuncionarioEvento; // Certifique-se de que este use está correto
use Illuminate\Database\Eloquent\Builder;
use App\Models\Funcionario;
use App\Models\ContaPagar;
use App\Models\Evento;
use App\Models\CategoriaConta;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ApuracaoMensalController extends Controller
{
    public function index(Request $request)
    {
        $nome = $request->nome;
        $dt_inicio = $request->get('start_date');
        $dt_fim = $request->get('end_date');

        $data = ApuracaoMensal::select('apuracao_mensals.*')
            ->join('funcionarios', 'apuracao_mensals.funcionario_id', '=', 'funcionarios.id')
            ->where('empresa_id', $request->empresa_id)
            ->when($nome, function ($query) use ($nome) {
                return $query->where('funcionarios.nome', 'like', "%$nome%");
            })
            ->when($dt_inicio, function ($query) use ($dt_inicio) {
                return $query->whereDate('apuracao_mensals.created_at', '>=', $dt_inicio);
            })
            ->when($dt_fim, function ($query) use ($dt_fim) {
                return $query->whereDate('apuracao_mensals.created_at', '<=', $dt_fim);
            })
            ->paginate(env("PAGINACAO", 15));

        return view('apuracao_mensal.index', compact('nome', 'dt_inicio', 'dt_fim', 'data'));
    }

    /**
     * Gera o Relatório em PDF com base nos filtros.
     */
 public function relatorioPDF(Request $request)
    {
        $nome = $request->nome;
        $dt_inicio = $request->get('start_date');
        $dt_fim = $request->get('end_date');
        $empresaId = $request->empresa_id;

        // Consulta com filtros
        $query = ApuracaoMensal::query()
            ->join('funcionarios', 'apuracao_mensals.funcionario_id', '=', 'funcionarios.id')
            ->where('funcionarios.empresa_id', $empresaId)
            ->with(['funcionario'])
            ->when($nome, function ($q) use ($nome) {
                $q->where('funcionarios.nome', 'like', "%$nome%");
            })
            ->when($dt_inicio, function ($q) use ($dt_inicio) {
                $q->whereDate('apuracao_mensals.created_at', '>=', $dt_inicio);
            })
            ->when($dt_fim, function ($q) use ($dt_fim) {
                $q->whereDate('apuracao_mensals.created_at', '<=', $dt_fim);
            })
            ->orderBy('funcionarios.nome');

        try {
            $dataRelatorio = $query->get();
            $empresa = Empresa::findOrFail($empresaId);

            // Totalizando valor_final
            $totalPagar = $dataRelatorio->sum(fn($item) => $item->valor_final ?? 0);

            // Gerar PDF
            $pdf = Pdf::loadView('apuracao_mensal.relatorio_pagamento_pdf', [
                'data' => $dataRelatorio,
                'empresa' => $empresa,
                'totalPagar' => $totalPagar,
                'dt_inicio' => $dt_inicio,
                'dt_fim' => $dt_fim,
            ])->setPaper('a4', 'portrait');

            return $pdf->download('relatorio_apuracao_' . date('Ymd_His') . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Erro ao gerar relatório: ' . $e->getMessage());
            return back()->with('flash_erro', 'Erro ao gerar PDF');
        }
    }


private function calculateLiquidValue(ApuracaoMensal $apuracao)
{
    $proventos = 0;
    $descontos = 0;

    // Buscar eventos ativos do funcionário com a relação eventoSalario
    $eventosFuncionario = FuncionarioEvento::where('funcionario_id', $apuracao->funcionario->id)
        ->where('ativo', 1)
        ->with('eventoSalario')
        ->get();

    foreach ($eventosFuncionario as $ev) {

        // valor do evento
        $valor = floatval($ev->valor ?? 0);

        // condicao do evento: soma / diminui
        $condicao = $ev->eventoSalario->condicao ?? null;

        if ($condicao === 'soma') {
            $proventos += $valor;
        }

        if ($condicao === 'diminui') {
            $descontos += $valor;
        }
    }

    // Atribuir valores ao objeto ApuracaoMensal
    $apuracao->total_proventos = $proventos;
    $apuracao->total_descontos = $descontos;
    $apuracao->valor_liquido = $proventos - $descontos;

    return $apuracao;
}


public function pdf($id)
{
    try {
        // 1. Buscar apuração com funcionário
        $apuracao = ApuracaoMensal::with('funcionario')->findOrFail($id);

        // 2. Buscar dados da empresa
        $empresa = Empresa::findOrFail($apuracao->funcionario->empresa_id);

        // Valor base do funcionário para cálculos percentuais
        $salarioBase = $apuracao->funcionario->salario;

        // 3. Buscar eventos ativos do funcionário, carregando a relação 'eventoSalario'
        $eventosFuncionario = FuncionarioEvento::where('funcionario_id', $apuracao->funcionario->id)
            ->where('ativo', 1)
            ->with('eventoSalario')
            ->get();

        // 4. Mapear, calcular valores e aplicar ajustes
        $eventosMapeados = $eventosFuncionario->map(function ($evento) use ($salarioBase) {

            // Nome e código do evento
            $nome = $evento->eventoSalario->nome ?? 'Descrição Não Encontrada';
            $codigo = $evento->eventoSalario->codigo ?? ($evento->condicao === 'soma' ? '000' : '999');

            // Tipo do valor: 'fixo' ou 'percentual'
            $tipoValor = $evento->eventoSalario->tipo_valor ?? 'fixo';

            // Valor inicial
            $valorCalculado = $evento->valor;
            $referencia = '1'; // padrão unidade/mês

            // Se for percentual, calcula valor real com ajuste manual
            if (strtolower($tipoValor) === 'percentual') {
                $percentual = $evento->valor / 100;

                // Ajuste manual por conta (campo 'ajuste_percentual', 1 = 100%)
                $fatorAjuste = $evento->ajuste_percentual ?? 1;
                $percentual = $percentual * $fatorAjuste;

                // Valor calculado com ajuste
                $valorCalculado = $salarioBase * $percentual;

                // Referência para exibir no PDF
                $referencia = number_format($evento->valor * $fatorAjuste, 2, ',', '.') . '%';
            }

            // Atribui propriedades calculadas ao objeto
            $evento->nome = $nome;
            $evento->codigo = $codigo;
            $evento->referencia = $referencia;
            $evento->valor_calculado = (float) number_format($valorCalculado, 2, '.', '');

            return $evento;
        });

        // 5. Separar proventos e descontos
        $proventos = $eventosMapeados->filter(fn($e) => ($e->condicao ?? '') === 'soma');
        $descontos = $eventosMapeados->filter(fn($e) => ($e->condicao ?? '') === 'diminui');

        // 6. Somar valores
        $totalProventos = $proventos->sum('valor_calculado');
        $totalDescontos = $descontos->sum('valor_calculado');

        // 7. Total líquido
        $valorLiquido = $totalProventos - $totalDescontos;

        // 8. Bases de cálculo
        $baseInss = $apuracao->base_inss ?? $salarioBase;
        $baseIrrf = $apuracao->base_irrf ?? 0;
        $baseFgts = $totalProventos;          // Base do FGTS = soma dos proventos
        $fgtsMes = $totalProventos * 0.08;    // 8% do total de proventos

        // 9. Gerar PDF
        $pdf = Pdf::loadView('apuracao_mensal.holerite_pdf', [
            'apuracao' => $apuracao,
            'empresa' => $empresa,
            'proventos' => $proventos,
            'descontos' => $descontos,
            'totalProventos' => $totalProventos,
            'totalDescontos' => $totalDescontos,
            'valorLiquido' => $valorLiquido,
            'baseInss' => $baseInss,
            'baseIrrf' => $baseIrrf,
            'baseFgts' => $baseFgts,
            'fgtsMes' => $fgtsMes,
        ])->setPaper('a4', 'portrait');

        // Nome do arquivo
        $nomeArquivo = 'holerite-' . preg_replace('/[^A-Za-z0-9]/', '', $apuracao->funcionario->nome)
                     . '-' . $apuracao->mes . '-' . $apuracao->ano . '.pdf';

        return $pdf->download($nomeArquivo);

    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Erro ao gerar o PDF: ' . $e->getMessage());
        return redirect()->back();
    }
}

    public function create()
    {
        $CategoriaConta = CategoriaConta::where('tipo', 'pagar')
            ->where('empresa_id', request()->empresa_id)
            ->orderBy('nome')
            ->get();

        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)
            ->orderBy('nome')
            ->get();

        $mesAtual = (int) date('m') - 1;

        return view('apuracao_mensal.create', compact('mesAtual', 'funcionarios', 'CategoriaConta'));
    }

    public function getEventos($id)
    {
        try {
            $item = Funcionario::findOrFail($id);
            if (sizeof($item->eventos) == 0) {
                return response()->json("", 200);
            }
            return view('apuracao_mensal.eventos', compact('item'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $ap = [
                    'funcionario_id' => $request->funcionario,
                    'mes' => $request->mes,
                    'ano' => $request->ano,
                    'valor_final' => __convert_value_bd($request->valor_total),
                    'forma_pagamento' => $request->tipo_pagamento,
                    'observacao' => $request->observacao ?? ''
                ];

                $result = ApuracaoMensal::create($ap);

                for ($i = 0; $i < sizeof($request->evento); $i++) {
                    $ev = EventoSalario::find($request->evento[$i]);
                    if ($ev) {
                        ApuracaoSalarioEvento::create([
                            'apuracao_id' => $result->id,
                            'evento_id' => $ev->id,
                            'valor' => __convert_value_bd($request->evento[$i]),
                            'metodo' => $request->metodo[$i],
                            'condicao' => $request->condicao[$i],
                            'nome' => $ev->nome
                        ]);
                    }
                }

                if ($request->conta_pagar) {
                    $conta = ContaPagar::create([
                        'empresa_id' => $request->empresa_id,
                        'funcionario_id' => $request->funcionario,
                        'data_vencimento' => $request->vencimento,
                        'valor_integral' => __convert_value_bd($request->valor_total),
                        'referencia' => 'Apuração salário ' . $result->funcionario->nome,
                        'status' => $request->conta_paga,
                        'data_pagamento' => $request->vencimento,
                        'valor_pago' => $request->conta_paga ? __convert_value_bd($request->valor_total) : 0,
                        'tipo_pagamento' => $request->tipo_pagamento,
                        'categoria_id' => $request->categoria_id
                    ]);

                    $result->conta_pagar_id = $conta->id;
                    $result->save();
                }
            });

            session()->flash('flash_sucesso', 'Salvo com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->route('apuracaoMensal.index');
    }

    public function destroy($id)
    {
        $item = ApuracaoMensal::findOrFail($id);

        try {
            DB::transaction(function () use ($item) {
                $item->eventos()->delete();

                if ($item->conta_pagar_id) {
                    ContaPagar::where('id', $item->conta_pagar_id)->delete();
                }

                $item->delete();
            });

            session()->flash("flash_sucesso", "Registro removido com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->back();
    }
}
