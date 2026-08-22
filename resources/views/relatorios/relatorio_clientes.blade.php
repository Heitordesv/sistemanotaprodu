@extends('relatorios.default')
@section('content')

@if($data_inicial && $data_final)
<p>Período: {{$data_inicial}} - {{$data_final}}</p>
@endif
<p>Relatório de Vendas {{$ordem}} Vendidos</p>
<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="50%" class="text-left">Cliente</th>
		<th width="25%" class="text-left">Qtd. de vendas</th>
		<th width="25%" class="text-left">Total</th>
	</tr>
</thead>

@php
$soma = 0;
@endphp
@foreach($vendas as $key => $v)
<tr class="@if($key%2 == 0) pure-table-odd @endif">

	<td>{{$v->nome}}</td>
	<td>{{number_format($v->total, 0)}}</td>
	<td>R$ {{number_format($v->total_dinheiro, 2, ',', '.')}}</td>
</tr>

@php
$soma += $v->total_dinheiro;
@endphp
@endforeach
</table>

</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="50%"></th>
			<th width="25%">Soma</th>
			<th width="25%"><strong>R$ {{number_format($soma, 2, ',', '.')}}</strong></th>
		</tr>
	</tbody>
</table>


@endsection
