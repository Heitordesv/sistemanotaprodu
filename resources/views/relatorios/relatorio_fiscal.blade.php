@extends('relatorios.default')
@section('content')

<div style="margin-bottom: 20px;">
    @if($d1 && $d2)
        <span><strong>Período:</strong> {{ \Carbon\Carbon::parse($d1)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($d2)->format('d/m/Y') }}</span>
    @endif
    
    @if(isset($natureza_nome) && $natureza_nome)
        <span style="margin-left: 20px;"><strong>Natureza:</strong> {{ $natureza_nome }}</span>
    @endif

    @if($tipo != 'todos')
        <span style="margin-left: 20px;"><strong>Tipo:</strong> {{ strtoupper($tipo) }}</span>
    @endif
</div>  

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px; width: 100%;">
    <thead>
        <tr>
            <th width="20%" class="text-left">Cliente</th>
            <th width="12%" class="text-left">Data</th>
            <th width="10%" class="text-left">Estado</th>
            <th width="12%" class="text-left">Pagamento</th>
            <th width="25%" class="text-left">Chave</th>
            <th width="10%" class="text-left">Valor</th>
            <th width="6%" class="text-left">Nº</th>
            <th width="5%" class="text-left">Tipo</th>
        </tr>
    </thead>

    @php
    $soma = 0;
    $totalPorPagamento = [];
    $pagamentos = [
        'Dinheiro' => '01',
        'Cheque' => '02',
        'Cartão de Crédito' => '03',
        'Cartão de Débito' => '04',
        'Crédito Loja' => '05',
        'Crediário' => '06',
        'Vale Alimentação' => '10',
        'Vale Refeição' => '11',
        'Vale Presente' => '12',
        'Vale Combustível' => '13',
        'Duplicata Mercantil' => '14',
        'Boleto Bancário' => '15',
        'Depósito Bancário' => '16',
        'Pagamento Instantâneo (PIX)' => '17',
        'Sem Pagamento' => '90',
        'Outros' => '99',
    ];

    foreach ($pagamentos as $nome => $codigo) {
        $totalPorPagamento[$nome] = 0;
    }
    @endphp

    <tbody>
        @foreach($data as $key => $d)
        @php
            // Lógica de Pagamento
            $tipoPagamento = $d['tipo_pagamento'] ?? null;
            $descricaoPagamento = array_search($tipoPagamento, $pagamentos) ?: 'Outros/Não Inf.';
            
            // Soma Geral e por Tipo (Apenas se o estado não for cancelado, se desejar filtrar)
            $soma += $d['valor_total'];
            if (isset($totalPorPagamento[$descricaoPagamento])) {
                $totalPorPagamento[$descricaoPagamento] += $d['valor_total'];
            } else {
                $totalPorPagamento['Outros'] += $d['valor_total'];
            }
        @endphp
        <tr class="@if($key%2 == 0) pure-table-odd @endif">
            <td>{{ $d['cliente'] == '' ? 'Consumidor Final' : $d['cliente'] }}</td>
            <td>{{ $d['data'] }}</td>
            <td>{{ strtoupper($d['estado']) }}</td>
            <td>{{ $descricaoPagamento }}</td>
            <td style="font-size: 10px;">{{ $d['chave'] }}</td>
            <td>R$ {{ number_format($d['valor_total'], 2, ',', '.') }}</td>
            <td>{{ $d['numero'] }}</td>
            <td>{{ strtoupper($d['tipo']) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table style="width: 100%; margin-top: 20px;">
    <tbody>
        <tr class="text-left">
            <th width="50%">Total de documentos: {{ count($data) }}</th>
            <th width="50%" style="font-size: 18px;">Soma Total: R$ {{ number_format($soma, 2, ',', '.') }}</th>
        </tr>
    </tbody>
</table>

<div style="page-break-inside: avoid;">
    <h3 style="margin-top: 20px;">Resumo por Forma de Pagamento</h3>
    <table class="table-sm" style="width: 50%; border-top: 1px solid #ccc;">
        <thead>
            <tr>
                <th class="text-left">Forma de Pagamento</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($totalPorPagamento as $forma => $total)
                @if($total > 0)
                <tr>
                    <td>{{ $forma }}</td>
                    <td class="text-right">R$ {{ number_format($total, 2, ',', '.') }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>

@endsection