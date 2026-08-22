<!DOCTYPE html>
<html lang="en">
<head>
	<title>{{$title}}</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="OneTech shop project">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/bootstrap4/bootstrap.min.css">
	<link rel="shortcut icon" href="{{$default['config']->img}}" type="image/x-icon" />

	<link href="/ecommerce_one/plugins/fontawesome-free-5.0.1/css/fontawesome-all.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/plugins/OwlCarousel2-2.2.1/owl.carousel.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/plugins/OwlCarousel2-2.2.1/owl.theme.default.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/plugins/OwlCarousel2-2.2.1/animate.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/plugins/slick-1.8.0/slick.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/main_styles.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/responsive.css">

	@if(isset($shop))
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/plugins/jquery-ui-1.12.1.custom/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/shop_styles.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/shop_responsive.css">
	@endif

	@if(isset($product))
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/product_styles.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/product_responsive.css">
	@endif

	@if(isset($contato))
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/contact_styles.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/contact_responsive.css">
	@endif

	@if(isset($cart))
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/cart_styles.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/cart_responsive.css">
	@endif

	@if(isset($blog))
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/blog_styles.css">
	<link rel="stylesheet" type="text/css" href="/ecommerce_one/styles/blog_responsive.css">
	@endif

	<style type="text/css">
		.img-logo{
			margin-top: 20px;
			width: 180px;
		}
		.img-logo2{
			width: 180px;
		}
		@media screen and (max-width: 800px) {
			.img-logo2{
				width: 100px;
			}

			.img-logo{
				width: 100px;
			}
		}
	</style>
</head>

<body>

	<div class="super_container">

		<!-- Header -->

		<header class="header">

			<!-- Top Bar -->

			<div class="top_bar">
				<div class="container">
					<div class="row">
						<div class="col d-flex flex-row">
							<div class="top_bar_contact_item"><div class="top_bar_icon"><img src="/ecommerce_one/images/phone.png" alt=""></div>{{$default['config']->telefone}}</div>
							<div class="top_bar_contact_item"><div class="top_bar_icon"><img src="/ecommerce_one/images/mail.png" alt=""></div><a href="mailto:{{$default['config']->email}}">{{$default['config']->email}}</a></div>
							<div class="top_bar_content ml-auto">

								<div class="top_bar_user">
									@if(session('user_ecommerce')) 
									<div class="user_icon"><img src="/ecommerce_one/images/user.svg" alt=""></div>
									
									<a href="{{$rota}}/login">Area do cliente</a>
									@else
									<div class="user_icon"><img src="/ecommerce_one/images/user.svg" alt=""></div>
									
									<a href="{{$rota}}/login">Login</a>
									@endif

								</div>
							</div>
						</div>
					</div>
				</div>		
			</div>

			<!-- Header Main -->

			<div class="header_main">
				<div class="container">
					<div class="row">

						<!-- Logo -->
						<div class="col-lg-2 col-sm-3 col-3 order-1">
							<div class="logo_container">
								<a href="{{$rota}}"><img class="img-logo" src="{{$default['config']->img}}" alt=""></a>
							</div>
						</div>

						<!-- Search -->
						<div class="col-lg-6 col-12 order-lg-2 order-3 text-lg-left text-right">
							<div class="header_search">
								<div class="header_search_content">
									<div class="header_search_form_container">
										<form action="{{$rota}}/pesquisa" class="header_search_form clearfix">
											<input type="search" required="required" class="header_search_input" placeholder="Pesquise o produto...">
											
											<button type="submit" class="header_search_button trans_300" value="Submit"><img src="/ecommerce_one/images/search.png" alt=""></button>
										</form>
									</div>
								</div>
							</div>
						</div>

						<!-- Wishlist -->
						<div class="col-lg-4 col-9 order-lg-3 order-2 text-lg-left text-right">
							<div class="wishlist_cart d-flex flex-row align-items-center justify-content-end">
								<div class="wishlist d-flex flex-row align-items-center justify-content-end">
									<a href="{{$rota}}/curtidas">
										<div class="wishlist_icon"><img src="/ecommerce_one/images/heart.png" alt=""></div>
										<div class="wishlist_content">
											<div class="wishlist_text"><a href="{{$rota}}/curtidas">Curtidas</a></div>
											<div class="wishlist_count">{{ $default['curtidas'] }}</div>
										</div>
									</a>
								</div>

								<!-- Cart -->
								<div class="cart">
									<div class="cart_container d-flex flex-row align-items-center justify-content-end">
										<div class="cart_icon">
											<a href="{{$rota}}/carrinho">
												<img src="/ecommerce_one/images/cart.png" alt="">
												<div class="cart_count">

													<span>{{ $default['carrinho'] != null ? sizeof($default['carrinho']->itens) : 0}}
													</span>
												</div>
											</a>
										</div>
										<div class="cart_content">
											<div class="cart_text"><a href="{{$rota}}/carrinho">Carrinho</a></div>
											<div class="cart_price">R$ {{ $default['carrinho'] != null ? number_format($default['carrinho']->somaItens(), 2, ',', '.') : '0,00'}}</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Main Navigation -->

			<nav class="main_nav">
				<div class="container">
					<div class="row">
						<div class="col">

							<div class="main_nav_content d-flex flex-row">

								<!-- Categories Menu -->

								<div class="cat_menu_container">
									<div class="cat_menu_title d-flex flex-row align-items-center justify-content-start">
										<div class="cat_burger"><span></span><span></span><span></span></div>
										<div class="cat_menu_text">Categorias</div>
									</div>

									<ul class="cat_menu" style="display: none">

										@foreach($default['categorias'] as $c)
										<li style="background: #F5F5F5;"><a href="{{$rota}}/{{$c->id}}/categorias">{{$c->nome}}</a></li>
										@foreach($c->subs as $s)
										<li><a style="padding-left: 30px;" href="{{$rota}}/{{$s->id}}/subcategoria">{{$s->nome}}</a></li>
										@endforeach
										@endforeach
									</ul>
								</div>

								<!-- Main Nav Menu -->

								<div class="main_nav_menu ml-auto">
									<ul class="standard_dropdown main_nav_dropdown">

										<li><a href="{{$rota}}">Home</a></li>
										<li><a href="{{$rota}}/categorias">Categorias</a></li>

										@if($default['postBlogExists'])
										<li><a href="{{$rota}}/blog">Blog</a></li>
										@endif
										<li><a href="{{$rota}}/contato">Contato</a></li>

									</ul>
								</div>

								<!-- Menu Trigger -->

								<div class="menu_trigger_container ml-auto">
									<div class="menu_trigger d-flex flex-row align-items-center justify-content-end">
										<div class="menu_burger">
											<div class="menu_trigger_text">menu</div>
											<div class="cat_burger menu_burger_inner"><span></span><span></span><span></span></div>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>
			</nav>

			<!-- Menu -->

			<div class="page_menu">
				<div class="container">
					<div class="row">
						<div class="col">

							<div class="page_menu_content">

								<div class="page_menu_search">
									<form action="#">
										<input type="search" required="required" class="page_menu_search_input" placeholder="Pesquise o produto...">
									</form>
								</div>
								<ul class="page_menu_nav">

									<li class="page_menu_item"><a href="{{$rota}}">Home</a></li>
									<li class="page_menu_item"><a href="{{$rota}}/categorias">Categorias</a></li>

									@if($default['postBlogExists'])
									<li class="page_menu_item"><a href="{{$rota}}/blog">Blog</a></li>
									@endif
									<li class="page_menu_item"><a href="{{$rota}}/contato">Contato</a></li>


								</ul>

								<div class="menu_contact">
									<div class="menu_contact_item"><div class="menu_contact_icon"><img src="/ecommerce_one/images/phone_white.png" alt=""></div>{{$default['config']->telefone}}</div>
									<div class="menu_contact_item"><div class="menu_contact_icon"><img src="/ecommerce_one/images/mail_white.png" alt=""></div><a href="mailto:{{$default['config']->email}}">{{$default['config']->email}}</a></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</header>

		@if(session()->has('mensagem_sucesso'))
		<div class="escfff" style="background: #fff;">
			<div class="container">
				<div class="alert alert-custom alert-success fade show" role="alert">

					<div class="alert-text"><i class="fa fa-check"></i> 
						{{ session()->get('mensagem_sucesso') }} 
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<i class="fa fa-close"></i>
						</button>
					</div>

				</div>
			</div>
		</div>
		@endif

		@if(session()->has('mensagem_erro'))
		<div class="escfff" style="background: #fff;">
			<div class="container">
				<div class="alert alert-custom alert-danger fade show" role="alert">

					<div class="alert-text"><i class="fa fa-check"></i> 
						{{ session()->get('mensagem_erro') }} 
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<i class="fa fa-close"></i>
						</button>
					</div>

				</div>
			</div>
		</div>
		@endif

		@yield('content')
		<div class="newsletter">
			<div class="container">
				<div class="row">
					<div class="col">
						<div class="newsletter_container d-flex flex-lg-row flex-column align-items-lg-center align-items-center justify-content-lg-start justify-content-center">
							<div class="newsletter_title_container">
								<div class="newsletter_icon"><img src="/ecommerce_one/images/send.png" alt=""></div>
								<div class="newsletter_title">Inscreva-se no boletim informativo</div>
								<div class="newsletter_text"><p>...e receba informações de descontos exclusivos</p></div>
							</div>
							<div class="newsletter_content clearfix">

								<form action="/ecommerceInformativo" method="post" class="newsletter_form">
									@csrf
									<input type="hidden" value="{{$default['config']->empresa_id}}" name="empresa_id">
									<input type="email" class="newsletter_input" required="required" name="email_info" placeholder="Seu melhor email">
									<button class="newsletter_button">Se inscrever</button>
								</form>

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Footer -->

		<footer class="footer">
			<div class="container">
				<div class="row">

					<div class="col-lg-3 footer_col">
						<div class="footer_column footer_contact">
							<div class="logo_container">
								<div class="logo"><a href="#">{{$default['config']->nome}}</a></div>
							</div>
							<div class="footer_title">Tem alguma pergunta? Ligue para nós 24 horas por dia, 7 dias por semana</div>
							<div class="footer_phone">{{$default['config']->telefone}}</div>
							<div class="footer_contact_text">
								<p>{{$default['config']->rua}}, {{$default['config']->numero}} - {{$default['config']->bairro}} - {{$default['config']->cidade->nome}}</p>
								{{-- <p>{{$default['config']->cidade}}</p> --}}
							</div>
							<div class="footer_social">
								<ul>
									@if($default['config']['link_facebook'] != "")
									<li><a target="_blank" href="{{$default['config']['link_facebook']}}"><i class="fab fa-facebook"></i></a></li>
									@endif

									@if($default['config']['link_instagram'] != "")
									<li><a target="_blank" href="{{$default['config']['link_instagram']}}"><i class="fab fa-instagram"></i></a></li>
									@endif
									@if($default['config']['link_twiter'] != "")
									<li><a target="_blank" href="{{$default['config']['link_twiter']}}"><i class="fab fa-twitter"></i></a></li>
									@endif

								</ul>
							</div>
						</div>
					</div>

					<div class="col-lg-2 offset-lg-2">

					</div>

					<div class="col-lg-2">
						<div class="footer_column">
							<div class="footer_title">Categorias</div>

							<ul class="footer_list ">
								@foreach($default['categorias'] as $c)
								<li><a href="{{$rota}}/{{$c->id}}/categorias">{{$c->nome}}</a></li>
								@endforeach
							</ul>
						</div>
					</div>

					<div class="col-lg-2">
						<div class="footer_column">
							<div class="footer_title">Acessos</div>
							<ul class="footer_list">
								<li><a href="{{$rota}}">Home</a></li>
								<li><a href="{{$rota}}/categorias">Categorias</a></li>

								@if($default['postBlogExists'])
								<li><a href="{{$rota}}/blog">Blog</a></li>
								@endif
								<li><a href="{{$rota}}/contato">Contato</a></li>
							</ul>
						</div>
					</div>

				</div>
			</div>
		</footer>

		<!-- Copyright -->

		<div class="copyright">
			<div class="container">
				<div class="row">
					<div class="col">

						<div class="copyright_container d-flex flex-sm-row flex-column align-items-center justify-content-start">
							<div class="copyright_content"><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
								Copyright &copy;<script>document.write(new Date().getFullYear());</script> by <strong>Slym</strong></a>
								<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
							</div>
							<div class="logos ml-sm-auto">
								<ul class="logos_list">
									<li><a href="#"><img src="/ecommerce_one/images/logos_1.png" alt=""></a></li>
									<li><a href="#"><img src="/ecommerce_one/images/logos_2.png" alt=""></a></li>
									<li><a href="#"><img src="/ecommerce_one/images/logos_4.png" alt=""></a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="/ecommerce_one/js/jquery-3.3.1.min.js"></script>
	<script src="/ecommerce_one/styles/bootstrap4/popper.js"></script>
	<script src="/ecommerce_one/styles/bootstrap4/bootstrap.min.js"></script>
	<script src="/ecommerce_one/plugins/greensock/TweenMax.min.js"></script>
	<script src="/ecommerce_one/plugins/greensock/TimelineMax.min.js"></script>
	<script src="/ecommerce_one/plugins/scrollmagic/ScrollMagic.min.js"></script>
	<script src="/ecommerce_one/plugins/greensock/animation.gsap.min.js"></script>
	<script src="/ecommerce_one/plugins/greensock/ScrollToPlugin.min.js"></script>
	<script src="/ecommerce_one/plugins/OwlCarousel2-2.2.1/owl.carousel.js"></script>
	<script src="/ecommerce_one/plugins/slick-1.8.0/slick.js"></script>
	<script src="/ecommerce_one/plugins/easing/easing.js"></script>
	<script src="/ecommerce_one/js/custom.js"></script>
	<script src="/ecommerce_one/plugins/parallax-js-master/parallax.min.js"></script>

	@if(!isset($payJs))
	<script src="/ecommerce/assets/js/jquery-ui.min.js"></script>
	<script src="/ecommerce/assets/js/jquery.slicknav.js"></script>
	@endif
	<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
	
	@if(isset($shop))
	<script src="/ecommerce_one/js/shop_custom.js"></script>
	<script src="/ecommerce_one/plugins/Isotope/isotope.pkgd.min.js"></script>
	<script src="/ecommerce_one/plugins/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
	<script src="/ecommerce_one/plugins/parallax-js-master/parallax.min.js"></script>
	@endif

	@if(isset($product))
	<script src="/ecommerce_one/js/product_custom.js"></script>
	@endif

	@if(isset($contato))
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key={{$default['config']->google_api}}"></script>
	<script src="/ecommerce_one/js/contact_custom.js">
	</script>
	
	@endif

	@isset($carrinhoJs)
	<script src="/ecommerce/assets/js/carrinho.js"></script>
	@endisset

	@isset($payJs)
	<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>

	<script type="text/javascript">
		window.Mercadopago.setPublishableKey('{{$default['config']->mercadopago_public_key}}')
	</script>

	<script src="/ecommerce/assets/js/pay.js"></script>
	@endisset

	<script type="text/javascript">
		$('.cat_menu_container').hover(function(){
			$('.cat_menu').css('display', 'block')
		}, function(){
			$('.cat_menu').css('display', 'none')
		})

		$('.cat_menu_container').click(function(){
			alert('o')
			$('.cat_menu').css('display', 'block')
		}, function(){
			$('.cat_menu').css('display', 'none')
		})
	</script>

	<script type="text/javascript">
		var cpfMascara = function(val) {
			return val.replace(/\D/g, "").length > 11
			? "00.000.000/0000-00"
			: "000.000.000-009";
		},
		cpfOptions = {
			onKeyPress: function(val, e, field, options) {
				field.mask(cpfMascara.apply({}, arguments), options);
			}
		};

		$(".cpf_cnpj").mask(cpfMascara, cpfOptions);
	</script>

	@yield('javascript')
	
</body>

</html>