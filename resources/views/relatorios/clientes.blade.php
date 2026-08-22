@extends('relatorios.default')
@section('content')



<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="45%" class="text-left">Razão social</th>
		<th width="15%" class="text-left">CPF/CNPJ</th>
		<th width="15%" class="text-left">Rua</th>
		<th width="15%" class="text-left">Número</th>
		<th width="15%" class="text-left">Bairro</th>
		<th width="15%" class="text-left">Cidade</th>
		<th width="15%" class="text-left">Telefone</th>
		<th width="15%" class="text-left">Dt. cadastro</th>
	</tr>
</thead>
@foreach($data as $key => $item)

<tr class="@if($key%2 == 0) pure-table-odd @endif">

	<td>{{ $item->razao_social }}</td>
	<td>{{ $item->cpf_cnpj }}</td>
	<td>{{ $item->rua }}</td>
	<td>{{ $item->numero }}</td>
	<td>{{ $item->bairro }}</td>
	<td>{{ $item->cidade->info }}</td>
	<td>{{ $item->telefone }}</td>
	<td>{{ __data_pt($item->created_at, 0) }}</td>
	
</tr>

@endforeach
</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="15%">Total de clientes</th>
			<th width="15%"><strong>{{sizeof($data)}}</strong></th>
		</tr>
	</tbody>
</table>

@endsection
