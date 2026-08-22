<!DOCTYPE html>
<html lang="pt-BR">

<head>
<meta charset="UTF-8">

<title>Nova Venda Realizada</title>

<style>

body{
    margin:0;
    padding:20px;
    background:#f3f4f6;
    font-family:Arial, Helvetica, sans-serif;
    color:#333;
}


.container{
    max-width:750px;
    margin:auto;
    background:#fff;
    border-radius:10px;
    overflow:hidden;
    border:1px solid #ddd;
}


.header{
    background:#2563eb;
    color:white;
    padding:25px;
    text-align:center;
    font-size:22px;
    font-weight:bold;
}


.content{
    padding:25px;
}


table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:25px;
}


th{
    background:#f1f5f9;
    padding:10px;
    border:1px solid #ddd;
}


td{
    padding:10px;
    border:1px solid #ddd;
    font-size:14px;
}


.info td{
    border:none;
}


h3{
    color:#2563eb;
}


.total{

    background:#ecfdf5;
    padding:20px;
    text-align:right;
    border-radius:8px;

}


.total strong{

    color:#16a34a;
    font-size:22px;

}


.footer{

    background:#f8fafc;
    text-align:center;
    padding:15px;
    font-size:12px;

}

</style>


</head>


<body>


@php

$formas = [

'01'=>'Dinheiro',
'02'=>'Cheque',
'03'=>'Cartão de Crédito',
'04'=>'Cartão de Débito',
'05'=>'Crédito Loja',
'06'=>'Crediário / Prazo',
'10'=>'Vale Alimentação',
'11'=>'Vale Refeição',
'12'=>'Vale Presente',
'13'=>'Vale Combustível',
'14'=>'Duplicata',
'15'=>'Boleto',
'16'=>'Depósito',
'17'=>'PIX',
'19'=>'PIX QRCode',
'90'=>'Sem pagamento',
'99'=>'Outros'

];


@endphp



<div class="container">


<div class="header">

🛒 Nova Venda Realizada

<br>

Venda #{{ $vendaCaixa->id }}

</div>



<div class="content">


<table class="info">

<tr>

<td>

<b>Empresa:</b>

{{ $empresa->nome ?? '' }}

</td>


<td>

<b>Data:</b>

{{ \Carbon\Carbon::parse($vendaCaixa->created_at)->format('d/m/Y H:i') }}

</td>


</tr>


<tr>

<td colspan="2">

<b>Cliente:</b>

{{ $vendaCaixa->nome ?? 'Consumidor Final' }}


@if($vendaCaixa->cpf)

<br>

CPF/CNPJ:
{{ $vendaCaixa->cpf }}

@endif


</td>

</tr>


</table>





<h3>📦 Produtos</h3>


<table>


<thead>

<tr>

<th>Produto</th>
<th>Qtd</th>
<th>Valor</th>
<th>Total</th>

</tr>

</thead>


<tbody>


@foreach($vendaCaixa->itens ?? [] as $item)


<tr>


<td>

{{ $item->produto->nome ?? 'Produto '.$item->produto_id }}

</td>


<td>

{{ number_format($item->quantidade,2,',','.') }}

</td>


<td>

R$ {{ number_format($item->valor,2,',','.') }}

</td>


<td>

R$ {{ number_format($item->quantidade * $item->valor,2,',','.') }}

</td>


</tr>


@endforeach


</tbody>


</table>







<h3>💳 Pagamento</h3>



<table>


<thead>

<tr>

<th>Forma</th>

<th>Valor</th>

</tr>

</thead>



<tbody>



@if(isset($vendaCaixa->faturas) && count($vendaCaixa->faturas) > 0)


@foreach($vendaCaixa->faturas as $fatura)


<tr>


<td>

{{ $formas[$fatura->forma_pagamento] ?? 'Código '.$fatura->forma_pagamento }}

</td>


<td>

R$ {{ number_format($fatura->valor,2,',','.') }}

</td>


</tr>


@endforeach



@elseif(isset($vendaCaixa->fatura) && count($vendaCaixa->fatura) > 0)


@foreach($vendaCaixa->fatura as $fatura)


<tr>

<td>

{{ $formas[$fatura->forma_pagamento] ?? 'Código '.$fatura->forma_pagamento }}

</td>


<td>

R$ {{ number_format($fatura->valor,2,',','.') }}

</td>

</tr>


@endforeach



@else


<tr>


<td>

{{ $formas[$vendaCaixa->tipo_pagamento] ?? 'Código '.$vendaCaixa->tipo_pagamento }}

</td>


<td>

R$ {{ number_format($vendaCaixa->valor_total,2,',','.') }}

</td>


</tr>


@endif



</tbody>


</table>







<div class="total">


@if($vendaCaixa->desconto > 0)

<p>

Desconto:
- R$ {{ number_format($vendaCaixa->desconto,2,',','.') }}

</p>

@endif



@if($vendaCaixa->acrescimo > 0)


<p>

Acréscimo:
+ R$ {{ number_format($vendaCaixa->acrescimo,2,',','.') }}

</p>


@endif



<strong>


TOTAL:

R$ {{ number_format($vendaCaixa->valor_total,2,',','.') }}


</strong>


</div>




</div>



<div class="footer">

NFE NOTAS - Sistema de Gestão

</div>



</div>


</body>


</html>