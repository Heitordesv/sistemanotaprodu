@extends('default.layout',['title' => 'Contas a crédito'])
@section('content')
<div class="page-content">
	<div class="card ">
		<div class="card-body p-4">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">

				<div class="ms-auto">
					<a href="{{ route('vendasEmCredito.create')}}" type="button" class="btn btn-success">
						<i class="bx bx-plus"></i> Nova Conta
					</a>
				</div>
			</div>
			<div class="col">
				<h6 class="mb-0 text-uppercase">Contas crédito</h6>

				{!!Form::open()
                ->fill(request()
                ->all())
				->get() !!}
				<div class="row mt-3">
					<div class="col-md-6">
						{!!Form::select('cliente_id', 'Pesquisar por cliente')
                        ->attrs(['class' => 'select2'])
						!!}
					</div>
					<div class="col-md-2">
						{!!Form::date('start_date', 'Data início')
						!!}
					</div>
					<div class="col-md-2">
						{!!Form::date('end_date', 'Data final')
						!!}
					</div>
					<div class="col-md-2">
						{!!Form::select('status', 'Estado',
						[
						'' => 'Todos',
						'1' => 'Pago',
						'0' => 'Pendente',
						])
						!!}
					</div>

					<div class="col-md-3 text-left ">
						<br>
						<button class="btn btn-primary"  type="submit"><i class="bx bx-search"></i>Pesquisar</button>
						<a id="clear-filter" class="btn btn-danger"
						href="{{ route('vendasEmCredito.index') }}">Limpar</a>
					</div>
				</div>

				{!!Form::close()!!}

				<hr/>
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table class="table mb-0 table-striped">
								<thead class="">
									<tr>
										<th width="">Código</th>
										<th width="">Cliente</th>
										<th width="">Venda</th>
										<th width="">Valor</th>
										<th width="">Data de cadastro</th>
										<th width="">Data de recebimento</th>
										<th width="">Status</th>
										<th width="">Ações</th>
									</tr>
								</thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td>
                                            <td colspan="7" class="text-center">Nada encontrado</td>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>
@endsection
