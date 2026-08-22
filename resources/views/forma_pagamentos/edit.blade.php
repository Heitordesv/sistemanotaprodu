@extends('default.layout',['title' => 'Editar Forma de Pagamento'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('formasPagamento.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Editar forma de pagamento</h5>
			</div>
			<hr>
			
			@if(!$podeEditar)
			<p class="text-info">*Esta é uma forma de pagamento padrão, por isso não é possível editar nome e prazo, para que ela não apareça na caixa de seleção da venda desative!</p>
			@endif
			
			{!!Form::open()->fill($item)
			->put()
			->route('formasPagamento.update', [$item->id])
			->multipart()!!}
			<div class="pl-lg-4">
				@include('forma_pagamentos._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection