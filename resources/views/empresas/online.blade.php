@extends('default.layout',['title' => 'Novo Representante'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('representantes.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div>
				<h5>Empresas online</h5>
				<h6>Total de empresas: {{ sizeof($empresas) }}</h6>
				<p style="color: blue">Atualização em 10 minutos</p>
			</div>
			<div class="table-responsive">
				<table class="table mb-0 table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>Nome / Razão social</th>
							<th>Nome fantasia</th>
							<th>Telefone</th>
							<th>Cidade</th>
							<th>Plano</th>
							<th>Último login</th>
							<th>Ações</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection
