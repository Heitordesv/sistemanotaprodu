@extends('ecommerce_one_tech.default')
@section('content')

<style type="text/css">
	.shoping__checkout span, li{
		font-size: 20px;
	}
</style>

<div class="cart_section" style="margin-top: -70px;">
	<div class="container">
		<div class="row">
			<div class="col-lg-10 offset-lg-1">
				<div class="cart_container">
					<input type="hidden" value="{{$pedido->transacao_id}}" id="transacao_id" name="">
					<input type="hidden" value="{{$pedido->status_pagamento}}" id="status" name="">

					<div class="cart_title">Detalhes do seu pedido</div>

					@if($pedido->status_pagamento == 'pending')
					<h3>PAGAMENTO: <strong class="text-warning">PENDENTE</strong></h3>
					@elseif($pedido->status_pagamento == 'approved')
					<h3>PAGAMENTO: <strong class="text-success">APROVADO</strong> - Transação ID: <strong class="text-primary">{{$pedido->transacao_id}}</strong></h3>
					@else
					<h3>PAGAMENTO: <strong class="text-danger">CANCELADO/REJEITADO</strong></h3>
					@endif

					<h4>Estado:
						@if($pedido->status_preparacao == 0)
						<span class="text-info">Novo</span>
						@elseif($pedido->status_preparacao == 1)
						<span class="text-primary">Aprovado</span>
						@elseif($pedido->status_preparacao == 2)
						<span class="text-danger">Cancelado</span>
						@elseif($pedido->status_preparacao == 3)
						<span class="text-warning">Aguardando Envio</span>
						@elseif($pedido->status_preparacao == 4)
						<span class="text-dark">Enviado</span>
						@else
						<span class="text-success">Entregue</span>
						@endif
					</h4>

					<br>
					<div class="cart_items">
						<ul class="cart_list">

							@foreach($pedido->itens as $i)
							<li class="cart_item clearfix">
								<div class="cart_item_image">
									<a href="/loja/{{$default['config']->link}}/{{$i->produto->id}}/verProduto">
										<img src="/ecommerce/produtos/{{$i->produto->galeria[0]->img}}" alt="">
									</a>
								</div>
								<div class="cart_item_info d-flex flex-md-row flex-column justify-content-between">
									<div class="cart_item_name cart_info_col">
										<div class="cart_item_title">Nome</div>
										<div class="cart_item_text">
											<a href="/loja/{{$default['config']->link}}/{{$i->produto->id}}/verProduto">
												{{$i->produto->produto->nome}}

												@if($i->produto->produto->grade)
												| {{$i->produto->produto->str_grade}}
												@endif
											</a>
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

										@if($pedido->codigo_rastreio != "")
										<h6>Código de rastreamento: 
											<strong class="text-info">
												{{ $pedido->codigo_rastreio }}
											</strong>
										</h6>
										@endif
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