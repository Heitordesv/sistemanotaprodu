@extends('relatorios.default')
@section('content')

@if($data_inicial && $data_final)
<p>Periodo: {{$data_inicial}} - {{$data_final}}</p>
@endif

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="45%" class="text-left">Código</th>
		<th width="15%" class="text-left">Descrição</th>
		<th width="15%" class="text-left">Vl. custo</th>
		<th width="15%" class="text-left">Vl. venda</th>
		<th width="15%" class="text-left">Quantidade</th>
				<th width="15%" class="text-left">Cfop</th>

		<th width="15%" class="text-left">Total custo/venda</th>
	</tr>
</thead>

@php
$somaCusto = 0;
$somaVenda = 0;
$somaItens = 0;
@endphp

@foreach($itens as $key => $i)

<tr class="@if($key%2 == 0) pure-table-odd @endif">

	<td>{{$i['id']}}</td>
	<td>
		{{$i['nome']}} 
		@if($i['grade'])
		{{$i['str_grade']}}
		@endif
	</td>
	<td>{{number_format($i['valor_compra'], 2, ',', '.')}}</td>
	<td>{{number_format($i['valor_venda'], 2, ',', '.')}}</td>
	@if($i['unidade'] == 'UN')
	<td>{{number_format($i['total'])}} {{$i['unidade']}}</td>
	@else
	<td>{{number_format($i['total'], 2)}} {{$i['unidade']}}</td>

	@endif

	<td>
		{{number_format($i['valor_compra']*$i['total'], 2, ',', '.')}}/{{number_format($i['valor_venda']*$i['total'], 2, ',', '.')}}
	</td>
</tr>
@php
$somaCusto += $i['valor_compra']*$i['total'];
$somaVenda += $i['valor_venda']*$i['total'];
$somaItens += $i['total'];
@endphp
@endforeach

</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="15%">Soma custo</th>
			<th width="15%"><strong>R$ {{number_format($somaCusto, 2, ',', '.')}}</strong></th>
			<th width="15%">Soma venda</th>
			<th width="15%"><strong>R$ {{number_format($somaVenda, 2, ',', '.')}}</strong></th>
			<th width="15%">Soma itens</th>
			<th width="15%"><strong>{{ $somaItens }}</strong></th>
		</tr>
	</tbody>
</table>

@endsection
