@extends('relatorios.default')
@section('content')
<style type="text/css">

	.tr-h{
		background: #C9F7F5;
	}
</style>

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
@if($data_inicial && $data_final)
<caption>Período: {{$data_inicial}} - {{$data_final}}</caption>
@endif

@php
$somaGeral = 0;
$somaFornecedor = 0;
$fornecedor_id = $compras[0]->fornecedor_id;
@endphp

@for($aux=0; $aux<sizeof($compras); $aux++)

@php
if($fornecedor_id != $compras[$aux]->fornecedor_id){
	$somaFornecedor = 0;
}
@endphp
<tr style="background: #C9F7F5">
	<td>Data</td>
	<td>Fornecedor</td>
	<td>Número NFe</td>
	<td>Total</td>
</tr>
<tr>
	<td>{{ \Carbon\Carbon::parse($compras[$aux]->created_at)->format('d/m/Y H:i')}}</td>
	<td>{{ $compras[$aux]->fornecedor->razao_social }}</td>
	<td>{{ $compras[$aux]->nf ?? '--' }}</td>
	<td>R$ {{ number_format($compras[$aux]->valor, 2, ',', '.')}}</td>
</tr>

<tr style="background: #EEE5FF">
	<td>Produto</td>
	<td>CFOP</td>
	<td>Quantidade</td>
	<td>Vl unitário</td>
	<td>Subtotal</td>
</tr>
@foreach($compras[$aux]->itens as $p)
<tr>
	<td>{{$p->produto->nome}}</td>
	<td>{{$p->cfop_entrada}}</td>
	<td>{{ number_format($p->quantidade, 2)}}</td>
	<td>{{ number_format($p->valor_unitario, 2, ',', '.')}}</td>
	<td>{{ number_format($p->quantidade*$p->valor_unitario, 2, ',', '.')}}</td>
</tr>

@endforeach

@php
$somaGeral += $compras[$aux]->valor;
$somaFornecedor += $compras[$aux]->valor;
@endphp

@isset($compras[$aux+1]->fornecedor_id)
@if($compras[$aux+1]->fornecedor_id != $fornecedor_id)
@php
$fornecedor_id = $compras[$aux]->fornecedor_id;
@endphp
<tr style="background: #EE2D41">
<td>Total:</td>
<td>R$ {{ number_format($somaFornecedor, 2, ',', '.')}}</td>
</tr>
@endif
@else
<tr style="background: #EE2D41">
<td>Total:</td>
<td>R$ {{ number_format($somaFornecedor, 2, ',', '.')}}</td>
</tr>
@endif

@endfor

</table>

<h4>Soma Geral: <strong style="color: #1BC5BD">R$ {{ number_format($somaGeral, 2, ',', '.') }}</strong></h4>
@endsection
