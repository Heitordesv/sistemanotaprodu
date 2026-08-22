@extends('relatorios.default')
@section('content')
<div class="row">
	<div class="col s12">
		<h4 class="center-align">Relátorio de Movimentações Produto: 
			<strong style="color: #8950FC">{{$produto->nome}}</strong>
		</h4>
	</div>

	<table class="table-sm table-borderless"
	style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
	<thead>
		<tr>
			<th width="200">TIPO</th>
			<th width="100">QUANTIDADE</th>
			<th width="100">VALOR</th>
			<th width="100">DATA</th>
			<!-- <th width="150">ITENS VENDIDOS</th> -->
		</tr>
	</thead>

	<tbody>
		@foreach($movimentacoes as $key => $m)
		<tr class="@if($key%2 == 0) pure-table-odd @endif">
			<td>{{$m['tipo']}}</td>
			<td>{{number_format($m['quantidade'], 2, ',', '.')}}</td>
			<td>{{number_format($m['valor'], 2, ',', '.')}}</td>
			<td>
				{{ \Carbon\Carbon::parse($m['data'])->format('d/m/Y H:i:s') }}
			</td>
		</tr>
		@endforeach
	</tbody>
</table>


</div>

@endsection
