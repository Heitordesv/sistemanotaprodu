@extends('default.layout',['title' => 'IBPT ' . $ibpt->uf])
@section('content')
<div class="card card-custom gutter-b @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">


	<div class="card-body">
		<div class="">
			<div class="col-sm-12 col-lg-4 col-md-6 col-xl-4">

				<h3>IBPT 
					<strong style="margin-left: 5px;" class="text-info">{{ $ibpt->uf }}</strong>
				</h3>
			</div>
		</div>
		<br>
		<div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
			<br>

			<div class="row">

				<div class="table-responsive">
					<table class="table mb-0 table-striped">
						<thead>
							<tr>
								<th>Código</th>
								<th>Descrição</th>
								<th>Nacional/Federal</th>
								<th>Importado/Federal</th>
								<th>Estadual</th>
								<th>Municipal</th>

							</tr>
						</thead>
						<tbody id="body" class="datatable-body">
							<?php $total = 0; ?>
							@foreach($itens as $i)
							<tr class="datatable-row">
								<td>{{$i->codigo}}</td>
								<td>{{$i->descricao}}</td>
								<td>{{$i->nacional_federal}}</td>
								<td>{{$i->importado_federal}}</td>
								<td>{{$i->estadual}}</td>
								<td>{{$i->municipal}}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				<div class="d-flex justify-content-between align-items-center flex-wrap">
					<div class="d-flex flex-wrap py-2 mr-3">
						@if(isset($links))
						{{$itens->links()}}
						@endif
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

@endsection