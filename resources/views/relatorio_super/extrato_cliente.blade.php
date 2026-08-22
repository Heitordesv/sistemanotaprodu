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

<h5>Período <strong>{{$data_inicial}} - {{$data_final}}</strong></h5>
<h3>{{$empresa->nome}}</h3>

@php

@endphp

<h5>Data de cadastro: {{\Carbon\Carbon::parse($empresa->created_at)->format('d/m/Y')}}</h5>
<h5>Nome do usuário: {{ $empresa->usuarioFirst->nome }}</h5>
<h5>Cidade: {{$empresa->configNota ? $empresa->configNota->municipio . " (" . $empresa->configNota->UF . ")" : $empresa->cidade}}</h5>

<h5>Telefone: {{$empresa->telefone}}</h5>
<h5>
	Plano:
	@if($empresa->planoEmpresa)
	{{$empresa->planoEmpresa->plano->nome}}
	@else
	--
	@endif
</h5>
<h5>
	Data de cancelamento:
	@if($empresa->status() == -1)
	--
	@elseif($empresa->status() && $empresa->tempo_expira >= 0)
	--
	@else

	@if(!$empresa->planoEmpresa)
	{{\Carbon\Carbon::parse($empresa->updated_at)}}
	@else

	@if($empresa->planoEmpresa->expiracao == '0000-00-00' && $empresa->status())
	--
	@else
	{{\Carbon\Carbon::parse($empresa->updated_at)}}
	@endif
	@endif
	@endif

</h5>
<h5>
	Regime tributário: 
	@if($empresa->tributacao)
	@if($empresa->tributacao->regime == 0)
	Simples
	@elseif($empresa->tributacao->regime == 1)
	Normal
	@else
	MEI
	@endif
	@else
	--
	@endif
</h5>
<h5>Email: {{$empresa->email}}</h5>
@if($empresa->contador)
<h5>Contador: {{$empresa->contador->razao_social}} | Tel: {{$empresa->contador->fone}}</h5>
@endif
@php
@endphp
<table style="width: 100%;">
	<tbody>
		<tr class="text-left">

			<th width="33%">Total de NFe: <strong>{{$totalNfe}}</strong></th>
			<th width="33%">Total de NFCe: <strong>{{$totalNfce}}</strong></th>
			<th width="33%">Total de CTe: <strong>{{$totalCte}}</strong></th>
		</tr>
	</tbody>
</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">

			<th width="33%">Total de MDFe: <strong>{{$totalMdfe}}</strong></th>
			<th width="33%">Total de usuarios: <strong>{{sizeof($empresa->usuarios)}}</strong></th>
			<th width="33%">Total de acessos: <strong>{{ $acessos }}</strong></th>
		</tr>
	</tbody>
</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">

			<th width="33%">Total de vendas peddo: <strong>{{$totalVendas}}</strong></th>
			<th width="33%">Total de vendas caixa: <strong>{{$totalizaVendasCaixa}}</strong></th>
			<th width="33%"></th>

		</tr>
	</tbody>
</table>

@endsection
