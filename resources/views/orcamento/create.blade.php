@extends('default.layout',['title' => 'Orçamento'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('orcamentoVenda.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			{!!Form::open()
			->post()
			->autocomplete('off')
			->route('orcamentoVenda.store')
			->multipart()!!}
			<div class="pl-lg-4">
				@include('orcamento._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection

