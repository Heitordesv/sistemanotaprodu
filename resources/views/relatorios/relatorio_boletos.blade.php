@extends('relatorios.default')
@section('content')
@if($data_inicial && $data_final)
<p>Periodo: {{$data_inicial}} - {{$data_final}}</p>
@endif

@if($status)
<p>Status: <strong>{{strtoupper($status)}}</strong></p>
@endif
<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="30%" class="text-left">Cliente</th>
		<th width="10%" class="text-left">Valor</th>
		<th width="10%" class="text-left">Categoria</th>
		<th width="20%" class="text-left">Estado</th>
		<th width="15%" class="text-left">Banco</th>
		<th width="15%" class="text-left">Nº Boleto</th>
	</tr>
</thead>
@php
$somaPendente = 0;
$somaRecebido = 0;
@endphp
<tbody>
	@foreach($contas as $key => $c)
	<tr class="@if($key%2 == 0) pure-table-odd @endif">
		<td>
			@if($c->venda_id != null || $c->venda_caixa_id != null)
			@if($c->venda_id != null)
			{{ $c->venda->cliente->razao_social }}
			@else
			@if($c->vendaCaixa->cliente)
			{{ $c->vendaCaixa->cliente->razao_social }}
			@else
			--
			@endif
			@endif
			@else
			@if($c->cliente_id != null)
			{{ $c->cliente->razao_social }}
			@else
			--
			@endif
			@endif
		</td>
		<td>{{ number_format($c->valor_integral, 2, ',', '.') }}</td>
		<td>{{ $c->categoria->nome }}</td>
		<td>
			@if($c->status == true)
			Recebido
			@else
			Pendente
			@endif
		</td>
		<td>
			{{ $c->boleto->banco->banco }}
		</td>
		<td>
			{{ $c->boleto->numero }}
		</td>
	</tr>

	@php
	if($c->status)
	$somaRecebido += $c->valor_integral;
	else
	$somaPendente += $c->valor_integral;

	@endphp

	@endforeach
</tbody>

</table>

<table style="width: 100%;">
	<tbody>
		<tr>
			<th>Soma Pendente: <strong style="color: indianred;">R$ {{number_format($somaPendente, 2, ',', '.')}}</strong></th>
			<th>Soma Recebido: <strong style="color: mediumseagreen;">R$ {{number_format($somaRecebido, 2, ',', '.')}}</strong></th>
		</tr>

	</tbody>
</table>

@endsection
