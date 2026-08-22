@extends('relatorios.default')
@section('content')

<h5>Período: <strong>{{ $data_inicial }} - {{ $data_final }}</strong></h5>

<table class="table-sm table-borderless" 
       style="border-bottom: 1px solid #ccc; margin-bottom:10px; width:100%; font-size:13px;">
<thead style="background:#f8f9fa;">
    <tr>
        <th class="text-left">Data de cadastro</th>
        <th class="text-left">CNPJ</th>
        <th class="text-left">Empresa</th> 
        <th class="text-left">Telefone</th>
        <th class="text-left">Ordens de Serviço</th>
        <th class="text-left">Acessos</th>
        <th class="text-left">Total NFe</th>
        <th class="text-left">Total NFCe</th>
        <th class="text-left">Total Clientes</th>
        <th class="text-left">Total Produtos</th>
        <th class="text-left">Valor de Vendas (R$)</th>
        <th class="text-left">Plano</th>
        <th class="text-left">Valor Plano (R$)</th>
    </tr>
</thead>

<tbody>
@php
    $totalOrdemServico = 0;
    $totalAcessos = 0;
    $totalNFe = 0;
    $totalNFCe = 0;
    $totalClientes = 0;
    $totalProdutos = 0;
    $totalVendas = 0;
    $totalPlanos = 0;
@endphp

@foreach($data as $key => $item)
<tr style="border-bottom: 1px solid #eee;">
    <td>{{ $item['data_cadastro'] }}</td>
    <td>{{ $item['cpf_cnpj'] }}</td>
    <td>{{ $item['empresa'] }}</td>
    <td>{{ $item['telefone'] }}</td>        
    <td>{{ $item['ordenservico'] }}</td>
    <td>{{ $item['acessos'] }}</td>
    <td>{{ $item['nfes'] }}</td>
    <td>{{ $item['nfces'] }}</td>
    <td>{{ $item['clientes'] }}</td>
    <td>{{ $item['produtos'] }}</td>
    <td>{{ number_format($item['bruto'], 2, ',', '.') }}</td>
    <td>{{ $item['plano_nome'] }}</td>
    <td>{{ number_format($item['plano_valor'], 2, ',', '.') }}</td>
</tr>

@php
    $totalOrdemServico += $item['ordenservico'];
    $totalAcessos += $item['acessos'];
    $totalNFe += $item['nfes'];
    $totalNFCe += $item['nfces'];
    $totalClientes += $item['clientes'];
    $totalProdutos += $item['produtos'];
    $totalVendas += $item['bruto'];
    $totalPlanos += $item['plano_valor'];
@endphp
@endforeach

<tr style="font-weight:bold; border-top:2px solid #000; background:#f1f1f1;">
    <td colspan="4" class="text-right">TOTAL GERAL:</td>
    <td>{{ $totalOrdemServico }}</td>
    <td>{{ $totalAcessos }}</td>
    <td>{{ $totalNFe }}</td>
    <td>{{ $totalNFCe }}</td>
    <td>{{ $totalClientes }}</td>
    <td>{{ $totalProdutos }}</td>
    <td>{{ number_format($totalVendas, 2, ',', '.') }}</td>
    <td></td>
    <td>{{ number_format($totalPlanos, 2, ',', '.') }}</td>
</tr>
</tbody>
</table>

<h5>Total de registros: <strong>{{ count($data) }}</strong></h5>

@endsection
