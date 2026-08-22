@extends('relatorios.default')
@section('content')

<p>Categoria: {{$categoria}}</p>
<p>Total de registrtos: {{ sizeof($produtos) }}</p>

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="30%" class="text-left">Produto</th>
		<th width="10%" class="text-left">Estoque atual</th>
		<th width="10%" class="text-left">Custo</th>
		<th width="10%" class="text-left">Margem de lucro</th>
		<th width="10%" class="text-left">Valor de venda</th>
		<th width="10%" class="text-left">Projeção total de vendas</th>
		<th width="10%" class="text-left">Valor total em estoque</th>
		<th width="10%" class="text-left">Data da últ. compra</th>
	</tr>
</thead>

@php 
$somaEstoque = 0;
$somaValorEstoque = 0;
$somaValorCusto = 0;
@endphp

@foreach($produtos as $key => $p)

<tr class="@if($key%2 == 0) pure-table-odd @endif">
	<td>{{$p->nome}} {{$p->str_grade}}
		@if($p->referencia != "")
		| Ref: {{$p->referencia}}
		@endif
	</td>
	@if($p->unidade_venda == 'UNID' || $p->unidade_venda == 'UN')
	<td>{{number_format($p->quantidade)}} {{$p->unidade_venda}}</td>
	@else
	<td>{{number_format($p->quantidade, 3, ',', '.')}} {{$p->unidade_venda}}</td>
	@endif
	<td>R$ {{number_format($p->valor_compra, 2, ',', '.')}}</td>
	<td>{{number_format($p->percentual_lucro, 2)}}%</td>
	<td>R$ {{number_format($p->valor_venda, 2, ',', '.')}}</td>
	<td>R$ {{number_format($p->valor_venda*$p->quantidade, 2, ',', '.')}}</td>
	<td>R$ {{number_format($p->valor_compra*$p->quantidade, 2, ',', '.')}}</td>
	<td>{{$p->data_ultima_compra}}</td>
	@php 
	$somaEstoque += $p->quantidade;
	$somaValorEstoque += $p->valor_venda*$p->quantidade;
	$somaValorCusto += $p->valor_compra*$p->quantidade;
	@endphp
</tr>


@endforeach
</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="15%">Quantidade estoque</th>
			<th width="15%"><strong>{{number_format($somaEstoque, 2, ',', '.')}}</strong></th>
			<th width="15%">Soma valor de venda</th>
			<th width="15%"><strong>R$ {{number_format($somaValorEstoque, 2, ',', '.')}}</strong></th>
			<th width="15%">Soma valor de custo</th>
			<th width="15%"><strong>R$ {{number_format($somaValorCusto, 2, ',', '.')}}</strong></th>
		</tr>
	</tbody>
</table>

@endsection
