@extends('default.layout',['title' => 'API de Convers達o do Facebook'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('apiFacebook.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">API de Convers達o do Facebook</h5>
			</div>
			<hr>
			{!!Form::open()
			->post()
			->route('apiFacebook.store')
			->multipart()!!}
			<div class="pl-lg-4">
				@include('api_facebook._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection
