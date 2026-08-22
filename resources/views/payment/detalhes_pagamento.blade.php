@extends('default.layout', ['title' => 'Detalhes de pagamento'])
@section('css')
<style type="text/css">
	.card-stretch:hover{
		cursor: pointer;
	}

	.input-group-append:hover{
		cursor: pointer;
	}
</style>
@endsection
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">

			<input type="hidden" value="{{$payment->transacao_id}}" id="transacao_id" name="">
			<input type="hidden" value="{{$payment->status}}" id="status" name="">
			<h3>Plano: <strong class="text-info">{{$payment->plano->plano->nome}}</strong></h3>
			<h3>Transação ID: <strong class="text-info">{{$payment->transacao_id}}</strong></h3>

			<h3 class="hide">Status: 
				@if($payment->status == 'approved')
				<span class="label label-xl label-inline label-light-success">Aprovado</span>
				@elseif($payment->status == 'pending')
				<span class="label label-xl label-inline label-light-warning">Pendente</span>
				@elseif($payment->status == 'rejected')
				<span class="label label-xl label-inline label-light-danger">Rejeitado</span>
				@else
				<span class="label label-xl label-inline label-light-dark">Não identificado</span>
				@endif
			</h3>

			<div class="col-lg-6 hide">

				<h3>Valor: <strong class="text-info">{{number_format($payment->valor, 2, ',', '.')}}</strong></h3>
				<h3>Forma de pagamento: <strong class="text-info">{{$payment->forma_pagamento}}</strong></h3>
				<h3>Data de criação: <strong class="text-info">{{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i:s')}}</strong></h3>
				<h3>Ultima atualização: <strong class="text-info">{{ \Carbon\Carbon::parse($payment->updated_at)->format('d/m/Y H:i:s')}}</strong></h3>
			</div>

			@if($payment->forma_pagamento == 'Pix')
			<div class="row hide">
				<div class="col-lg-12">

					<div class="col-lg-4 offset-lg-4">
						<img style="width: 400px; height: 400px;" src="data:image/jpeg;base64,{{$payment->qr_code_base64}}"/>
					</div>					
				</div>	
				<div class="col-lg-12">

					<div class="col-lg-11 offset-lg-1">

						<div class="input-group">
							<input type="text" class="form-control" value="{{$payment->qr_code}}" id="qrcode_input" />

							<div class="input-group-append">
								<span class="input-group-text">

									<i onclick="copy()" class="bx bx-copy">
									</i>

								</span>
							</div>
						</div>

					</div>				
				</div>				
			</div>

			@endif
			<div class="row status-approved col-12 d-none">
				<h3 class="text-success w-100 text-center display-3">Pagamento aprovado <i class="bx bx-check text-success"></i></h3>
				<a href="{{ route('graficos.index') }}" class="btn btn-success">Tela inicial</a>
			</div>
		</div>
		
	</div>
</div>
@endsection
@section('js')

<script type="text/javascript">
	function copy(){
		const inputTest = document.querySelector("#qrcode_input");

		inputTest.select();
		document.execCommand('copy');

		swal("", "Código pix copado!!", "success")
	}

	if($('#status').val() != "approved"){
		$('.loading-class').remove()
    	let intervalVar =setInterval(() => {
    		let transacao_id = $('#transacao_id').val();
    		$.get(path_url+'api/payment-consulta/'+transacao_id)
    		.done((success) => {
				// console.log(success)
				if(success == "approved"){
					clearInterval(intervalVar)
					// location.reload()
					$('.hide').addClass('d-none')
					$('.status-approved').removeClass('d-none')
				}
			})
    		.fail((err) => {
    			console.log(err)
    		})
    	}, 2000)
    }
</script>
@endsection

