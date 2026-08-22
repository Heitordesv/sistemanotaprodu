@extends('ecommerce_one_tech.default')
@section('content')

<style type="text/css">
	.img-cat{
		width: 60px;
		border-radius: 5px;
	}

	.img-destaque{
		width: 120px;
	}
</style>

@if(sizeof($carrossel) > 0)
<div class="banner">
	<a @if($carrossel[0]->link_acao != "") href="{{$carrossel[0]->link_acao}}" @endif>
		<div class="banner_background" style="background-image:url({{$carrossel[0]->image}})"></div>
	</a>
	<div class="col-lg-5 offset-lg-4 fill_height">
		<div class="banner_content">
			<h1 style="color: {{$carrossel[0]->cor_titulo}}" class="banner_text">{{$carrossel[0]->titulo}}</h1>
			<!-- <div class="banner_price"><span>$530</span>$460</div> -->
			<div style="color: {{$carrossel[0]->cor_descricao}}" class="banner_product_name">{{$carrossel[0]->descricao}}</div>
			@if($carrossel[0]->nome_botao != "" && $carrossel[0]->link_acao != "")
			<div class="button banner_button"><a href="{{$carrossel[0]->link_acao}}">{{$carrossel[0]->nome_botao}}</a></div>

			@endif
		</div>
	</div>
</div>
@endif

<div class="characteristics">
	<div class="container">
		<div class="row">

			<!-- Char. Item -->
			@foreach($categorias as $c)
			<div class="col-lg-3 col-md-6 char_col">

				<div class="char_item d-flex flex-row align-items-center justify-content-start">
					
					<div class="char_icon">
						<a href="{{$rota}}/{{$c->id}}/categorias">
							<img class="img-cat" src="{{$c->img}}" alt="">
						</a>
					</div>
					<div class="char_content">
						<a href="{{$rota}}/{{$c->id}}/categorias">
							<div class="char_title">{{$c->nome}}</div>
							<div class="char_subtitle">produtos: <strong>{{sizeof($c->produtosAtivos())}}</strong></div>
						</a>
					</div>
				</div>
			</div>
			@endforeach

		</div>
	</div>
</div>

@if(sizeof($produtosEmDestaque) > 0)
<div class="deals_featured">
	<div class="container">
		<div class="row">
			<div class="col d-flex flex-lg-row flex-column align-items-center justify-content-start">

				<!-- Deals -->

				<div class="deals">
					<div class="deals_title">Produtos em destaque</div>
					<div class="deals_slider_container">

						<!-- Deals Slider -->
						<div class="owl-carousel owl-theme deals_slider">

							<!-- Deals Item -->
							@foreach($produtosEmDestaque as $p)
							@if(sizeof($p->galeria) > 0)
							<div class="owl-item deals_item">
								<a href="{{$rota}}/{{$p->id}}/verProduto">
									<div class="deals_image"><img src="{{$p->galeria[0]->img}}" alt=""></div>
								</a>
								
								<div class="deals_content">
									<div class="deals_info_line d-flex flex-row justify-content-start">
										<div class="deals_item_category">
											<a href="{{$rota}}/{{$p->categoria->id}}/categorias">
												{{$p->categoria->nome}}
											</a>
										</div>
										<div style="text-decoration: line-through;" class="deals_item_price_a ml-auto">
											R$ {{number_format($p->valor+($p->valor*0.25), 2, ',', '.')}}
										</div>
									</div>
									<a href="{{$rota}}/{{$p->id}}/verProduto">
										<div class="deals_info_line d-flex flex-row justify-content-start">
											<div class="deals_item_name">
												{{$p->produto->nome}}
											</div>
											<div class="deals_item_price ml-auto">
												R$ {{number_format($p->valor, 2, ',', '.')}}
											</div>
										</div>
									</a>
									
								</div>
							</div>
							@endif
							@endforeach

							
						</div>
					</div>
					<div class="deals_slider_nav_container">
						<div class="deals_slider_prev deals_slider_nav"><i class="fas fa-chevron-left ml-auto"></i></div>
						<div class="deals_slider_next deals_slider_nav"><i class="fas fa-chevron-right ml-auto"></i></div>
					</div>
				</div>

				<div class="featured">
					<div class="tabbed_container">


						<div class="product_panel panel active">
							<div class="featured_slider slider">

								<!-- Slider Item -->
								@foreach($produtosEmDestaque as $key => $p)
								@if($key < 8)
								@if(sizeof($p->galeria) > 0)
								<div class="featured_slider_item">
									<div class="border_active"></div>
									<div class="product_item discount d-flex flex-column align-items-center justify-content-center text-center">
										<div class="product_image d-flex flex-column align-items-center justify-content-center">
											<img class="img-destaque" src="{{$p->galeria[0]->img}}" alt="">
										</div>
										<div class="product_content">
											<div class="product_price discount">
												R$ {{number_format($p->valor, 2, ',', '.')}}
												@if($p->percentual_desconto_view > 0)
												<span style="text-decoration: line-through;">
													R$ {{number_format($p->valor+
													($p->valor*($p->percentual_desconto_view/100)), 2, ',', '.')}}
												</span>
												@endif
											</div>
											<div class="product_name"><div><a href="{{$rota}}/{{$p->id}}/verProduto">{{$p->produto->nome}}</a></div></div>
											<div class="product_extras">
												<div class="product_color">
													
												</div>
												<a href="{{$rota}}/{{$p->id}}/verProduto"><button class="product_cart_button">Adicionar ao carrinho</button></a>
											</div>
										</div>
										<a href="{{$rota}}/{{$p->id}}/curtirProduto">
											<div class="product_fav">
												<i class="fas fa-heart @if($p->curtido) text-danger @endif"></i>
											</div>
										</a>
										
									</div>
								</div>
								@endif
								@endif
								@endforeach


							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endif

<div class="popular_categories">
	<div class="container">
		<div class="row">
			<div class="col-lg-3">
				<div class="popular_categories_content">
					<div class="popular_categories_title">Categorias</div>
					<div class="popular_categories_slider_nav">
						<div class="popular_categories_prev popular_categories_nav"><i class="fas fa-angle-left ml-auto"></i></div>
						<div class="popular_categories_next popular_categories_nav"><i class="fas fa-angle-right ml-auto"></i></div>
					</div>
					<div class="popular_categories_link"><a href="{{$rota}}/categorias">Todas as categorias</a></div>
				</div>
			</div>

			<!-- Popular Categories Slider -->

			<div class="col-lg-9">
				<div class="popular_categories_slider_container">
					<div class="owl-carousel owl-theme popular_categories_slider">

						@foreach($categorias as $c)
						<div class="owl-item">
							<div class="popular_category d-flex flex-column align-items-center justify-content-center">
								<a href="{{$rota}}/{{$c->id}}/categorias">
									<div class="popular_category_image">
										<img height="70" src="{{$c->img}}" alt="">
									</div>
									<div class="popular_category_text">

										{{$c->nome}}
									</div>
								</a>
							</div>
						</div>
						@endforeach

					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="banner_2">
	
	<div class="banner_2_background" ></div>
	<div class="banner_2_container">
		<div class="banner_2_dots"></div>
		<!-- Banner 2 Slider -->

		<div class="owl-carousel owl-theme banner_2_slider">


			@foreach($carrossel as $key => $c)
			@if($key > 0)
			<div class="owl-item">
				<div class="banner_background" style="background-image:url({{$c->img}})"></div>
				<div class="banner_2_item" >
					<div class="container fill_height">

						<div class="row fill_height">
							<div class="col-lg-4 col-md-6 fill_height">
								<div class="banner_2_content">

									<div class="banner_2_title">{{$c->titulo}}</div>
									<div class="banner_2_text">{{$c->descricao}}</div>

									<div class="button banner_2_button"><a href="{{$c->link_acao}}">{{$c->nome_botao}}</a></div>
								</div>
							</div>

						</div>
					</div>			
				</div>
			</div>
			@endif
			@endforeach

		</div>
	</div>
</div>
<br><br>
@if(sizeof($produtosEmDestaque) > 8)
<div class="best_sellers">
	<div class="container">
		<div class="row">
			<div class="col">
				<div class="tabbed_container">

					<div class="bestsellers_panel panel active">

						<!-- Best Sellers Slider -->

						<div class="bestsellers_slider slider">

							@foreach($produtosEmDestaque as $key => $p)

							@if($key >= 8)
							<div class="bestsellers_item discount">
								<div class="bestsellers_item_container d-flex flex-row align-items-center justify-content-start">
									<div class="bestsellers_image">
										<img src="{{$p->galeria[0]->img}}" alt="">
									</div>
									<div class="bestsellers_content">
										<div class="bestsellers_category">
											<a href="{{$rota}}/{{$p->categoria->id}}/categorias">{{$p->categoria->nome}}</a>
										</div>
										<div class="bestsellers_name"><a href="product.html">{{$p->produto->nome}}</a></div>

										<div class="bestsellers_price discount">
											R$ {{number_format($p->valor, 2, ',', '.')}}
											@if($p->percentual_desconto_view > 0)
											<span>R$ {{number_format($p->valor+($p->valor*($p->percentual_desconto_view/100)), 2, ',', '.')}}</span>
											@endif

										</div>
									</div>
								</div>
								
							</div>
							@endif
							@endforeach


						</div>


					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endif
<br>

@endsection