@extends('default.layout',['title' => 'Manifesto'])
@section('content')
<div class="page-content">
	<div class="card ">
		<div class="card-body p-4">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">

			</div>
			<div class="col">
				{!!Form::open()->fill(request()->all())
				->get()
				!!}
				<div class="row mt-2">
					<div class="col-md-3">
						{!!Form::date('start_date', 'Data inicial')
						!!}
					</div>
                    <div class="col-md-3    ">
						{!!Form::date('end_date', 'Data final')
						!!}
					</div>
                    <div class="col-md-3">
						{!!Form::select('tipo', 'Tipo', [0 => '--', 1 => 'Ciência', 2 => 'Confirmada', 3 => 'Desconhecido', 4 => 'Op. não Realizada'])
						->attrs(['class' => 'select2'])
						!!}
					</div>
                    <div class="col-md-3 text-left ">
						<br>
                        <button class="btn btn-primary"  type="submit"> <i class="bx bx-search"></i>Pesquisa</button>
					</div>
				</div>

				{!!Form::close()!!}

                <div class="mt-5">
                     <h5>Manifesto</h5>
                </div>
                <div>
                    <a href="{{ route('cte.consultaDocumentos') }}">
                    <button  type="button" class="btn btn-primary"><i class="bx bx-refresh"></i> Nova consulta de documentos</button>
                    </a>
                </div>
                <h6 class="mt-3">Total de registros:</h6>
				<hr/>
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Documento</th>
                                        <th>Valor</th>
                                        <th>Data emissão</th>
                                        <th>Num. protocolo</th>
                                        <th>Chave</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
									@forelse ($data as $item)
										<tr>
											<td>{{ $item->nome }}</td>
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
		</div>
	</div>
</div>
@endsection
