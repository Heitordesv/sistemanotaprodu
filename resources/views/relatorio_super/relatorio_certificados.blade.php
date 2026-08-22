@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>

		<th width="20%" class="text-left">Nome da empresa</th>
		<th width="20%" class="text-left">Nome do contato</th>
		<th width="10%" class="text-left">Telefone</th>
		<th width="20%" class="text-left">Contador</th>
		<th width="10%" class="text-left">Status</th>
		<th width="20%" class="text-left">Data de venc.</th>
	</tr>
</thead>

@foreach($empresas as $key => $e)
<tr class="@if($key%2 == 0) pure-table-odd @endif">

	<td>{{$e->nome}}</td>
	<td>{{$e->usuarioFirst->nome}}</td>
	<td>{{$e->telefone}}</td>
	<td>{{$e->info_contador}}</td>
	<td>{{$e->vencido ? 'Vencido' : 'À Vencer'}}</td>
	<td>{{\Carbon\Carbon::parse($e->vencimento)->format('d/m/Y')}}</td>
</tr>
@endforeach

@endsection
