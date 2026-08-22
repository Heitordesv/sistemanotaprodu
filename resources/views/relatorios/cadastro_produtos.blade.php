@extends('relatorios.default')
@section('content')

@if($data_inicial && $data_final)
<p>Periodo: {{$data_inicial}} - {{$data_final}}</p>
@endif

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="50%" class="text-left">Produto</th>
		<th width="25%" class="text-left">Data de cadastro</th>
		<th width="25%" class="text-left">Estoque</th>
	</tr>
</thead>

@foreach($produtos as $key => $p)
<tr class="@if($key%2 == 0) pure-table-odd @endif">

	<td>{{$p->nome}}</td>
	<td>{{\Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i')}}</td>
	<td>{{$p->estoqueAtual()}}</td>
</tr>

@endforeach
</table>
@endsection
