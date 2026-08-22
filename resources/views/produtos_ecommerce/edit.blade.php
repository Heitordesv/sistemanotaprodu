@extends('default.layout',['title' => 'Editar produto ecommerce'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('produtoEcommerce.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Editar produtos</h5>
			</div>
			<hr>
			{!!Form::open()->fill($item)
			->put()
			->route('produtoEcommerce.update', [$item->id])
			->multipart()!!}
			<div class="pl-lg-4">
				@include('produtos_ecommerce._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>

@include('modals._produtoRapido', ['not_submit' => true])
@include('modals._categoriaEcommerce', ['not_submit' => true])

@endsection
