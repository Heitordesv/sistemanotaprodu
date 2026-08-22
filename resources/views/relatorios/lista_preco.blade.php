@extends('relatorios.default')
@section('content')

@if($data)
<p>Data: {{$data}}</p>
@endif

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="45%" class="text-left">Produto</th>
		<th width="15%" class="text-left">Valor venda padrão</th>
		<th width="15%" class="text-left">Valor de compra</th>
		<th width="15%" class="text-left">Margem de venda lista</th>
		<th width="10%" class="text-left">* lucro</th>
	</tr>
</thead>
@foreach($lista->itens as $key => $i)

<tr class="@if($key%2 == 0) pure-table-odd @endif">

	<td>
		{{$i->produto->nome}}
	</td>
	<td>{{number_format($i->produto->valor_venda, 2, ',', '.')}}
	</td>
	<td>{{number_format($i->produto->valor_compra, 2, ',', '.')}}
	</td>
	<td>{{number_format($i->valor, 2, ',', '.')}}
	</td>
	<td>{{number_format($i->percentual_lucro, 2, ',', '.')}}
	</td>
</tr>

@endforeach
</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="15%">Total de itens</th>
			<th width="15%"><strong>{{sizeof($lista->itens)}}</strong></th>
		</tr>
	</tbody>
</table>

@endsection
