@extends('default.layout',['title' => 'Novo Produto'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('nuvemshop-produtos.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Novo produto</h5>
			</div>
			<hr>
			
			{!!Form::open()
			->post()
			->route('nuvemshop-produtos.store')
			->multipart()!!}
			<div class="pl-lg-4">
				@include('nuvemshop_produtos._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@include('modals._produto', ['not_submit' => true])

@endsection
@section('js')
@endsection

