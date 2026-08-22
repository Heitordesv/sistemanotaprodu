@extends('default.layout',['title' => 'Editar Endereço Cliente Delivery'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('clientesDelivery.enderecos', $item)}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Editar Endereço Delivery</h5>
			</div>
			<hr>
			{!!Form::open()->fill($item)
			->put()
			->route('enderecoDelivery.update', [$item->id])
			->multipart()!!}
            <input type="hidden" name="cliente_id" value="{{ $item->cliente_id }}">
			<div class="pl-lg-4">
				<div class="row">
                    <div class="col-md-10">
                        {!! Form::text('rua', 'Rua')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::tel('numero', 'Número')->attrs(['class' => '']) !!}
                    </div>
                    @isset($bairros)
                    <div class="col-md-5">
                        {!! Form::select('bairro_id', 'Bairro', $bairros->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']) !!}
                    </div>
                    @endisset
                    <div class="col-md-6 mt-2">
                        {!! Form::text('referencia', 'Referência')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-2 mt-2">
                        {!! Form::select('tipo', 'Tipo', ['casa' => 'Casa', 'trabalho' => 'Trabalho'])->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-2 mt-2">
                        {!! Form::tel('latitude', 'Latitude')->attrs(['data-mask' => '0000000000']) !!}
                    </div>
                    <div class="col-md-2 mt-2">
                        {!! Form::tel('longitude', 'Longitude')->attrs(['data-mask' => '0000000000']) !!}
                    </div>
                    <div class="mt-5">
                        <button type="submit" class="btn btn-info px-5">Salvar</button>
                    </div>
                </div>
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection
