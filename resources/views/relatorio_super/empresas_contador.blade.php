@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>

		<th width="15%" class="text-left">Data de cadastro</th>
		<th width="30%" class="text-left">Empresa</th>
		<th width="20%" class="text-left">Plano</th>
		<th width="10%" class="text-left">Vencimento certificado</th>
		<th width="10%" class="text-left">Status</th>
	</tr>
</thead>

@foreach($empresas as $key => $e)
<tr class="@if($key%2 == 0) pure-table-odd @endif">

	<td>{{ \Carbon\Carbon::parse($e->created_at)->format('d/m/Y H:i') }}</td>
	<td>{{$e->nome}}</td>
	@if($e->planoEmpresa)
	<td>{{$e->planoEmpresa->plano->nome}}</td>
	@else
	<td>--</td>
	@endif

	@if($e->vencimento)
	<td>{{ \Carbon\Carbon::parse($e->vencimento)->format('d/m/Y')}}</td>
	@else
	<td>--</td>
	@endif
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
</tr>
@endforeach
@endsection
