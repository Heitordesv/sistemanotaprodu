@extends('relatorios.default')
@section('content')

@if($data_inicial && $data_final)
<h6>Período: {{$data_inicial}} - {{$data_final}}</h6>
@endif
<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="25%" class="text-left">Data</th>
		<th width="25%" class="text-left">Lucro PDV</th>
		<th width="25%" class="text-left">Lucro venda Pedido NFe</th>
		<th width="25%" class="text-left">Soma</th>
	</tr>
</thead>

@php
$soma = 0;
@endphp
@foreach($lucros as $key => $v)
<tr class="@if($key%2 == 0) pure-table-odd @endif">
	<td>{{$v['data']}}</td>
	<td>{{number_format($v['valor_caixa'], 2, ',', '.')}}</td>
	<td>{{number_format($v['valor'], 2, ',', '.')}}</td>
	<td>{{number_format($v['valor'] + $v['valor_caixa'], 2, ',', '.')}}</td>
</tr>

@php
$soma += $v['valor'] + $v['valor_caixa'];
@endphp
@endforeach
</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="25%"></th>
			<th width="25%"></th>
			<th width="25%">Soma</th>
			<th width="25%"><strong>R$ {{number_format($soma, 2, ',', '.')}}</strong></th>
		</tr>
	</tbody>
</table>

@endsection
