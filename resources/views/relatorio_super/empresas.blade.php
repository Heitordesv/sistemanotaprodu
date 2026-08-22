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

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="10%" class="text-left">Data de cad.</th>
		<th width="20%" class="text-left">Nome da empresa</th>
		<th width="15%" class="text-left">Nome do responsável</th>
		<th width="10%" class="text-left">Telefone</th>
		<th width="15%" class="text-left">Contador</th>
		<th width="10%" class="text-left">Status</th>
		<th width="10%" class="text-left">Plano</th>
		<th width="10%" class="text-left">Valor</th>
	</tr>
</thead>
@php
$soma = 0;
@endphp
@foreach($empresas as $key => $e)
<tr class="@if($key%2 == 0) pure-table-odd @endif">

	<td>{{\Carbon\Carbon::parse($e->created_at)->format('d/m/Y')}}</td>
	<td>{{$e->nome}}</td>
	<td>{{$e->usuarioFirst ? $e->usuarioFirst->nome : ''}}</td>
	<td>{{$e->telefone}}</td>
	<td>{{$e->info_contador == '' ? '--' : $e->info_contador }}</td>
	<td>

		@if($e->status() == -1)
		<span class="label label-xl label-inline label-light-info">
			MASTER
		</span>

		@elseif($e->status() && $e->tempo_expira >= 0)
		<span class="label label-xl label-inline label-light-success">
			ATIVO
		</span>
		@else

		@if(!$e->planoEmpresa)
		<span class="label label-xl label-inline label-light-danger">
			DESATIVADO
		</span>
		@else

		@if($e->planoEmpresa->expiracao == '0000-00-00' && $e->status())
		<span class="label label-xl label-inline label-light-success">
			ATIVO
		</span>
		@else
		<span class="label label-xl label-inline label-light-danger">
			DESATIVADO
		</span>
		@endif
		@endif
		@endif

	</td>
	<td>

		@if($e->planoEmpresa)
		{{$e->planoEmpresa->plano->nome}}
		@else
		--
		@endif
	</td>
	<td>

		@if($e->planoEmpresa)
		{{number_format($e->planoEmpresa->getValor(), 2, ',', '.')}}
		@else
		--
		@endif

	</td>
</tr>	
@php
if($e->planoEmpresa)
$soma += $e->planoEmpresa->getValor();
@endphp
@endforeach
<table style="width: 100%;">
	<tbody>
		<tr class="text-left">

			<th width="50%">Total de empresas: {{ sizeof($empresas) }}</th>
			<th width="50%">Soma: R$ {{ number_format($soma, 2, ',', '.') }}</th>
		</tr>
	</tbody>
</table>

@endsection
