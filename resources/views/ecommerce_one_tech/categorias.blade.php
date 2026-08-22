@extends('ecommerce_one_tech.default')
@section('content')
<div class="deals_featured">
	<div class="container">
		<div class="row">
			<div class="col d-flex flex-lg-row flex-column align-items-center justify-content-start">

				<!-- Slider Item -->
				<div class="col-12"> 
					<div class="row"> 
						@foreach($categorias as $key => $c)

						<div class="featured_slider_item col-lg-4 col-xl-3 col-6">

							<div class="product_item discount d-flex flex-column align-items-center justify-content-center text-center">
								<div class="product_image d-flex flex-column align-items-center justify-content-center">
									<a href="{{$rota}}/{{$c->id}}/categorias"><img style="width: 120px;" class="img-destaque" src="{{$c->img}}" alt=""></a>
								</div>
								<div class="product_content">
									<div class="product_price discount">

										<a href="{{$rota}}/{{$c->id}}/categorias"><span style="">
											Produtos: <strong>{{sizeof($c->produtos)}}</strong>
										</span></a>
									</div>
									<div class="product_name"><div>
										<a href="{{$rota}}/{{$c->id}}/categorias">{{$c->nome}}</a></div>
									</div>
									<div class="product_extras">
										<div class="product_color">

										</div>
										<a href="{{$rota}}/{{$c->id}}/categorias"><button class="product_cart_button">Ver Produtos</button></a>
									</div>
								</div>

							</div>
						</div>

						@endforeach

					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection