@extends('relatorios.default')
@section('content')

@if($data_inicial && $data_final)
<p>Periodo: {{$data_inicial}} - {{$data_final}}</p>
@endif

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="45%" class="text-left">Tipo</th>
		<th width="15%" class="text-left">Total</th>
	</tr>
</thead>

@foreach($somaTiposPagamento as $key => $v)

<tr class="@if($key%2 == 0) pure-table-odd @endif">

	<td>{{App\Models\VendaCaixa::getTipoPagamento($key)}}</td>
	<td>{{number_format($v, 2, ',', '.')}}</td>
</tr>

@endforeach
</table>
@endsection
