@extends('default.layout',['title' => 'Editar IBPT'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('ibpt.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-">Editar tabela de IBPT {{ $item->uf }}</h5>
			</div>
			<hr>
			{!!Form::open()->fill($item)
			->put()
			->route('ibpt.update', [$item->id])
			->multipart()!!}
			<div class="">
				@include('ibpt._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection
