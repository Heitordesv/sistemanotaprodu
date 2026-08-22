@extends('default.layout',['title' => 'Receber Conta'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('conta-receber.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Receber conta</h5>
			</div>
			<hr>
			
			{!!Form::open()
			->put()
			->route('conta-receber.payPut', [$item->id])
			!!}
			<div class="pl-lg-4">
				<div class="row">
					<div class="col-md-6">
						<h6>Data de cadastro: <strong class="">{{ __data_pt($item->created_at) }}</strong></h6>
						<h6>Valor: <strong class="">R$ {{ __moeda($item->valor_integral) }}</strong></h6>

					</div>
					<div class="col-md-6">
						<h6>Data de vencimento: <strong class="">{{ __data_pt($item->data_vencimento, false) }}</strong></h6>
						<h6>Referência: <strong class="">{{ $item->referencia }}</strong></h6>

					</div>
				</div>
				@include('conta_receber._forms_pay')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection