@extends('relatorios.default')
@section('content')

@if($data_inicial && $data_final)
<h6>Período: {{$data_inicial}} - {{$data_final}}</h6>
@endif
<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="33%" class="text-left">Data</th>
		<th width="33%" class="text-left">Qtd. de compras</th>
		<th width="34%" class="text-left">Total</th>
	</tr>
</thead>

@php
$soma = 0;
@endphp
@foreach($compras as $key => $c)
<tr class="@if($key%2 == 0) pure-table-odd @endif">
	<td>{{\Carbon\Carbon::parse($c->data)->format('d/m/Y')}}</td>
	<td>{{number_format($c->compras_diarias)}}</td>
	<td>R$ {{number_format($c->total, 2, ',', '.')}}</td>
</tr>

@php
$soma += $c->total;
@endphp
@endforeach
</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="33%"></th>
			<th width="33%">Soma</th>
			<th width="34%"><strong>R$ {{number_format($soma, 2, ',', '.')}}</strong></th>
		</tr>
	</tbody>
</table>


@endsection
