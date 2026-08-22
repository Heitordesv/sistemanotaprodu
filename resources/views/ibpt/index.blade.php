@extends('default.layout',['title' => 'Nova Importação'])
@section('content')
<div class="page-content">
	<div class="card ">
		<div class="card-body p-4">
			<a class="btn btn-info" href="{{ route('ibpt.create') }}"><i class="bx bx-plus"></i> Nova importação</a>

			<div class="row mt-2">

				@foreach($data as $item)


				<div class="col-sm-12 col-12 col-xl-4">
					<div class="card card-custom gutter-b example example-compact">
						<div class="card-header">

							<h3 class="card-title"><strong style="margin-right: 5px;" class="text-info">{{$item->uf}}</strong> {{$item->versao}} - {{ \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y H:i:s')}}
							</h3>
							<div class="card-toolbar">

								<a href="{{ route('ibpt.edit', [$item->id]) }}" class="btn btn-icon btn-circle btn-sm btn-warning mr-1">
									Editar
								</a>
								<a href="{{ route('ibpt.show', [$item->id]) }}" class="btn btn-icon btn-circle btn-sm btn-info mr-1">
									Ver
								</a>

							</div>
						</div>

					</div>

				</div>

				@endforeach

			</div>
		</div>
	</div>
</div>
@endsection
