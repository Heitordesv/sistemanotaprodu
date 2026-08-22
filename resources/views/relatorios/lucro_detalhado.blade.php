@extends('relatorios.default')
@section('content')

@if($data_inicial)
<h6>Data: {{$data_inicial}}</h6>
@endif
<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="10%" class="text-left">Horário</th>
		<th width="10%" class="text-left">Local</th>
		<th width="30%" class="text-left">Cliente</th>
		<th width="20%" class="text-left">Venda / Compra</th>
		<th width="15%" class="text-left">Lucro</th>
		<th width="15%" class="text-left">%Lucro</th>
	</tr>
</thead>

@php
$somaLucro = 0;
$somaPerc = 0;
@endphp
@foreach($lucros as $key => $v)
<tr class="@if($key%2 == 0) pure-table-odd @endif">
	<td>{{$v['horario']}}</td>
	<td>{{$v['local']}}</td>
	<td>{{$v['cliente']}}</td>
	<td>{{__moeda($v['valor_venda'], 2)}} / {{__moeda($v['valor_compra'], 2)}}</td>
	<td>{{__moeda($v['lucro'], 2)}}</td>
	<td>{{$v['lucro_percentual']}}</td>
</tr>

@php
$somaLucro += $v['lucro'];
$somaPerc += (float)($v['lucro_percentual']);
@endphp
@endforeach
</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="25%">Total lucro</th>
			<th width="25%"><strong>R$ {{__moeda($somaLucro, 2, ',', '.')}}</strong></th>
			<th width="25%">% médio</th>
			<th width="25%"><strong>{{__moeda(($somaPerc/sizeof($lucros)), 2, ',', '.')}}%</strong></th>
		</tr>
	</tbody>
</table>

@endsection
