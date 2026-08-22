@extends('ecommerce_one_tech.default')
@section('content')

<style type="text/css">
	.loader {
		border: 5px solid #f3f3f3; /* Light grey */
		border-top: 5px solid #3498db; /* Blue */
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

	.form-control{
		color: #000;
	}
</style>

<div class="cart_section">
	<div class="container">
		<div class="row">
			<div class="col-lg-10 offset-lg-1">
				<div class="cart_container">

					<div class="cart_items">
						<ul class="cart_list">
							@if(sizeof($curtidas) > 0)
							@foreach($curtidas as $i)
							<li class="cart_item clearfix">
								<div class="cart_item_image"><img src="/ecommerce/produtos/{{$i->produto->galeria[0]->img}}" alt=""></div>
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
										<div class="cart_item_title">Adicionado em</div>
										<div class="cart_item_text">
											{{ \Carbon\Carbon::parse($i->created_at)->format('d/m/Y H:i:s')}}
											
										</div>
									</div>
									<div class="cart_item_quantity cart_info_col">
										<div class="cart_item_title">Valor</div>
										<div class="cart_item_text">
											R$ {{number_format($i->produto->valor, 2, ',', '.')}}
										</div>
									</div>


									<div class="cart_item_total cart_info_col">
										<div class="cart_item_title"></div>
										<div class="cart_item_text" >
											<td class="shoping__cart__quantity">
												<a style="margin-top: 10px;" class="btn primary-btn" href="{{$rota}}/{{$i->produto->id}}/verProduto">
													<i class="fa fa-shopping-cart"></i> Adicionar
												</a>
											</td>
										</div>
									</div>
								</div>
							</li>
							@endforeach
							@endif
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection