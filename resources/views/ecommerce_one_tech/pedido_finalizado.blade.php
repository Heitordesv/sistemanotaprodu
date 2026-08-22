@extends('ecommerce_one_tech.default')
@section('content')

<style type="text/css">
	.shoping__checkout span, li{
		font-size: 20px;
	}
	.input-group-text:hover{
		cursor: pointer;
		background: #0E8CE4 !important;
	}
	.input-group-text button{
		height: 100% !important;
		width: 30px;
		border-top-right-radius: 5px;
		border-bottom-right-radius: 5px;
		color: #fff;
		background: #0E8CE4;
	}

</style>

<div class="cart_section" style="margin-top: -70px;">
	<div class="container">
		<div class="row">
			<div class="col-lg-10 offset-lg-1">
				<div class="cart_container">
					<input type="hidden" value="{{$pedido->transacao_id}}" id="transacao_id" name="">
					<input type="hidden" value="{{$pedido->status_pagamento}}" id="status" name="">
					@if($pedido->status != 2)
					<div class="">
						<div class="alert alert-custom alert-success fade show" role="alert">

							<div class="alert-text"><i class="fa fa-check"></i> 

								@if($pedido->forma_pagamento == "Boleto")
								Obrigado por realizar o pedido, aguardamos o pagamento do seu boleto 😊

								@elseif($pedido->forma_pagamento == "Pix")
								Obrigado por realizar o pedido, abaixo esta o Qrcode e copia e cola do seu pix 😊
								@else
								Obrigado por realizar o pedido 😊
								@endif

							</div>
						</div>
					</div>


					@if($pedido->forma_pagamento == 'Pix')
					<div class="row">
						<div class="col-lg-12">

							<div class="col-lg-4 offset-lg-4">
								<img style="width: 400px; height: 400px;" src="data:image/jpeg;base64,{{$pedido->qr_code_base64}}"/>
							</div>                  
						</div>  
						<div class="col-lg-12">

							<div class="col-lg-11 offset-lg-1">

								<div class="input-group">
									<input type="text" class="form-control" value="{{$pedido->qr_code}}" id="qrcode_input" />

									<div class="input-group-append">
										<span class="input-group-text">
											<button>
												<i onclick="copy()" class="fa fa-copy">
												</i>
											</button>
										</span>
									</div>
								</div>

							</div>              
						</div>              
					</div>

					<div class="row">


					</div>
					@endif

					@elseif($pedido->status == 2)

					<div class="">
						<div class="alert alert-custom alert-success fade show" role="alert">

							<div class="alert-text"><i class="fa fa-check"></i> 


								@if($pedido->forma_pagamento == "Pix")

								Que ótimo seu pix foi aprovado, obrigado pela confiança 😊

								@endif

							</div>
						</div>
					</div>

					@endif
					<br>
					<div class="cart_title">Detalhes do seu pedido</div>
					<div class="cart_items">
						<ul class="cart_list">

							@foreach($pedido->itens as $i)
							<li class="cart_item clearfix">
								<div class="cart_item_image">
									<img style="max-height: 130px;" src="/ecommerce/produtos/{{$i->produto->galeria[0]->img}}" alt="">
								</div>
								<div class="cart_item_info d-flex flex-md-row flex-column justify-content-between">
									<div class="cart_item_name cart_info_col">
										<div class="cart_item_title">Nome</div>
										<div class="cart_item_text">
											{{$i->produto->produto->nome}}

											@if($i->produto->produto->grade)
											| {{$i->produto->produto->str_grade}}
											@endif
										</div>
									</div>
									
									<div class="cart_item_quantity cart_info_col">
										<div class="cart_item_title">Quantidade</div>
										<div class="cart_item_text">
											{{$i->quantidade}}
										</div>
									</div>
									<div class="cart_item_price cart_info_col">
										<div class="cart_item_title">Valor</div>
										<div class="cart_item_text">
											R$ {{number_format($i->produto->valor, 2, ',', '.')}}
										</div>
									</div>
									<div class="cart_item_total cart_info_col">
										<div class="cart_item_title">Subtotal</div>
										<div class="cart_item_text">
											R$ {{number_format($i->quantidade*$i->produto->valor, 2, ',', '.')}}
										</div>
									</div>

									<div class="cart_item_total cart_info_col">
										<div class="cart_item_title"></div>
										<div class="cart_item_text" >
											<a  href="{{$rota}}/{{$i->id}}/deleteItemCarrinho"><span style="margin-top: 18px;" class="fa fa-trash text-danger"></span></a>
										</div>
									</div>
								</div>
							</li>
							@endforeach

						</ul>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-12">
							<div class="shoping__cart__btns">

							</div>
						</div>
						<input type="hidden" value="{{csrf_token()}}" id="token">


						<div class="col-lg-6">
							<div class="shoping__checkout">
								<h5>Endereço</h5>
								<ul>
									<li>

										<h6>Rua: 
											<strong>
												{{ $pedido->endereco->rua }}, {{ $pedido->endereco->numero }}
											</strong>
										</h6>
										<h6>Complemento: 
											<strong>
												{{ $pedido->endereco->complemento }}
											</strong>
										</h6>
										<h6>Bairro: 
											<strong>
												{{ $pedido->endereco->bairro }}
											</strong>
										</h6>
										<h6>CEP: 
											<strong>
												{{ $pedido->endereco->cep }}
											</strong>
										</h6>

										<h6>Cidade: 
											<strong>
												{{ $pedido->endereco->cidade }} ({{ $pedido->endereco->uf }})
											</strong>
										</h6>
									</li>

								</ul>

							</div>
						</div>

						<div class="col-lg-6">
							<div class="shoping__checkout">
								<h5>TOTAL</h5>
								<ul>
									<li>Itens 
										<span class="text-info">
											R$ {{ number_format($pedido->somaItens(), 2, ',', '.') }}
										</span>
									</li>

									<li>Frete 
										<span>
											R$ {{ number_format($pedido->valor_frete, 2, ',', '.') }}
										</span>
									</li>

									<li>Total 
										<span id="total" class="text-success">
											R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
										</span>
									</li>

									<li>
										@if($pedido->link_boleto != "")
										<a target="_blank" class="btn btn-lg btn-info" href="{{$pedido->link_boleto}}">
											<i class="fa fa-print"></i> Imprimir Boleto
										</a>
										@endif
									</li>
								</ul>

							</div>
						</div>
					</div>


				</div>
			</div>
		</div>

	</div>
</div>

@section('javascript')
<script type="text/javascript">
	@if($pedido->link_boleto != "")
	// window.open('{{$pedido->link_boleto}}')
	@endif


	function copy(){

		const inputTest = document.querySelector("#qrcode_input");

		inputTest.select();
		document.execCommand('copy');

		swal("", "Código pix copado!!", "success")
	}

	var prot = window.location.protocol;
	var host = window.location.host;
	var pathname = window.location.pathname;
	let path = prot + "//" + host;

	if($('#status').val() != "approved"){
		setInterval(() => {
			let transacao_id = $('#transacao_id').val();

			$.get(path+'/ecommercePay/consulta/'+transacao_id)
			.done((success) => {

				if(success == "approved"){
					location.reload()
				}
			})
			.fail((err) => {
				console.log(err)
			})
		}, 1500)
	}
</script>

@endsection
@endsection