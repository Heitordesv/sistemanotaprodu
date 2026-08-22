@extends('ecommerce_one_tech.default')
@section('content')

<style type="text/css">
	.loader {
		border: 5px solid #f3f3f3;
		border-top: 5px solid #3498db;
		border-radius: 50%;
		width: 30px;
		height: 30px;
		animation: spin 2s linear infinite;
		margin-left: 10px;
		margin-top: 5px;
	}

	@keyframes spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}

	.form-control {
		color: #000;
	}
</style>

<div class="cart_section">
	<div class="container">
		<div class="row">
			<div class="col-lg-10 offset-lg-1">
				<div class="cart_container">
					<div class="cart_title">Carrinho de compra</div>

					<div class="cart_items">
						<ul class="cart_list">
							@if($default['carrinho'] != null && $default['carrinho']->itens->count())
								@foreach($default['carrinho']->itens as $i)
									<li class="cart_item clearfix">
										<div class="cart_item_image">
											@if(isset($i->produto->galeria[0]->img))
												<img src="{{ $i->produto->galeria[0]->img }}" alt="">
											@else
												<img src="/path/imagem-padrao.jpg" alt="Imagem não disponível">
											@endif
										</div>
										<div class="cart_item_info d-flex flex-md-row flex-column justify-content-between">
											<div class="cart_item_name cart_info_col">
												<div class="cart_item_title">Nome</div>
												<div class="cart_item_text">
													{{ $i->produto->produto->nome ?? 'Produto sem nome' }}
													@if(isset($i->produto->produto->grade) && $i->produto->produto->grade)
														| {{ $i->produto->produto->str_grade }}
													@endif
												</div>
											</div>

											<div class="cart_item_quantity cart_info_col">
												<div class="cart_item_title">Quantidade</div>
												<div class="cart_item_text">
													<input style="width: 80px" id="{{ $i->id }}" class="qtd form-control" type="number" value="{{ $i->quantidade }}" min="1" max="15">
												</div>
											</div>

											<div class="cart_item_price cart_info_col">
												<div class="cart_item_title">Valor</div>
												<div class="cart_item_text">
													R$ {{ number_format($i->produto->valor ?? 0, 2, ',', '.') }}
												</div>
											</div>

											<div class="cart_item_total cart_info_col">
												<div class="cart_item_title">Subtotal</div>
												<div class="cart_item_text">
													R$ {{ number_format(($i->quantidade * ($i->produto->valor ?? 0)), 2, ',', '.') }}
												</div>
											</div>

											<div class="cart_item_total cart_info_col">
												<div class="cart_item_text">
													<a href="{{ $rota }}/{{ $i->id }}/deleteItemCarrinho">
														<span style="margin-top: 18px;" class="fa fa-trash text-danger"></span>
													</a>
												</div>
											</div>
										</div>
									</li>
								@endforeach
							@else
								<li class="cart_item clearfix">Carrinho vazio</li>
							@endif
						</ul>

						<div class="cart_buttons" style="margin-top: 5px;">
							<a href="{{ $rota }}/carrinho">
								<button type="button" class="btn">
									<i class="fa fa-retweet"></i>
									ATUALIZAR CARRINHO
								</button>
							</a>
						</div>
					</div>

					<input type="hidden" value="{{ csrf_token() }}" id="token">
					<input type="hidden" value="{{ $default['carrinho'] != null ? $default['carrinho']->somaItens() : 0 }}" id="soma_hidden">

					<!-- Frete -->
					<div class="cart_items" style="margin-top: 5px;">
						<ul class="cart_list">
							<li class="cart_item clearfix">
								<h3>Frete</h3>
								<form action="#">
									@if($default['carrinho'] != null)
										<input type="hidden" id="pedido_id" value="{{ $default['carrinho']->id }}">
									@endif
									<div class="col-12">
										<div class="row">
											<input style="width: 30%;" class="form-control" id="cep" data-mask="00000-000" data-mask-reverse="true" type="text" placeholder="CEP">
											<button id="btn-calcular-frete" type="button" class="btn">CALCULAR</button>
											<div style="display: none" class="loader spinner-border"></div>
										</div>
									</div>
								</form>
								<br>
								<div class="frete"></div>
							</li>
						</ul>
					</div>

					<!-- Soma dos itens -->
					<div class="order_total">
						<div class="order_total_content text-md-right">
							<div class="order_total_title">Soma dos itens:</div>
							<div class="order_total_amount">
								R$ {{ $default['carrinho'] != null ? number_format($default['carrinho']->somaItens(), 2, ',', '.') : '0,00' }}
							</div>
						</div>
					</div>

					<!-- Total -->
					<div class="order_total">
						<div class="order_total_content text-md-right">
							<div class="order_total_title">Total:</div>
							<div class="order_total_amount" id="total">
								R$ {{ $default['carrinho'] != null ? number_format($default['carrinho']->somaItens(), 2, ',', '.') : '0,00' }}
							</div>
						</div>
					</div>

					<!-- Botões -->
					<div class="cart_buttons">
						<div class="col-12">
							<div class="row">
								<a href="{{ $rota }}">
									<button type="button" class="button cart_button_clear">CONTINUAR COMPRANDO</button>
								</a>
								<form method="get" action="{{ $rota }}/checkout">
									<input type="hidden" id="tp_frete" value="" name="tp_frete">
									<button type="submit" class="button cart_button_checkout">CONTINUAR PARA PAGAMENTO</button>
								</form>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>

@endsection