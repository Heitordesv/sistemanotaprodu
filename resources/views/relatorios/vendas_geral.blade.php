@extends('relatorios.default')
@section('content')

<h3 style="margin-bottom: 5px;">Relatório de Vendas Geral</h3>
<p style="margin-bottom: 15px;">
    Período: <strong>{{ $data_inicial }}</strong> até <strong>{{ $data_final }}</strong>
</p>

<table width="100%" border="1" cellspacing="0" cellpadding="5" style="font-size: 11px; border-collapse: collapse;">
    <thead style="background-color: #f2f2f2;">
        <tr>
            <th>Data</th>
            <th>ID</th>
            <th>Cliente</th>
            <th>Tipo</th>
            <th>Pagamento</th>
            <th>Desconto</th>
            <th>Valor Líquido</th>
            <th>Total</th>
        </tr>
    </thead>

    <tbody>
    @php
        $total = 0;
        $totalLiquido = 0;
        $desconto = 0;
    @endphp

    @forelse($vendas as $v)
        <tr>
            <td>{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') }}</td>
            <td>{{ $v->id }}</td>
            <td>{{ $v->cliente ?? 'Consumidor final' }}</td>
            <td>{{ strtoupper($v->tipo ?? '-') }}</td>
            <td>{{ $v->tipo_pagamento ?? '-' }}</td>

            <td style="text-align:right;">
                R$ {{ number_format($v->desconto ?? 0, 2, ',', '.') }}
            </td>

            <td style="text-align:right;">
                R$ {{ number_format($v->valor_liquido ?? 0, 2, ',', '.') }}
            </td>

            <td style="text-align:right;">
                R$ {{ number_format($v->valor_total ?? 0, 2, ',', '.') }}
            </td>
        </tr>

        @php
            $total += $v->valor_total ?? 0;
            $totalLiquido += $v->valor_liquido ?? 0;
            $desconto += $v->desconto ?? 0;
        @endphp

    @empty
        <tr>
            <td colspan="8" style="text-align:center;">
                Nenhum registro encontrado
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

<br><br>

{{-- RESUMO --}}
<table width="100%" style="font-size: 12px;">
    <tr>
        <td><strong>Total Bruto:</strong> R$ {{ number_format($total, 2, ',', '.') }}</td>
        <td><strong>Total Líquido:</strong> R$ {{ number_format($totalLiquido, 2, ',', '.') }}</td>
    </tr>
    <tr>
        <td><strong>Total Desconto:</strong> R$ {{ number_format($desconto, 2, ',', '.') }}</td>
        <td></td>
    </tr>
</table>

<br>

{{-- RESUMO POR PAGAMENTO --}}
@if(isset($resumo))
    <h4>Resumo por Forma de Pagamento</h4>

    <table width="50%" border="1" cellspacing="0" cellpadding="5" style="font-size: 11px;">
        <thead style="background:#f2f2f2;">
            <tr>
                <th>Forma</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumo as $tipo => $valor)
                <tr>
                    <td>{{ $tipo }}</td>
                    <td style="text-align:right;">
                        R$ {{ number_format($valor, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection