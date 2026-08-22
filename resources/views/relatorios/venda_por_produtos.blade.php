@extends('relatorios.default')
@section('content')
<style type="text/css">
	.b-top{
		border-top: 1px solid #000; 
	}
	.b-bottom{
		border-bottom: 1px solid #000; 
	}
</style>

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
		<th width="15%" class="text-left">Total custo/venda</th>
	</tr>
</thead>

@php
$somaLucro = 0;
$somaVenda = 0;
$somaCompra = 0;
@endphp
@foreach($itens as $i)
@if(sizeof($i['itens']) > 0)
<tr>
	<td>
		Data: <strong style="color: #0BB7AF">{{\Carbon\Carbon::parse($i['data'])->format('d/m/Y')}}</strong>
	</td>
</tr>
<tr>
	<td class="" style="width: 435px;">
		Nome
	</td>
	<td class="" style="width: 90px;">
		Quantidade
	</td>
	<td class="" style="width: 90px;">
		Valor venda/compra
	</td>
	<td class="" style="width: 110px;">
		Valor venda média
	</td>
	<td class="" style="width: 90px;">
		Subtotal
	</td>
	<td class="" style="width: 90px;">
		Lucro
	</td>
</tr>	
@foreach($i['itens'] as $d)
<tr>
	<th class="b-top">{{$d['produto']->nome}} {{$d['produto']->str_grade}}</th>
	<th class="b-top">{{number_format($d['quantidade'], 2)}}</th>
	<th class="b-top">
		{{number_format($d['valor'], 2, ',', '.')}}/
		{{number_format($d['valor_custo'], 2, ',', '.')}}
	</th>
	<th class="b-top">{{number_format($d['media'], 2, ',', '.')}}</th>
	<th class="b-top">{{number_format($d['subtotal'], 2, ',', '.')}}</th>
	<th class="b-top">
		{{number_format(
		$d['subtotal'] - ($d['quantidade']*$d['produto']->valor_compra)
		, 2, ',', '.')
	}}
</th>
</tr>
@php
$somaLucro += $d['subtotal'] - ($d['quantidade']*$d['produto']->valor_compra);
$somaVenda += $d['media'] * $d['quantidade'];
$somaCompra += $d['produto']->valor_compra * $d['quantidade'];
@endphp
@endforeach
@endif
@endforeach

</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="15%">Soma compra</th>
			<th width="15%"><strong>R$ {{number_format($somaCompra, 2, ',', '.')}}</strong></th>
			<th width="15%">Soma venda</th>
			<th width="15%"><strong>R$ {{number_format($somaVenda, 2, ',', '.')}}</strong></th>
			<th width="15%">Soma lucro</th>
			<th width="15%"><strong>R$ {{number_format($somaLucro, 2, ',', '.')}}</strong></th>
		</tr>
	</tbody>
</table>


@endsection
