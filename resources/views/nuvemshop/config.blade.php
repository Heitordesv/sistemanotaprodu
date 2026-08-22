@extends('default.layout',['title' => 'Configuração Nuvem Shop'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Configuração Nuvem Shop</h5>
			</div>
			<hr>
			
			{!!Form::open()->fill($config)
			->post()
			->route('nuvemshop.store')
			->multipart()!!}
			<div class="pl-lg-4">
				<div class="row g-3">
					<div class="col-md-3">
						{!!Form::text('client_id', 'Client ID')
						->required()
						!!}
					</div>

					<div class="col-md-6">
						{!!Form::text('client_secret', 'Client secret')
						->required()
						!!}
					</div>

					<div class="col-md-3">
						{!!Form::text('email', 'Email')
						->required()
						->type('email')
						!!}
					</div>

					<div class="col-12">
						<button type="submit" class="btn btn-primary px-5">Salvar</button>
					</div>
				</div>
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection