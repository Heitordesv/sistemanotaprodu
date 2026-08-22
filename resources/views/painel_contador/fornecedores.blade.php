@extends('default.layout',['title' => 'Forncedores'])
@section('content')

<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="card-body">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-6">
                        {!!Form::text('razao_social', 'Pesquisar por nome')
                        !!}
                    </div>
                    <div class="col-md-5 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('contador.fornecedores') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <div class="card mt-3">
					<div class="card-body">
						<div class="table-responsive">
							<table class="table mb-0 table-striped">
								<thead>
                                    <tr>
                                        <th>Razão social</th>
                                        <th>CPF/CNPJ</th>
                                        <th>IE</th>
                                        <th>Rua</th>
                                        <th>Número</th>
                                        <th>Bairro</th>
                                        <th>Cidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->razao_social }}</td>
                                        <td>{{ $item->cpf_cnpj }}</td>
                                        <td>{{ $item->ie_rg }}</td>
                                        <td>{{ $item->rua }}</td>
                                        <td>{{ $item->numero }}</td>
                                        <td>{{ $item->bairro }}</td>
                                        <td>{{ $item->cidade->info }}</td>
                                    </tr>
                                    @empty
									<tr>
										<td colspan="7" class="text-center">Nada encontrado</td>
									</tr>
									@endforelse
                                </tbody>
							</table>
						</div>
					</div>
				</div>
            </div>
			{!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection
