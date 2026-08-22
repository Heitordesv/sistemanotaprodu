@extends('relatorios.default')
@section('content')

<br>

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px; width: 100%; font-size: 12px;">
<thead>
	<tr>
		<th width="10%" class="text-left">Data</th>
		<th width="8%" class="text-left">Id</th>
		<th width="14%" class="text-left">Vendedor</th>
		<th width="18%" class="text-left">Cliente</th>
		<th width="12%" class="text-left">Forma Pgto</th>
		<th width="12%" class="text-left">Desp. Op.</th>
		<th width="10%" class="text-left">Desconto</th>
		<th width="10%" class="text-left">Vl. Líquido</th>
		<th width="10%" class="text-left">Total</th>
	</tr>
</thead>

@php
$somaPedido = 0;
$somaPedidoLiquido = 0;
$somaPdv = 0;
$somaDesconto = 0;
@endphp

<tbody>
@foreach($vendas as $key => $v)

@php
$isPdv = isset($v->cpf);
@endphp

<tr style="background-color: {{ $key % 2 == 0 ? '#f9f9f9' : '#ffffff' }}">
	<td>{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') }}</td>
	<td>{{ $v->id }}</td>

	<td>
		{{ method_exists($v, 'vendedor') ? $v->vendedor() : '-' }}
	</td>

	<td>
		{{ optional($v->cliente)->razao_social ?? 'Consumidor final' }}
	</td>

	<td>
		{{ $isPdv 
			? (method_exists($v, 'getTipoPagamento2') ? $v->getTipoPagamento2() : '-') 
			: (method_exists($v, 'getTipoPagamento') ? $v->getTipoPagamento() : '-') 
		}}
	</td>

	@if($v->tbl == 'pdv')
		<td>R$ 0,00</td>
		<td>R$ {{ number_format($v->desconto, 2, ',', '.') }}</td>
		<td>R$ {{ number_format($v->valor_total, 2, ',', '.') }}</td>
		<td>R$ {{ number_format($v->valor_total + $v->desconto, 2, ',', '.') }}</td>
	@else
		<td>R$ {{ method_exists($v, 'valorDespesaOperacionais') ? $v->valorDespesaOperacionais() : '0,00' }}</td>
		<td>R$ {{ number_format($v->desconto, 2, ',', '.') }}</td>
		<td>R$ {{ number_format(method_exists($v, 'valorLiquido') ? $v->valorLiquido() : 0, 2, ',', '.') }}</td>
		<td>R$ {{ number_format($v->valor_total, 2, ',', '.') }}</td>
	@endif

</tr>

@php
if(!$isPdv){
	$somaPedido += $v->valor_total;
	$somaPedidoLiquido += method_exists($v, 'valorLiquido') ? $v->valorLiquido() : 0;
}else{
	$somaPdv += $v->valor_total;
}

$somaDesconto += $v->desconto;
@endphp

@endforeach
</tbody>
</table>

<br>

<table style="width: 100%; font-size: 13px;">
<tbody>
	<tr>
		<th style="text-align:left;">
			Soma Pedido: <strong>R$ {{ number_format($somaPedido, 2, ',', '.') }}</strong>
		</th>
		<th style="text-align:left;">
			Soma Pedido Líquido: <strong>R$ {{ number_format($somaPedidoLiquido, 2, ',', '.') }}</strong>
		</th>
	</tr>

	<tr>
		<th style="text-align:left;">
			Soma PDV: <strong>R$ {{ number_format($somaPdv, 2, ',', '.') }}</strong>
		</th>
		<th style="text-align:left;">
			Total Geral: <strong>R$ {{ number_format($somaPedidoLiquido + $somaPdv, 2, ',', '.') }}</strong>
		</th>
	</tr>

	<tr>
		<th style="text-align:left;">
			Soma Desconto: <strong>R$ {{ number_format($somaDesconto, 2, ',', '.') }}</strong>
		</th>
		<th></th>
	</tr>
</tbody>
</table>

@endsection