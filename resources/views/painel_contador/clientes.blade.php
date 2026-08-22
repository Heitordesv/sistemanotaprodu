@extends('default.layout',['title' => 'Clientes'])
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
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('contador.clientes') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <div class="card mt-3">
					<div class="card-body">
						<div class="table-responsive">
							<table class="table mb-0 table-striped">
								<thead class="">
									<tr>
										<th></th>
										<th>Razão social</th>
										<th>CPF/CNPJ</th>
										<th>Data de cadastro</th>
										<th>Celular</th>
										<th>Endereço</th>
										<th>Cidade</th>
										<th>Ações</th>
									</tr>
								</thead>
								<tbody>
									@forelse($data as $item)
									<tr>
										<td><img class="img-round" src="{{ $item->img }}"></td>
										<td>{{ $item->razao_social }}</td>
										<td>{{ $item->cpf_cnpj }}</td>
										<td>{{ __data_pt($item->created_at) }}</td>
										<td>{{ $item->celular }}</td>
										<td>{{ $item->rua }}, {{ $item->numero }} | {{ $item->bairro }}</td>
										<td>{{ $item->cidade->info }}</td>

										<td>
											<form action="{{ route('clientes.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
												@method('delete')
												<a href="{{ route('clientes.edit', $item) }}" class="btn btn-warning btn-sm text-white">
													<i class="bx bx-edit"></i>
												</a>

												@csrf
												<button type="button" class="btn btn-delete btn-sm btn-danger">
													<i class="bx bx-trash"></i>
												</button>
											</form>
										</td>
									</tr>
									@empty
									<tr>
										<td colspan="8" class="text-center">Nada encontrado</td>
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
