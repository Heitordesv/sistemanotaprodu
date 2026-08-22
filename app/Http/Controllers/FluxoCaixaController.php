<?php

namespace App\Http\Controllers;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\CreditoVenda;
use App\Models\MovimentacaoFinaneira;
use App\Models\OrdemServico;
use App\Models\ServicoOs;

use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;

class FluxoCaixaController extends Controller
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

        if ($start_date && $end_date) {
            $datas = $this->returnPesquisa($start_date, $end_date);
        } else {
            $datas = $this->returnDateMesAtual();
        }

        $fluxo = $this->criarArrayDeDatas($datas['start'], $datas['end']);

        return view('fluxo_caixa.index', compact('datas', 'fluxo'));
    }

    private function returnDateMesAtual()
    {
        $hoje = date('Y-m-d');
        $primeiroDia = substr($hoje, 0, 7) . "-01";
        return ['start' => $primeiroDia, 'end' => $hoje];
    }

    private function returnPesquisa($start_date, $end_date)
    {
        return ['start' => $start_date, 'end' => $end_date];
    }

    private function criarArrayDeDatas($inicio, $fim)
    {
        $diferenca = strtotime($fim) - strtotime($inicio);
        $dias = floor($diferenca / (60 * 60 * 24));
        $global = [];
        $dataAtual = $inicio;
        for ($aux = 0; $aux < $dias + 1; $aux++) {
            $contaReceber = $this->getContasReceber($dataAtual);
            $contaPagar = $this->getContasPagar($dataAtual);
            $credito = $this->getCreditoVenda($dataAtual);
            $venda = $this->getVendas($dataAtual);
            $vendaCaixa = $this->getVendaCaixa($dataAtual);
            $os = $this->getOs($dataAtual);
            $osentrada = $this->getOsentrada($dataAtual);

            $tst = [
                'data' => $this->parseViewData($dataAtual),
                'conta_receber' => $contaReceber,
                'conta_pagar' => $contaPagar,
                'credito_venda' => $credito->valor ?? 0,
                'venda' => $venda->valor ?? 0,
                'venda_caixa' => $vendaCaixa->valor ?? 0,
                'os' => $os->valor ?? 0,
               'osentrada' => $osentrada->valor_entrada ?? 0,

            ];
            array_push($global, $tst);
            $temp = [];
            $dataAtual = date('Y-m-d', strtotime($dataAtual . '+1day'));
        }
        return $global;
    }

    private function getContasReceber($data)
    {
        $valor = 0;
        $contas = ContaReceber::selectRaw('data_recebimento as data, sum(valor_recebido) as valor')
            // ->where('updated_at', $data)
            ->whereBetween('data_recebimento', [
                $data . " 00:00:00",
                $data . " 23:59:00"
            ])
            ->where('status', 1)
            ->where('empresa_id', $this->empresa_id)
            // ->groupBy('updated_at')
            ->first();
        $valor += $contas->valor ?? 0;
        return $valor;
    }

    private function getContasPagar($data)
    {
        $contas = ContaPagar::selectRaw('data_pagamento as data, sum(valor_pago) as valor')
            // ->where('updated_at', $data)
            ->whereBetween('data_pagamento', [
                $data . " 00:00:00",
                $data . " 23:59:00"
            ])
            ->where('empresa_id', $this->empresa_id)
            ->where('status', 1)
            ->first();
        return $contas->valor ?? 0;
    }

    private function getCreditoVenda($data)
    {
        $creditos = CreditoVenda::selectRaw('DATE_FORMAT(vendas.data_registro, "%Y-%m-%d") as data, sum(vendas.valor_total) as valor')
            ->join('vendas', 'vendas.id', '=', 'credito_vendas.venda_id')
            ->whereRaw("DATE_FORMAT(credito_vendas.updated_at, '%Y-%m-%d') = '$data'")
            ->where('credito_vendas.status', true)
            ->where('vendas.empresa_id', $this->empresa_id)
            ->groupBy('data')
            ->first();
        return $creditos;
    }

    private function getVendas($data)
    {
        $venda = Venda::selectRaw('DATE_FORMAT(data_registro, "%Y-%m-%d") as data, sum(valor_total) as valor')
            ->whereRaw("DATE_FORMAT(data_registro, '%Y-%m-%d') = '$data' ")
            ->where('empresa_id', $this->empresa_id)
            ->groupBy('data')
            ->first();
        return $venda;
    }

    private function getVendaCaixa($data)
    {
        $venda = VendaCaixa::selectRaw('DATE_FORMAT(data_registro, "%Y-%m-%d") as data, sum(valor_total) as valor')
            ->whereRaw("DATE_FORMAT(data_registro, '%Y-%m-%d') = '$data'")
            ->where('empresa_id', $this->empresa_id)
            ->groupBy('data')
            ->first();
        return $venda;
    }

  private function getOs($data)
{
    // 1. Buscar IDs das OS do dia
    $os_ids = OrdemServico::whereDate('updated_at', $data)
        ->where('estado', 'finalizado')
        ->where('empresa_id', $this->empresa_id)
        ->pluck('id');

    if ($os_ids->isEmpty()) {
        return (object)[
            'data' => $data,
            'valor' => 0
        ];
    }

    // 2. Somar apenas serviços
    $total = ServicoOs::whereIn('ordem_servico_id', $os_ids)
        ->sum('valor_unitario');

    return (object)[
        'data' => $data,
        'valor' => $total
    ];
}
  private function getOsentrada($data)
{
    // Entrada continua vindo da OS
    $totalEntrada = OrdemServico::whereDate('updated_at', $data)
        ->where('empresa_id', $this->empresa_id)
        ->sum('valor_entrada');

    return (object)[
        'data' => $data,
        'valor' => $totalEntrada
    ];
}
    


    private function parseViewData($date)
    {
        return date('d/m/Y', strtotime(str_replace("/", "-", $date)));
    }
}
