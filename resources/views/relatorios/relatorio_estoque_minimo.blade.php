@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="45%" class="text-left">Produto</th>
		<th width="15%" class="text-left">Total disponível</th>
		<th width="15%" class="text-left">Estoque minímo</th>
		<th width="15%" class="text-left">Total a comprar</th>
		<th width="10%" class="text-left">Valor de compra ant. item</th>
	</tr>
</thead>

@php
$somaItens = 0; 
$somaValor = 0; 
@endphp

@foreach($itens as $key => $i)
<tr class="@if($key%2 == 0) pure-table-odd @endif">
	<td>{{$i['nome']}}</td>
	<td>{{number_format($i['estoque_atual'], 2, ',', '.')}}</td>
	<td>{{number_format($i['estoque_minimo'], 2, ',', '.')}}</td>
	@if($i['total_comprar'] > 0)
	<td>{{number_format($i['total_comprar'], 2, ',', '.')}}</td>
	@else
	<td>0</td>
	@endif
	<td>{{number_format($i['valor_compra'], 2, ',', '.')}}</td>

	@php 
	$somaItens++; 
	if($i['total_comprar'] > 0)
	$somaValor += $i['total_comprar'] * $i['valor_compra']; 
	@endphp

</tr>

@endforeach
</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="25%">Total de itens para comprar</th>
			<th width="25%"><strong>{{number_format($somaItens)}}</strong></th>
			<th width="25%">Soma</th>
			<th width="25%"><strong>R$ {{number_format($somaValor, 2, ',', '.')}}</strong></th>
		</tr>
	</tbody>
</table>

@endsection
