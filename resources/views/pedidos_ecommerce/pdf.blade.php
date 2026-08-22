<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

body{
font-family: Arial, Helvetica, sans-serif;
font-size:11px;
}

table{
width:100%;
border-collapse:collapse;
}

td, th{
border:1px solid #000;
padding:4px;
}

.sem-borda{
border:none;
}

.center{
text-align:center;
}

.right{
text-align:right;
}

.bold{
font-weight:bold;
}

.titulo{
font-size:16px;
font-weight:bold;
text-align:center;
}

</style>

</head>

<body>

{{-- CABEÇALHO --}}
<table>
<tr>

<td width="60%">

<b>{{$pedido->empresa->razao_social}}</b><br>

CNPJ: {{$pedido->empresa->cpf_cnpj}}<br>

{{$pedido->empresa->rua}}, {{$pedido->empresa->numero}}<br>

{{$pedido->empresa->cidade->nome ?? ''}} - {{$pedido->empresa->cidade->uf ?? ''}}<br>

CEP: {{$pedido->empresa->cep}}

</td>

<td width="40%" class="center">

<div class="titulo">

DECLARAÇÃO DE CONTEÚDO

</div>

<br>

PEDIDO Nº {{$pedido->id}}

<br>

DATA

<br>

{{date('d/m/Y',strtotime($pedido->created_at))}}

</td>

</tr>
</table>


<br>

{{-- CODIGO --}}
<table>
<tr>

<td width="30%" class="center">

<img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=PEDIDO-{{$pedido->id}}">

</td>

<td width="70%" class="center">

<img src="https://barcode.tec-it.com/barcode.ashx?data={{$pedido->id}}&code=Code128&dpi=96">

<br>

<b>{{$pedido->id}}</b>

</td>

</tr>
</table>

<br>

{{-- DESTINATARIO --}}
<table>

<tr>
<td colspan="4" class="bold">DESTINATÁRIO</td>
</tr>

<tr>

<td width="50%">

Nome<br>

<b>{{$pedido->cliente->nome ?? ''}} {{$pedido->cliente->sobre_nome ?? ''}}</b>

</td>

<td width="25%">

Telefone<br>

{{$pedido->cliente->telefone ?? ''}}

</td>

<td width="25%">

CEP<br>

{{$pedido->endereco->cep ?? ''}}

</td>

</tr>

<tr>

<td colspan="2">

Endereço<br>

{{$pedido->endereco->rua ?? ''}}, {{$pedido->endereco->numero ?? ''}}

</td>

<td>

Bairro<br>

{{$pedido->endereco->bairro ?? ''}}

</td>

<td>

Cidade<br>

{{$pedido->endereco->cidade ?? ''}} - {{$pedido->endereco->estado ?? ''}}

</td>

</tr>

</table>


<br>

{{-- PRODUTOS --}}
<table>

<tr class="bold center">

<td width="10%">CÓD</td>
<td width="40%">DESCRIÇÃO</td>
<td width="10%">QTD</td>
<td width="20%">VALOR</td>
<td width="20%">TOTAL</td>

</tr>

@php
$total = 0;
@endphp

@foreach($pedido->itens as $item)

@php

$valor = $item->produto->valor ?? 0;
$qtd = $item->quantidade;
$subtotal = $valor * $qtd;
$total += $subtotal;

@endphp

<tr>

<td class="center">{{$item->produto->id ?? ''}}</td>

<td>{{$item->produto->descricao ?? 'Produto'}}</td>

<td class="center">{{$qtd}}</td>

<td class="right">
R$ {{number_format($valor,2,',','.')}}
</td>

<td class="right">
R$ {{number_format($subtotal,2,',','.')}}
</td>

</tr>

@endforeach

</table>


<br>

{{-- TOTAIS --}}
<table>

<tr>

<td width="70%" class="bold">

FORMA DE PAGAMENTO

<br>

{{$pedido->forma_pagamento}}

</td>

<td width="30%" class="right bold">

VALOR TOTAL

<br>

R$ {{number_format($total,2,',','.')}}

</td>

</tr>

</table>


<br>

{{-- DECLARAÇÃO --}}
<table>

<tr>

<td>

Declaro que os produtos acima descritos não possuem conteúdo ilícito e são verdadeiros.

<br><br><br>

____________________________________

<br>

Assinatura do remetente

</td>

</tr>

</table>


</body>
</html>