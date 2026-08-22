@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="45%" class="text-left">#</th>
		<th width="15%" class="text-left">Total</th>
	</tr>
</thead>

@php
$soma = 0; $inc = 0;
@endphp

@foreach($vendas as $key => $v)

@foreach($v['itens'] as $i)
<tr style="background: #e8eaf6">
	<td>{{$i->quantidade}} x {{$i->produto->nome}} {{$i->produto->str_grade}} {{$i->produto->unidade_venda}} x {{number_format($i->valor, 2, ',', '.')}} = R$ {{number_format(($i->quantidade * $i->valor), 2, ',', '.')}}</td>
	<td></td>
</tr>
@endforeach
<tr>
	<td style="color: green; border-bottom: 1px solid #000;">{{($v['data'])}}</td>
	<td style="color: green; border-bottom: 1px solid #000;">{{number_format($v['total'], 2, ',', '.')}}</td>
</tr>


<?php $soma += $v['total']; $inc++;?>
@endforeach
</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="25%">Total de vendas</th>
			<th width="25%"><strong>{{$inc}}</strong></th>
			<th width="25%">Valor total</th>
			<th width="25%"><strong>R$ {{number_format($soma, 2, ',', '.')}}</strong></th>
		</tr>
	</tbody>
</table>

@endsection
