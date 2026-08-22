@extends('default.layout',['title' => 'Duplicar produto'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Duplicar produto</h5>
			</div>
			<hr>
			{!!Form::open()->fill($item)
			->post()
			->route('produtos.store')
			->multipart()!!}
			<div class="pl-lg-4">
				@include('produtos._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection
